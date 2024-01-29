<?php

namespace Modules\Paiements\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Assurances\Entities\SouscriptionsEntity;
use Modules\Assurances\Models\SouscriptionsModel;
use Modules\Paiements\Entities\LignetransactionEntity;
use Modules\Paiements\Entities\PaiementEntity;
use Modules\Paiements\Entities\PayOptionEntity;
use Modules\Paiements\Entities\TransactionEntity;
// use Monetbil;

class PaiementsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    public function applyAssurReduction()
    {   /* NB: Une réduction ne peut être appliquée que sur les produits de son auteur */
        /*
            - On recupère le cout de la souscription,
            - On compare avec le prix reçu, ils doivent être pareils.
            - On récupère les infos de la réduction,
            - On vérifie que la réduction est applicable
            - On calcule le prix à réduire,
            - On retourne le résultat.
        */
        $rules = [
            'idSouscription' => [
                'rules'      => 'required|numeric|is_not_unique[souscriptions.id]',
                'errors'     => [
                    'required' => 'Identifiant de souscription invalide.',
                    'numeric'  => 'Identifiant de souscription invalide.',
                    'is_not_unique' => 'Identifiant de souscription invalide.',
                ],
            ],
            'code'       => [
                'rules'  => 'required|is_not_unique[reductions.code]',
                'errors' => [
                    'required'      => "Code de réduction inconnu.",
                    'is_not_unique' => "Code de réduction inconnu.",
                ],
            ],
            'prix'       => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'required' => "Montant inconne.",
                    'numeric'  => "Montant invalide.",
                ],
            ],
        ];

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => $errorsData['errors'],
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input = $this->getRequestInput($this->request);
        $souscription = model("SouscriptionsModel")->find($input['idSouscription']);
        $prixInitial = $input['prix'];
        $code        = $input['code'];
        if (!$souscription->cout == $prixInitial) {
            $response = [
                'statut'  => 'no',
                'message' => "Le prix n'est pas en accord avec la souscription.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $reduction = model("ReductionsModel")->where("code", $code)->first();

        /* On verifie que la reduction est applicable */
        $dateDiff = strtotime($reduction->expiration_date) - strtotime(date('Y-m-d'));
        if ($dateDiff < 0 || $reduction->usage_max_nombre <= $reduction->utilise_nombre) {
            $message = "Code promo Expiré!";
        }
        $idAssureur = model("AssurancesModel")->where("id", $souscription->assurance_id)->findColumn("assureur_id")[0];
        if ($idAssureur != $reduction->auteur_id) {
            $message = "Code promo non utilisateble pour cette assurance.";
        }

        if (isset($message)) {
            $response = [
                'status'  => 'no',
                'message' => $message,
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $reduction = $this->calculateReduction($prixInitial, $reduction);
        $data = [
            "code"          => $code,
            "prixInitial"   => $prixInitial,
            "prixReduction" => $reduction,
            "prixFinal"     => $prixInitial - $reduction
        ];
        $response = [
            'status'  => 'ok',
            'message' => "Réduction appliquée.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Détermine le montant à réduire
     */
    private function calculateReduction($prixInitial, $reduction): float
    {
        /*
            lorsque la valeure et le taux son défini tous les deux, le taux est appliqué à la limite de la valeure
        */
        $valeur = $reduction->valeur;
        $taux   = $reduction->taux;
        if ($taux && $valeur) {
            $reductionTaux = ($prixInitial * $taux) / 100;
            $reduction     = $reductionTaux > $valeur ? $valeur : $reductionTaux;
        } elseif (!$taux) {
            $reduction = ($prixInitial * $taux) / 100;
        } else {
            $reduction = $valeur;
        }

        return $reduction;
    }

    /**
     * Initialyse la souscriptionj à une assurance en générant un lien,
     * après avoir initié la transaction.        
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function InitiateAssurPayment()
    {
        require_once ROOTPATH . 'Modules\Paiements\ThirdParty\monetbil-php-master\monetbil.php';

        $monetbil_args_model = array(
            'amount'      => 0,
            'phone'       => null,
            'locale'      => 'fr', // Display language fr or en
            'country'     => 'CM',
            'currency'    => 'XAF',
            'operator'    => null,
            'item_ref'    => null,
            'payment_ref' => null,
            'user'        => null,
            'first_name'  => null,
            'last_name'   => null,
            'email'       => null,
            'return_url'  => null,
            'notify_url'  => base_url('paiements/notify'),
            'logo'        => base_url("uploads/images/logoinch.jpeg"),
        );

        // // This example show payment url
        // $data = Monetbil::url($monetbil_args);

        $rules = [
            'idSouscription' => [
                'rules'        => 'required|numeric|is_not_unique[souscriptions.id]',
                'errors'       => [
                    'required' => 'Souscription non définie.',
                    'numeric'  => 'Identifiant de souscription invalide',
                    'is_not_unique' => 'Identifiant de souscription invalide',
                ],
            ],
            'prix'           => [
                'rules'        => 'required|numeric',
                'errors'       => [
                    'required' => 'Prix non défini.',
                    'numeric'  => 'Prix invalide',
                ],
            ],
            'idAssurance'    => [
                'rules'        => 'required|numeric|is_not_unique[assurances.id]',
                'errors'       => [
                    'required' => 'Assurance non spécifié.',
                    'numeric'  => "Identifiant d'assurance invalide",
                    'is_not_unique' => "Identifiant d'assurance invalide",
                ],
            ],
            'idPayOption'    => [
                'rules'      => 'required|numeric|is_not_unique[paiement_options.id]',
                'errors'     => [
                    'required' => 'Option de paiement non spécifiée.',
                    'numeric'  => "option de paiement invalide",
                    'is_not_unique' => "Option de paiement invalide",
                ],
            ],
            'telephone'      => [
                'rules'      => 'if_exist|numeric',
                'errors'     => ['numeric' => 'Numéro de téléphone invalide.'],
            ],
            'pays'           => [
                'rules'      => 'if_exist|is_not_unique[paiement_pays.code]',
                'errors'     => ['is_not_unique' => 'Pays non pris en charge.'],
            ],
            'returnURL'      => [
                'rules'      => 'required|valid_url]',
                'errors'     => [
                    'required'  => "L'URL de retour doit être spécifié.",
                    'valid_url' => "URL de retour non conforme.",
                ],
            ],
            // 'avance'         => [
            //     'rules'        => 'required|numeric',
            //     'errors'       => [
            //         'required' => 'Avance non définie.',
            //         'numeric'  => "Valeur de l'avance invalide",
            //     ],
            // ],
            'codeReduction'  => [
                'rules'        => 'if_exist|is_not_unique[reductions.code]',
                'errors'       => [
                    'is_not_unique'  => "Code de réduction inconnu.",
                ],
            ],
            'operateur'      => [
                'rules'        => 'required|in_list[CM_ORANGEMONEY,CM_MTNMOBILEMONEY,CM_EUMM]',
                'errors'       => [
                    'required' => 'Opérateur non défini.',
                    'in_list'  => 'Opérateur invalide',
                ],
            ],
        ];

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $input     = $this->getRequestInput($this->request);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'initialiser le paiement.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $prixInitial  = $input['prix'];
        $souscription = model("SouscriptionsModel")->find($input['idSouscription']);
        /*
            1- On initie la ligne de transaction,
            2- On initie la transaction,
            3- On initie le paiement,
            4- On met a jour la souscription,
            5- Déclencher le background Job de gestion des paiement,
            6- On appelle monetBill,
            7- On retourne le résultat,
            x- On initie le paiement,    // Ceci sera plustô fait après la réponse de l'API de paiement
        */
        $prixUnitaire  = model("AssurancesModel")->where('id', $input['idAssurance'])->findColumn("prix")[0];
        $payOption     = model("PaiementOptionsModel")->find($input['idPayOption']);
        if (isset($input['codeReduction'])) {
            $reduction = model("ReductionsModel")->where("code", $input['codeReduction'])->first();
            $prixReduction = $this->calculateReduction($prixInitial, $reduction);
        } else {
            $prixReduction = 0;
        }
        $prixToPay = $prixInitial - $prixReduction;

        // 1- Initiation de la ligne transaction
        $ligneInfo = new LignetransactionEntity([
            "produit_id"         => $input['idAssurance'],
            "produit_group_name" => 'Assurance',
            "souscription_id"    => $souscription->id,
            "quantite"           => 1,
            "prix_unitaire"      => $prixUnitaire,
            "prix_total"         => $prixInitial,
            // "reduction_code"     => $reduction->code,
            "prix_reduction"     => $prixReduction,
            "prix_total_net"     => $prixToPay,
        ]);
        isset($reduction) ? $ligneInfo->reduction_code = $reduction->code : null;
        $ligneInfo->id = model("LignetransactionsModel")->insert($ligneInfo);

        // 2- Initiation de la Transaction
        $tva          = model("TvasModel")->find(1);
        $prixTVA      = ($prixToPay * $tva->taux) / 100;
        $prixToPayNet = $prixToPay + $prixTVA;
        // $avance       = (float)$input['avance'];
        $avance       = $payOption->get_initial_amount_from_option($prixToPayNet);
        /** @todo après confirmation du paiement par l'API, mettre à jour l'état en fonction de la valeur
         * du reste_a_payer. conf: document word ReferenceClassDiagramme.
         * @done
         */
        $transactInfo = new TransactionEntity([
            "code"          => random_string('alnum', 10),
            "motif"         => "Paiement Souscription $souscription->code",
            "pay_option_id" => $payOption->id,
            "prix_total"    => $prixToPay,
            "tva_taux"      => $tva->taux,
            "valeur_tva"    => $prixTVA,
            "net_a_payer"   => $prixToPayNet,
            "avance"        => $avance,
            "reste_a_payer" => $prixToPayNet - $avance,
            "etat"          => TransactionEntity::INITIE,
        ]);
        $transactInfo->id = model("TransactionsModel")->insert($transactInfo);
        model("TransactionLignesModel")->insert(['transaction_id' => $transactInfo->id, 'ligne_id' => $ligneInfo->id]);

        // 3- On initie le paiement,
        $paiementInfo = array(
            'code'    => random_string('alnum', 10),
            'montant' => $avance,
            'statut'  => PaiementEntity::EN_COURS,
        );

        // 4- Mise à jour de la souscription
        /** Cette étape est déplacée dans la gestion de la réponse de l'API. */

        // 5- Déclencher le background job de gestion des paiements
        /** Cette étape est déplacée dans la gestion de la réponse de l'API. */

        // 6- Appeler MonetBill
        $monetbil_args = array(
            'amount'      => $avance,
            'phone'       => $input['telephone'] ?? null,
            'country'     => $input['pays'] ?? null,
            'phone_lock'  => false,
            'locale'      => 'fr', // Display language fr or en
            'operator'    => $input['operateur'],
            'item_ref'    => $souscription->code,
            'payment_ref' => $paiementInfo['code'],
            'user'        => $this->request->utilisateur->code,
            'return_url'  => $input['returnURL'],
        );

        // This example show payment url
        $data = ['url' => \Monetbil::url($monetbil_args + $monetbil_args_model)];

        $response = [
            'statut'  => 'ok',
            'message' => "Paiement Initié.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Valide un paiement effectué par Monetbil et l'enregistre.
     * 
     * Elle est appellée uniquement par l'API Monetbil
     */
    public function setPayStatus()
    {
        require_once ROOTPATH . 'Modules\Paiements\ThirdParty\monetbil-php-master\monetbil.php';

        $params = \Monetbil::getPost();
        $service_secret = \Monetbil::getServiceSecret();

        if (!\Monetbil::checkSign($service_secret, $params)) {
            header('HTTP/1.0 403 Forbidden');
            exit('Error: Invalid signature');
        }

        $service          = \Monetbil::getPost('service');
        $transaction_id   = \Monetbil::getPost('transaction_id');
        $transaction_uuid = \Monetbil::getPost('transaction_uuid');
        $phone            = \Monetbil::getPost('msisdn');
        $amount           = \Monetbil::getPost('amount');
        $fee              = \Monetbil::getPost('fee');
        $status           = \Monetbil::getPost('status');
        $message          = \Monetbil::getPost('message');
        $country_name     = \Monetbil::getPost('country_name');
        $country_iso      = \Monetbil::getPost('country_iso');
        $country_code     = \Monetbil::getPost('country_code');
        $mccmnc           = \Monetbil::getPost('mccmnc');
        $operator         = \Monetbil::getPost('mobile_operator_name');
        $currency         = \Monetbil::getPost('currency');
        $user             = \Monetbil::getPost('user');
        $item_ref         = \Monetbil::getPost('item_ref');
        $payment_ref      = \Monetbil::getPost('payment_ref');
        $first_name       = \Monetbil::getPost('first_name');
        $last_name        = \Monetbil::getPost('last_name');
        $email            = \Monetbil::getPost('email');

        list($payment_status) = \Monetbil::checkPayment($transaction_id);

        $ligneTransact = model("LigneTransactionModel")->where('souscription_id', $item_ref)->first();
        $idLigneTransact = $ligneTransact->id;
        $idAssurance = $ligneTransact->idproduit_id;
        unset($ligneTransact);
        $transactInfo = model("TransactionsModel")->join("transaction_lignes", "transaction_id=transactions.id")
            ->select('transactions.*')
            ->where("ligne_id", $idLigneTransact)
            ->first();

        if (\Monetbil::STATUS_SUCCESS == $payment_status) {
            // Successful payment!
            model("PaiementsModel")->where("code", $payment_ref)->set('statut', PaiementEntity::VALIDE)->update();

            if ($transactInfo['reste_a_payer'] <= 0) {
                model("TransactionsModel")->update($transactInfo['id'], ['etat' => TransactionEntity::TERMINE]);
            } else {
                model("TransactionsModel")->update($transactInfo['id'], ['etat' => TransactionEntity::EN_COURS]);
            }
            $souscription = model("SouscriptionsModel")->where("code", $item_ref)->first();
            $duree = model("AssurancesModel")->where('id', $idAssurance)->findColumn('duree')[0];
            $today = date('Y-m-d');

            model("SouscriptionsModel")->where("code", $item_ref)->set([
                "etat"              => SouscriptionsEntity::ACTIF,
                "dateDebutValidite" => $today,
                "dateFinValidite"   => date('Y-m-d', strtotime("$today + $duree days")),
            ])->update();
            // Mark the order as paid in your system
        } elseif (\Monetbil::STATUS_CANCELLED == $payment_status) {
            // Transaction cancelled
            model("PaiementsModel")->where("code", $payment_ref)->set('statut', PaiementEntity::ANNULE)->update();
        } else {
            // Payment failed!
            model("PaiementsModel")->where("code", $payment_ref)->set('statut', PaiementEntity::ECHOUE)->update();
        }

        /** @todo Line to remove */
        file_put_contents(WRITEPATH . '/BillContent/' . date('Y-m-d') . 'txt', json_encode([
            'received data' => \Monetbil::getPost()
        ]));
        // Received
        exit('received');
    }

    public function getCountries()
    {
        $response = [
            'statut'  => 'ok',
            'message' => "Pays acceptés pour le paiement.",
            'data'    => model("PaiementPaysModel")->findAll(),
        ];
        return $this->sendResponse($response);
    }
}
