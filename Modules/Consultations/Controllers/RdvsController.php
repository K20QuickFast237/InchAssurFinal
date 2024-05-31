<?php

namespace Modules\Consultations\Controllers;

require_once ROOTPATH . 'Modules\Paiements\ThirdParty\monetbil-php-master\monetbil.php';

use App\Controllers\BaseController;
use App\Database\Migrations\Transactions;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Consultations\Entities\ConsultationEntity;
use Modules\Paiements\Entities\PaiementEntity;
use Modules\Paiements\Entities\PayOptionEntity;
use Modules\Paiements\Entities\TransactionEntity;

class RdvsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des rdvs d'un utilisateur
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier) {
            if (!auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($identifier, 'id');
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }
        $rdvs = model("RdvsModel")
            ->where("emetteur_user_id", $utilisateur->id)
            ->orwhere("destinataire_user_id", $utilisateur->id)
            ->groupBy('destinataire_user_id', 'desc')
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => (count($rdvs) ? count($rdvs) : 'Aucun') . ' rendez-vous trouvé(s).',
            'data'    => $rdvs,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retourne la liste de tous les rendez-vous enregistrés
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
        if (!auth()->user()->can('consultations.viewAll')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rdvs = model("RdvsModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => (count($rdvs) ? count($rdvs) : 'Aucun') . ' rendez-vous trouvé(s).',
            'data'    => $rdvs,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Returne les détails d'un rendez-vous
     *
     * @return ResponseInterface The HTTP response.
     */
    public function show($identifier)
    {
        $identifier = $this->getIdentifier($identifier);
        $rdv = model("RdvsModel")->where($identifier['name'], $identifier['value'])->first();
        if (
            $rdv &&
            (auth()->user()->inGroup('administrateur') ||
                $rdv->emetteur_user_id == $this->request->utilisateur->id ||
                $rdv->destinataire_user_id == $this->request->utilisateur->id)
        ) {
            $response = [
                'statut'  => 'ok',
                'message' => 'Détails du rendez-vous.',
                'data'    => $rdv,
            ];
            return $this->sendResponse($response);
        }
        $response = [
            'statut'  => 'no',
            'message' => $rdv ? ' Action non authorisée pour ce profil.' : "Rendez-vous Inconnu.",
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
    }

    /**
     * Enregistre un rendez-vous
     * un rendez-vous est une consultation en Attenete avec bilan vide
     * 
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            'objet' => ['rules' => 'required', 'errors' => ['required' => 'Précisez l\'objet du Rendez-vous.']],
            /* Do not remove, for other format
                'heure'  => [
                    'rules' => 'required|valid_date[Y-m-d H:i]',
                    'errors' => ['required' => "Précisez l'heure du Rendez-vous.", 'valid_date' => "Format d'heure attendu YYYY-MM-DD HH:ii."]
                ],
            */
            'date'  => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => ['required' => 'Précisez la date du Rendez-vous.', 'valid_date' => 'Format de date attendu YYYY-MM-DD.']
            ],
            'heure' => [
                'rules'  => 'required|valid_date[H:i]',
                'errors' => ['required' => 'Précisez l\'heure du Rendez-vous.', 'valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'idCreneau' => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => 'Mauvaise identification du créneau choisi.', 'numeric' => 'Mauvaise identification du créneau choisi.']
            ],
            'withAssur' => [
                'rules'  => 'required|permit_empty',
                'errors' => ['required' => 'Vous devez préciser si avec assurance ou non.']
            ],
            // 'idAssurance' => [
            //     'rules'  => 'required_with[withAssur]|permit_empty|is_not_unique[assurances.id]',
            //     'errors' => ['required' => 'Assurance requise pour consultation avec asssurance.']
            // ],
            'idSouscription' => [
                'rules'  => 'required_with[withAssur]|permit_empty|is_not_unique[souscriptions.id]',
                'errors' => ['required' => 'Souscription requise pour consultation avec asssurance.']
            ],
            // 'duree' => [
            //     'rules'  => 'if_exist|numeric',
            //     'errors' => ['if_exist' => 'Précisez la duree du Rendez-vous', 'numeric' => 'Duree invalide.']
            // ],
            'canal' => [
                'rules'  => 'required|is_not_unique[canaux.nom]',
                'errors' => ['required' => 'Précisez le canal de consultation.', 'is_not_unique' => 'Canal de consultation invalide.']
            ],
            'langue' => [
                'rules'  => 'if_exist|is_not_unique[langues.nom]',
                'errors' => ['required' => 'Précisez la langue de consultation.', 'is_not_unique' => 'Langue de consultation invalide.']
            ],
            'idLocalisation' => [
                'rules'  => 'required|is_not_unique[localisations.id]',
                'errors' => ['required' => 'Précisez la ville de consultation.', 'is_not_unique' => 'Localisation de consultation invalide.']
            ],
            // 'skill'      => [
            //     'rules'  => 'required|is_not_unique[skills.nom]',
            //     'errors' => ['required' => 'Précisez la compétence de consultation.', 'is_not_unique' => 'Compétence de consultation invalide.']
            // ],
            'idSkill'    => [
                'rules'  => 'required|is_not_unique[skills.id]',
                'errors' => ['required' => 'Précisez la compétence de consultation.', 'is_not_unique' => 'Compétence de consultation invalide.']
            ],
            // 'idEmetteur' => [
            //     'rules'  => 'required|is_not_unique[utilisateurs.id]',
            //     'errors' => ['is_not_unique' => 'Emetteur inconnu.', 'required' => 'Précisez l\'emetteur du Rendez-vous.']
            // ],
            'idDestinataire' => [
                'rules'  => 'required|is_not_unique[utilisateurs.id]',
                'errors' => ['is_not_unique' => 'Destinataire inconnu.', 'required' => 'Précisez le destinataire du Rendez-vous.']
            ],
            'isSecondAdvice' => [
                'rules'  => 'required|permit_empty',
                'errors' => ['required' => 'Vous devez préciser si c\'est un second avis ou non.']
            ],
            'isExpertise' => [
                'rules'  => 'required|permit_empty',
                'errors' => ['required' => 'Précisez si il s\'agit d\'une expertise ou non.'],
            ],
            'idPrevious' => [
                'rules'  => 'required_with[isSecondAdvice]|permit_empty|is_not_unique[consultations.id]',
                'errors' => ['required' => 'Préciser la consultation de départ pour un second avis.', 'is_not_unique' => 'Consultation de départ invalide.']
            ],
            'operateur'  => [
                'rules'  => 'required|is_not_unique[paiement_modes.operateur]',
                'errors' => ['required' => 'Opérateur non défini.', 'is_not_unique' => 'Opérateur invalide'],
            ],
        ];

        $pay_rules = [
            'operateur'  => [
                'rules'  => 'required|is_not_unique[paiement_modes.operateur]',
                'errors' => ['required' => 'Opérateur non défini.', 'is_not_unique' => 'Opérateur invalide'],
            ],
            'telephone'  => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => 'Numéro de téléphone requis pour ce mode de paiement.', 'numeric' => 'Numéro de téléphone invalide.']
            ],
            'returnURL'  => [
                'rules'  => 'required|valid_url',
                'errors' => ['required' => 'L\'URL de retour doit être spécifiée pour ce mode de paiement.', 'valid_url' => 'URL de retour non conforme.']
            ],
            'pays'       => [
                'rules'  => 'if_exist|is_not_unique[paiement_pays.code]',
                'errors' => ['is_not_unique' => 'Pays non pris en charge.'],
            ],
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter ce Rendez-vous.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);
        $utilisateur = $this->request->utilisateur;

        $skill = model('MedecinSkillsModel')
            ->select('nom, description, skills.id, medecin_id, description_perso, cout, isExpert, cout_expert')
            ->join('skills', 'skills.id = medecin_skills.skill_id')
            ->where('skill_id', $input['idSkill'])
            ->where('medecin_id', $input['idDestinataire'])
            ->first() ?? [];

        if ($input['isExpertise'] && !$skill['isExpert']) {
            $response = [
                'statut'  => 'no',
                'message' => 'Le Médecin choisi ne consulte pas en Expertise pour ce motif.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $consultationInfos = [
            'objet'           => (string)htmlspecialchars($input['objet']),
            'description'     => (string)htmlspecialchars($input['description'] ?? null),
            'date'            => $input['date'] ?? date('Y-m-d'),
            'heure'           => $input['heure'] ?? date('H:i:s'),
            'canal'           => $input['canal'],
            'langue'           => $input['langue'],
            'localisation_id' => $input['idLocalisation'],
            'skill'           => $skill['nom'],
            // 'prix'            => (float)$input['prix'],
            'isAssured'       => (bool)$input['withAssur'] ?? false,
            'isSecondAdvice'  => (bool)$input['isSecondAdvice'] ?? false,
            'isExpertise'     => (bool)$input['isExpertise'],
            'prix'            => (bool)$input['isExpertise'] ? $skill['cout_expert'] : $skill['cout'],
            // 'statut'          => ConsultationEntity::VALIDE,  // plus besoin de validation pour uns consultation payée
            // 'patient_user_id' => $input['idEmetteur'],
            'patient_user_id' => $utilisateur->id,
            'medecin_user_id' => $input['idDestinataire'],
            'statut'          => ConsultationEntity::ENATTENTE,
        ];

        // if (isset($input['duree'])) {
        //     $consultationInfos['duree'] = $input['duree'];
        // } else {
        //     $consultationInfos['duree'] = ConsultationEntity::DEFAULT_DUREE;
        // }
        if ($consultationInfos['isSecondAdvice']) {
            $consultationInfos['previous_id'] = (int)$input['idPrevious'];
        }

        $consultModel = model("ConsultationsModel");
        $consultationInfos['code'] = $this->generatecodeConsult($consultModel);

        model("ConsultationsModel")->db->transBegin();

        if ($consultationInfos['isAssured']) {
            $souscriptID = (int)$input['idSouscription'];
            $consultationInfos['souscription_id'] = $souscriptID;
            // Vérifier que la souscription offre ce service
            $souscription = model('SouscriptionsModel')->find($souscriptID);
            if (!$souscription->isValid()) {
                $response = [
                    'statut'  => 'no',
                    'message' => 'Souscription Invalide.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
            try {
                $serviceInfo = (new ConsultationEntity())->showServiceInfo();
                $prixCouvert = $souscription->coverage($serviceInfo->id, $consultationInfos['prix']);
            } catch (\Throwable $th) {
                $response = [
                    'statut'  => 'no',
                    'message' => $th->getMessage(),
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
            $paiementInfo = [
                'code'      => random_string('alnum', 10),
                'montant'   => $prixCouvert,
                'statut'    => PaiementEntity::VALIDE,
                'mode_id'   => model("PaiementModesModel")->where('operateur', 'PORTE_FEUILLE')->findColumn('id')[0] ?? 1,
                'auteur_id' => $souscription->assurance->assureur_id,
            ];
            // Mise à jour des services de la souscription
            $service = $souscription->getService($serviceInfo->id);
            $service->quantite_utilise = $service->quantite_utilise + 1;
            $service->prix_couvert    = $service->prix_couvert + $prixCouvert;
            $service->etat = $serviceInfo->prix_couverture > $service->prix_couvert && $serviceInfo->quantite > $service->quantite_utilise;

            model("SouscriptionServicesModel")->where('service_id', $service->id)
                ->where('souscription_id', $service->souscription_id)
                ->set('quantite_utilise', $service->quantite_utilise)
                ->set('prix_couvert', $service->prix_couvert)
                ->set('etat', $service->etat)
                ->update();
        } else {
            if ($input['operateur'] == 'PORTE_FEUILLE') {
                // Vérifier que le portefeuille couvre les frais
                $portefeuille = model('PortefeuillesModel')->where('utilisateur_id', $utilisateur->id)->first();
                $prixCouvert  = $consultationInfos['prix'];
                if ($portefeuille->solde < $prixCouvert) {
                    $message  = "Le solde du portefeuille n'est pas suffisant pour payer ce service.";
                    $response = [
                        'statut'  => 'no',
                        'message' => $message,
                    ];
                    return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
                }
                // déduire le montant du portefeuille
                model('PortefeuillesModel')->where('utilisateur_id', $utilisateur->id)->set('solde', $portefeuille->solde - $prixCouvert)->update();
                $paiementInfo = [
                    'code'      => random_string('alnum', 10),
                    'montant'   => $prixCouvert,
                    'statut'    => PaiementEntity::VALIDE,
                    'mode_id'   => model("PaiementModesModel")->where('operateur', 'PORTE_FEUILLE')->findColumn('id')[0] ?? 1,
                    'auteur_id' => $utilisateur->id,
                ];
            } else {
                // Initialiser la transaction Monetbill
                try {
                    if (!$this->validate($pay_rules)) {
                        $hasError = true;
                        throw new \Exception('');
                    }
                } catch (\Throwable $th) {
                    $errorsData = $this->getErrorsData($th, isset($hasError));
                    $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
                    $response = [
                        'statut'  => 'no',
                        'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter ce Rendez-vous.",
                        'errors'  => $errorsData['errors'],
                    ];
                    return $this->sendResponse($response, $errorsData['code']);
                }
                $prixCouvert  = $consultationInfos['prix'];
                $paiementInfo = [
                    'code'      => random_string('alnum', 10),
                    'montant'   => $prixCouvert,
                    'statut'    => PaiementEntity::VALIDE,
                    'mode_id'   => model("PaiementModesModel")->where('operateur', $input['operateur'])->findColumn('id')[0] ?? 1,
                    'auteur_id' => $utilisateur->id,
                ];
                $monetbil_args = array(
                    'amount'      => $prixCouvert,
                    'phone'       => $input['telephone'] ?? $this->request->utilisateur->tel1,
                    'country'     => $input['pays'] ?? null,
                    'phone_lock'  => false,
                    'locale'      => 'fr', // Display language fr or en
                    'operator'    => $input['operateur'],
                    'item_ref'    => $consultationInfos['code'],
                    'payment_ref' => $paiementInfo['code'],
                    'user'        => $this->request->utilisateur->code,
                    'return_url'  => $input['returnURL'],
                    'notify_url'  => base_url('paiements/notfyConsult'),
                    'logo'        => base_url("uploads/images/logoinch.jpeg"),
                );
                // This example show payment url
                $data    = ['url' => \Monetbil::url($monetbil_args)];
                $message = "Paiement Initié.";
                $tranctionEtat = TransactionEntity::INITIE;
            }
        }

        // Enregistrement de la consultation
        $consultationInfos['statut'] = $prixCouvert >= $consultationInfos['prix'] ? ConsultationEntity::VALIDE : ConsultationEntity::ENATTENTE;
        $consultationInfos['id'] = $consultModel->insert($consultationInfos);

        // Ouvrir la transaction et faire le paiement par l'assureur pour la souscription
        $transactInfo = [
            "code"            => random_string('alnum', 10),
            "motif"           => "Consultation " . $consultationInfos['code'],
            "beneficiaire_id" => $utilisateur->id,
            "prix_total"      => $consultationInfos['prix'],
            "tva_taux"        => 0, //$tva->taux,
            "valeur_tva"      => 0, //$prixTVA,
            "net_a_payer"     => $consultationInfos['prix'],
            "avance"          => $prixCouvert,
            "reste_a_payer"   => $consultationInfos['prix'] - $prixCouvert,
            "pay_option_id"   => 1,
            "etat"            => $prixCouvert >= $consultationInfos['prix'] ? TransactionEntity::TERMINE : TransactionEntity::EN_COURS,
        ];

        $ligneInfo = [
            "produit_id"         => $consultationInfos['id'],
            "produit_group_name" => 'Consultation',
            "souscription_id"    => isset($souscription) ? $souscription->id : null,
            "quantite"           => 1,
            "prix_unitaire"      => $consultationInfos['prix'],
            "prix_total"         => $consultationInfos['prix'],
            // "reduction_code"     => $reduction->code,
            "prix_reduction"     => 0,
            "prix_total_net"     => $consultationInfos['prix'],
        ];

        // vérification de la dispnibilité
        $agenda = model("AgendasModel")->where('proprietaire_id', $consultationInfos['medecin_user_id'])
            ->where('jour_dispo', $consultationInfos['date'])
            ->where('heure_dispo_debut <=', $consultationInfos['heure'])
            ->where('heure_dispo_fin >=', $consultationInfos['heure'])
            ->first();
        $slotID = $input['idCreneau'];
        $slotExist = array_filter($agenda->slots, function ($slot) use ($slotID) {
            return $slot['id'] == $slotID;
        });
        if (!$slotExist) {
            $response = [
                'statut'  => 'no',
                'message' => 'la date et l\'heure du rendez-vous sont invalides.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        } else {
            $agenda->removeSlot($slotID);
        }
        // Mise à jour de l'agenda du médecin
        if (!isset($tranctionEtat)) { // Soit qu'il n'a pas payé via MonetBill
            model("AgendasModel")->where('id', $agenda->id)
                ->set('slots', $agenda->slots)
                // ->set('etat', (bool)count($agenda->slots))
                ->update();
        } else {
            $transactInfo['etat'] = $tranctionEtat;
        }

        $ligneInfo['id'] = model("LignetransactionsModel")->insert($ligneInfo);
        $transactInfo['id'] = model("TransactionsModel")->insert($transactInfo);
        model("TransactionLignesModel")->insert(['transaction_id' => $transactInfo['id'], 'ligne_id' => $ligneInfo['id']]);
        $paiementInfo['transaction_id'] = $transactInfo['id'];
        model("PaiementsModel")->insert($paiementInfo);

        model("ConsultationsModel")->db->transCommit();

        $response = [
            'statut'  => 'ok',
            'message' => "Rendez-vous ajouté.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie un rdv (l'objet, la date et/ou l'heure)
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($identifier)
    {
        $rules = [
            'heure' => [
                'rules'  => 'if_exist|valid_date[H:i]',
                'errors' => ['valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'date'  => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'Format de date attendu YYYY-MM-DD.']
            ],
            'objet' => 'if_exist',
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour ce Rendez-vous.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        $identifier = $this->getIdentifier($identifier);
        $rdv = model("RdvsModel")->where($identifier['name'], $identifier['value']);
        foreach ($input as $key => $value) {
            $rdv->set($key, $value);
        }
        $rdv->update();

        $response = [
            'statut'  => 'ok',
            'message' => 'Rendez-vous mis à jour.',
        ];
        return $this->sendResponse($response);
    }

    /** @todo think about the use cases
     * Supprime une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
    }

    /**
     * Génère un code unique pour chaque consultation
     *
     * @return string code
     */
    private function generatecodeConsult($consultModel = null)
    {
        return 'CS' . date('ymdHis');
        // $codePrefixe= Services::getDBPrefix() ?? "CS";
        // $codegenerate=$codePrefixe.$this->generateCode(6); 
        // $verif_exist=$consultModel->where('code',$codegenerate)->first();
        // if($verif_exist===null){
        //     return $codegenerate;
        // }else{
        //     $this->generatecodeConsult($consultModel);
        // }
    }
}
