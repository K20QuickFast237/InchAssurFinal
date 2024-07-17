<?php

namespace Modules\CLI\Controllers;

use CodeIgniter\Controller;
use Config\Services;
use Modules\Assurances\Entities\SouscriptionsEntity;
use Modules\Consultations\Entities\AvisExpertEntity;
use Modules\Consultations\Entities\ConsultationEntity;
use Modules\Paiements\Entities\TransactionEntity;
use Modules\Produits\Entities\PaiementOptionsEntity;

class CliController extends Controller
{
    const REMINDER_CYCLE = 1;
    public function message($to = 'World')
    {
        if (!is_cli()) {
            exit("Use only from CLI.");
        }
        return "Hello {$to}!" . PHP_EOL;
    }
    /*
        Ici mettre les fonctions qui s'exécuterons automatiquement en arrière plan tous les jours.
    */
    // Netttoyage ou archivage des fichiers non répertoriés dans la bd.
    // Vérification des statuts de souscriptions, consultations et de transactions.


    public function exec()
    {
        echo "\n-------------------- Debut Programme de synchronisation : --------------------\n";

        echo "\n*** Verification des Expertises demandées ***";
        $nbr_meds = $this->notifyExpertise();
        if ($nbr_meds) {
            echo "$nbr_meds Medecins Notifiés: ";
            print_r($nbr_meds);
        } else {
            echo "Aucun Expert à notifier.\n";
        }
        echo "=> Fin vérification des Expertises.\n";

        // echo "\n";
        echo "\n*** Vérification des transactions et paiements ***";
        $this->managePay();
        echo "=> Fin vérification des transactions et paiements.\n";

        // echo "\n";
        echo "\n*** Vérification des consultations ***";
        $this->cleanConsults();
        echo "=> Fin vérification des consultations.\n";

        // echo "\n";
        echo "\n*** Vérification des souscriptions ***";
        $this->cleanSubscriptions();
        echo "=> Fin vérification des souscriptions.\n";

        return "\n--------------------------- Fin du programme. --------------------------------";
    }

    private function cleanConsults()
    {
        /*
            Termine les consultations dont:
            - la date de rdv est passée de 2 jours
            - 
        */
        $consults = model("ConsultationsModel")
            ->where('statut', ConsultationEntity::VALIDE)
            ->asArray()
            ->findAll();

        $delaiConsult = ConsultationEntity::EPIRATION_TIME + 3;
        $expiredIds = [];
        foreach ($consults as $consult) {
            $dateFin = (new \Datetime($consult['date']))->modify("+$delaiConsult days");
            $gap = $dateFin->diff(new \DateTime())->days;
            if (strtotime(date('Y-m-d')) > strtotime($consult['date']) && $gap > $delaiConsult) {
                $expiredIds[] = $consult['id'];
            }
        }
        if ($expiredIds) {
            model("ConsultationsModel")->whereIn('id', $expiredIds)->set('statut', ConsultationEntity::EXPIREE)->update();
        }
        return 0;
    }

    private function cleanSubscriptions()
    {
        /*
            Fait passer l'état des souscriptions à inactive après la date de fin, 
            ainsi que lorsque les services sont totalement consommés
        */
        $souscriptions = model("SouscriptionsModel")->where('etat', SouscriptionsEntity::ACTIF)->findAll();

        $endedIds = [];
        foreach ($souscriptions as $souscript) {
            if (strtotime(date("Y-m-d")) > strtotime($souscript->dateFinValidite)) {
                $endedIds[] = $souscript->id;
            }
            if (!$souscript->hasServices()) {
                $endedIds[] = $souscript->id;
            }
        }

        model("SouscriptionsModel")->whereIn('id', $endedIds)->set('etat', SouscriptionsEntity::TERMINE)->update();
        return 0;
    }

    private function managePay()
    {
        /*
            Vérifie l'état des transactions terminées et notifie pour les paiements planifiés et à échéance
        */
        $transactions = model("TransactionsModel")
            ->join("paiement_options", "pay_option_id=paiement_options.id", "left")
            ->join("utilisateurs", "beneficiaire_id=utilisateurs.id", "left")
            ->select("transactions.id as idTrans, transactions.*, paiement_options.*, nom, prenom, email, tel1")
            ->where("transactions.etat", TransactionEntity::EN_COURS)
            ->asArray()
            ->findAll();

        $data = [];
        foreach ($transactions as $t) {
            $data[$t['idTrans']] = $t;
        }
        $terminatedIds = [];
        $payers = [];
        foreach ($transactions as $transaction) {
            if ($transaction['net_a_payer'] == $transaction['reste_a_payer']) {
                $terminatedIds[] = $transaction['idTrans'];
            }
            if ($transaction['type'] == PaiementOptionsEntity::CYCLIQUE) {
                $jours = $this->jours_depuis($transaction['dateCreation']);
                $cycleJours = $transaction['cycle_longueur'];
                if ($jours % ($cycleJours - self::REMINDER_CYCLE) === 0) {
                    $payers[] = ['id' => $transaction['idTrans'], 'jours' => 0];
                }
            }
            if ($transaction['type'] == PaiementOptionsEntity::PERIODE) {
                $jours = $this->jours_depuis($transaction['dateCreation']);
                $periodeJours = $transaction['etape_duree'];
                $cinquieme    = round(3 * $periodeJours / 5);
                $last         = $periodeJours - 5;
                if ($jours == $cinquieme) {
                    $payers[] = ['id' => $transaction['idTrans'], 'jours' => $periodeJours - $cinquieme];
                }
                if ($jours == $last) {
                    $payers[] = ['id' => $transaction['idTrans'], 'jours' => 5];
                }
            }
        }
        if ($terminatedIds) {
            model("TransactionsModel")->whereIn('id', $terminatedIds)->set('etat', TransactionEntity::TERMINE)->update();
        }
        if ($payers) {
            $this->sendPaiementEmail($payers, $data);
        }
        model("TransactionsModel")->whereIn('id', $terminatedIds)->set('etate', TransactionEntity::TERMINE)->update();
    }

