<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Assurances\Entities\SouscriptionsEntity;
use Modules\Paiements\Entities\PayOptionEntity;
use Modules\Paiements\Entities\TransactionEntity;
use CodeIgniter\Events\Events;

class Synchronise extends BaseCommand
{
    protected $group       = 'fonctionalities';
    protected $name        = 'app:synchonise';
    protected $description = 'Updates paiements, transactions and subscriptions status and send related notifications.';

    public function run(array $params)
    {
        /* -----------------------------------------------------------------------
                                    Gestion des Souscriptions
                        L'objectif est de récupérer les souscriptions et
                en fonction de leur date d'expiration, mettre à jour le statut.
        -------------------------------------------------------------------------- */
        $souscriptions  = model("SouscriptionsModel")->where("statut", SouscriptionsEntity::ACTIF)->findAll();
        $endenSouscript = array_filter($souscriptions, fn ($s) => strtotime($s->dateFinValidite) > strtotime(date('Y-m-d')));
        $endedIds       = array_map(fn ($s) => $s->id, $endenSouscript);
        $userIds        = array_map(fn ($s) => $s->souscripteur_id, $endenSouscript);

        // on notifie
        $users   = model("UtilisateursModel")->select("id, nom, prenom, email")
            ->whereIn('id', $userIds)
            ->findAll();
        foreach ($endenSouscript as $souscript) {
            $userSubscriptCode = $souscript->code;
            $user = array_filter($users, fn ($u) => $u->id == $souscript->souscripteur_id);
            Events::trigger('EndedSouscription', $user, $userSubscriptCode);
        }
        // model("SouscriptionsModel")->whereIn('id', $endedIds)->set("etat", SouscriptionsEntity::TERMINE)->update();

        /* -----------------------------------------------------------------------
                            Gestion des Options de paiement
            L'objectif est de récupérer les transactions en cours et
            en fonction de leur option de paiement, Envoyer une notification;
            si nécessaire, mettre fin à la souscription en actuailsant son statut.
        --------------------------------------------------------------------------- */
        $transacts = model("TransactionsModel")->where("reste_a_payer >", 0)
            ->where("etat", TransactionEntity::EN_COURS)
            ->findAll();
        $payOptionIds = array_unique(array_map(fn ($t) => $t->pay_option_id, $transacts));
        $payOptions   = model("PaiementOptionsModel")->whereIn('id', $payOptionIds)->findAll();
        $payOptions   = array_combine($payOptionIds, $payOptions);

        // $today = date('Y-m-d');
        $endedTransact = $stepPayNotify = $planPayNotify = [];
        foreach ($transacts as $transact) {
            $option   = $payOptions[$transact->pay_option_id];
            $timeDiff = time() - strtotime($transact->dateCreation);
            $jours    = floor($timeDiff / DAY);
            if ($option->type == PayOptionEntity::ECHEANCE_PAY_OPT) {
                /* On vérifie que nous sommes à moitié de la durée d'échéance où aux 3/4 et
                    on notifie (à travers l émission d'un évènement). 
                    Si la date max de paiement est atteinte,
                    le paiement et la souscription sont marqués terminé malgré tout,
                    et on notifie.
                */
                $moitie   = round($option->etape_duree / 2);
                $moinTier = round($option->etape_duree / 3);
                if ($jours > $option->etape_duree) {
                    // retrouver l'id de la transaction concernée
                    $endedTransact[] = $transact->id;
                    $endedIds[] = $transact->id;
                } elseif ($jours == $moitie || $jours == $moinTier) {
                    // On emet l'évènement de notification
                    $planPayNotify[] = $transact->id;
                }
            }
            if ($option->type == PayOptionEntity::PLANED_PAY_OPT) {
                /* On vérifie que nous sommes la veille d'une date d'échéance et on notifie.
                    le reste pareil au cas ci-dessus.
                */
                $step = $jours % $option->cycle_longueur;
                if ($option->cycle_longueur - $step == 1) { // $nous sommes la veille.
                    $stepPayNotify[] = $transact->id;
                }
            }
        }

        $transactIds = array_unique(array_merge($endedTransact, $stepPayNotify, $planPayNotify));
        $souscriptIds = model("TransactionLignesModel")
            ->join("lignetransactions", "transaction_lignes.ligne_id=lignetransactions.id", "left")
            ->select("transaction_id, souscription_id")
            ->whereIn("transaction_id", $transactIds)
            ->findAll();
        // ->findColumn("souscription_id");
        $souscriptIds = array_combine(array_column($souscriptIds, "transaction_id"), array_column($souscriptIds, "souscription_id"));

        $souscripts = model("SouscriptionsModel")->whereIn("id", array_values($souscriptIds))->findAll();
        $userIds = array_map(fn ($s) => [$s->id => $s->souscripteur_id], $souscripts);
        $users   = model("UtilisateursModel")->select("id, nom, prenom, email")
            ->whereIn('id', array_values($userIds))
            ->findAll();

        $infotransact = array_map(function ($t) use ($transacts, $souscripts, $souscriptIds, $users, $userIds) {
            $transact = array_filter($transacts, fn ($e) => $e->id == $t);
            $souscript = array_filter($souscripts, fn ($e) => $e->id == $souscriptIds[$t]);
            $user     = array_filter($users, fn ($e) => $e->id == $userIds[$souscript->id]);
            return [
                $t => [
                    "souscription" => $souscript,
                    "user"         => $user,
                    "transaction"  => $transact,
                ]
            ];
        }, $transactIds);

        foreach ($endedTransact as $transactId) {
            Events::trigger(
                'EndedSouscription',
                $infotransact[$transactId]["user"],
                $infotransact[$transactId]["souscription"]->code,
                $infotransact[$transactId]["transaction"]->code,
                $normal = false
            );
        }

        foreach ($stepPayNotify as $transactId) {
            Events::trigger(
                'PaiementRemember',
                $infotransact[$transactId]["user"],
                $infotransact[$transactId]["transaction"]->code,
            );
        }

        foreach ($planPayNotify as $transactId) {
            $payoptId = $infotransact[$transactId]["transaction"]->pay_option_id;
            $payopt   = $payOptions[$payoptId];
            $dateFin  = date('Y-m-d', strtotime($infotransact[$transactId]["transaction"]->dateCreation . " + $payopt->etape_duree days"));
            Events::trigger(
                'PaiementSuggest',
                $infotransact[$transactId]["user"],
                $infotransact[$transactId]["transaction"]->code,
                $dateFin
            );
        }

        /*
            $endedSubs = array_combine(array_column($endedSubs, "souscription_id"), array_column($endedSubs, "code"));

            $userSubs = model("SouscriptionsModel")->select("code, souscripteur_id")->whereIn('id', array_keys($endedSubs))->findAll(); // findColumn("souscripteur_id");
            $userSubs = array_combine(array_column($userSubs, "souscripteur_id"), array_column($userSubs, "code"));
            $users    = model("UtilisateursModel")->select("id, nom, prenom, email")
                ->whereIn('id', array_keys($userSubs))
                ->findAll();
            foreach ($users as $user) {
                $userSubscriptCode = $userSubs[$user->id];
                Events::trigger('EndedSouscription', $user, $userSubscriptCode, $normal = false);
            }








        */

        $endedIds = array_unique(array_merge($endedIds, $endedIds));
        model("SouscriptionsModel")->whereIn('id', $endedIds)->set("etat", SouscriptionsEntity::TERMINE)->update();

        return EXIT_SUCCESS;
    }
}