    private function notifyExpertise()
    {
        /*
            Récupère les demandes d'avis en cours et notifie les médecins en fonction de la date de demande
            un delai de trois jours est accordé pour la demande d'avis d'expert
        */
        $delaiNotif = 3;
        $date = (new \Datetime())->modify("-$delaiNotif days")->format('Y-m-d');

        $avis = model("AvisExpertModel")
            ->select("medecin_sender_id as from, medecin_receiver_id as to")
            ->where('statut', AvisExpertEntity::EN_COURS)
            ->where('dateCreation', $date)
            ->asArray()->findAll();
        if (!$avis) {
            return [];
        }

        $users = model("utilisateursModel")->select("id, nom, prenom, email, tel1")
            ->whereIn('id', array_merge(array_column($avis, "to"), array_column($avis, "from")))
            ->asArray()
            ->findAll();

        $data = [];
        foreach ($avis as $e) {
            $patient = array_filter($users, fn ($u) => $u['id'] == $e['from']);
            $med = array_filter($users, fn ($u) => $u['id'] == $e['to']);
            $patient = array_shift($patient);
            $med = array_shift($med);
            $patients = $data[$e['to']]["patients"] ?? [];
            array_push($patients, $patient['nom'] . ' ' . $patient['prenom']);
            $data[$e['to']] = ["patients" => array_unique($patients), 'medecin' => ["nom" => $med['nom'] . ' ' . $med['prenom'], 'email' => $med['email'], 'tel' => $med['tel1']]];
        }

        $data = array_values($data);

        $this->sendExpertiseMails($data);
        return count($data);
    }

    private function sendExpertiseMails($infos)
    {
        // envoie sms
        $tels = array_map(fn ($m) => $m['medecin']['tel'], $infos);
        sendSmsMessage($tels, "IncHAssur", "Cher Spécialiste, vous avez des demandes d'expertises en attente de traitement depuis déjà un moment. Merci de faire confiance à IncHAssur.");

        // envoi mail
        $email = Services::email();
        foreach ($infos as $info) {
            $names = implode(", ", $info['patients']);
            $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
            $email->setTo($info['medecin']['email']);
            $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
            $email->setSubject('Rappel Demande Expertise');
            $email->setMessage("<h2>Bonjour " . $info['medecin']['nom'] . ".</h2>
                            <br>Ça fait un moment vous n'avez pas répondu aux demandes d'expertise de <italic> $names </italic>.
                            <br>Rendez-vous dans votre espace personnel afin d'y apporter votre réponse.
                            Merci de faire partie de nos utilisateurs de marque.
                            InchAssur-" . date('d-m-Y H:i'));
        }
    }

    private function sendPaiementEmail(array $payers, array $data)
    {
        $infos = [];
        foreach ($payers as $payer) {
            $infos[] = [
                "email" => $data[$payer['id']]['email'],
                "nom"   => $data[$payer['id']]['nom'] . ' ' . $data[$payer['id']]['prenom'],
                "tel"   => $data[$payer['id']]['tel1'],
                "duree" => $payer['jours'],
                "code"  => $data[$payer['id']]['code'],
            ];
        }
        // envoie sms
        $tels = array_unique(array_column($infos, 'tel'));
        sendSmsMessage($tels, "IncHAssur", "Cher utilisateur, vous avez des transactions en cours, plus de détails dans le mail qui vous a été envoyé. Merci de faire confiance à IncHAssur.");

        // envoi mail
        $email = Services::email();
        foreach ($infos as $info) {
            $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
            $email->setTo($info['email']);
            $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
            $email->setSubject('Rappel de transactions en cours');
            if ((int)$info['jours']) { // notification de paiement sur une periode
                $email->setMessage("<h2>Bonjour " . $info['nom'] . ".</h2>
                                <br>Il ne reste plus que " . $info['duree'] . " jours avant la fin de votre transaction numéro <strong>" . $info['code'] . ".<strong><br>
                                pensez à <a href='#'>effectuer un paiement</a> afin de la régler avant la date butoire.
                                Merci de nous faire confiance.
                                InchAssur-" . date('d-m-Y H:i'));
            } else { // notification de paiement cyclique
                $email->setMessage("<h2>Bonjour " . $info['nom'] . ".</h2>
                                <br>Vous avez un paiement prévu dans " . self::REMINDER_CYCLE . " jour(s) pour la transaction numéro <strong>" . $info['code'] . ".<strong><br>
                                Vous pouvez le <a href='#'>régler ici,</a> ou depuis votre compte utilisateur.
                                Merci de nous faire confiance.
                                InchAssur-" . date('d-m-Y H:i'));
            }
        }
        return 0;
    }

    private function jours_depuis(string $startDate)
    {
        $debut = new \DateTime(date('Y-m-d', strtotime($startDate)));
        $fin = new \DateTime();
        $diff = $fin->diff($debut);
        return $diff->days;

        $debut = strtotime($startDate);
        $today = time();
        $ecart = $today - $debut;
        return round($ecart / 86400, 0, PHP_ROUND_HALF_DOWN);
    }
}
