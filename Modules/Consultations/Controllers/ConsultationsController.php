<?php

namespace Modules\Consultations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
use Config\Services;
use Modules\Consultations\Entities\AvisExpertEntity;
use Modules\Consultations\Entities\ConsultationEntity;
use Modules\Consultations\Entities\OrdonnanceEntity;
use Modules\Paiements\Entities\PaiementEntity;
use Modules\Paiements\Entities\TransactionEntity;

// use CodeIgniter\CodeIgniter;

class ConsultationsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des consultations d'un utilisateur
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
        $consults = model("ConsultationsModel")
            ->where("medecin_user_id", $utilisateur->id)
            ->orwhere("patient_user_id", $utilisateur->id)
            // ->groupBy('patient_user_id', 'desc')
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => (count($consults) ? count($consults) : 'Aucune') . ' consultation(s) trouvée(s).',
            'data'    => $consults,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retourne la liste de toutes les consultations enregistrées
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

        $consults = model("ConsultationsModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => (count($consults) ? count($consults) : 'Aucune') . ' consultation trouvée(s).',
            'data'    => $consults,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Returne les détails d'une consultation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function show($identifier)
    {
        $identifier = $this->getIdentifier($identifier);
        $consult = model("ConsultationsModel")->where($identifier['name'], $identifier['value'])->first();
        if (!(
            $consult &&
            (auth()->user()->inGroup('administrateur') ||
                $consult->medecin_user_id['idUtilisateur'] == $this->request->utilisateur->id ||
                $consult->patient_user_id['idUtilisateur'] == $this->request->utilisateur->id)
        )) {
            $response = [
                'statut'  => 'no',
                'message' => $consult ? ' Action non authorisée pour ce profil.' : "Consultation Inconnue.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        if ($consult->withExpertise) {
            $consult->expertise;
            $consult->expertise ? $consult->expertise->documents : null;
        }
        if ($consult->isSecondAdvice) {
            $consult->previous;
        }
        $consult->documents;
        $consult->ordonnance;
        $consult->transaction;
        $response = [
            'statut'  => 'ok',
            'message' => 'Détails de la consultation.',
            'data'    => $consult,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie une consultation (bilan, objet, documents, heure et date)
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
            'objet'      => 'if_exist',
            'bilan'      => 'if_exist',
            'statut'     => 'if_exist',
            "documents"  => 'if_exist|uploaded[documents]',
            "titres"     => [
                'rules'  => 'required_with[documents]',
                'errors' => ['required_with' => "Précisez les titres de documents."],
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour ce Rendez-vous.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);
        $documents = $this->request->getFiles() ?? null;

        $identifier = $this->getIdentifier($identifier);
        $consult = model('ConsultationsModel')->where($identifier['name'], $identifier['value'])->first();
        if (isset($input['bilan'])) {
            if ($this->request->utilisateur->id != $consult->medecin_user_id['idUtilisateur']) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Cette modification ne vous est pas authorisée.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
        }
        model("ConsultationsModel")->db->transBegin();
        if (isset($input['heure']) || isset($input['date'])) {
            // Vérifier que les valeurs choisies sont disponibles dans l'agenda du médecin
            $date  = $input['date'] ?? $consult->date;
            $heure = $input['heure'] ?? $consult->heure;
            $newAgenda = model("AgendasModel")->where('proprietaire_id', $consult->medecin_user_id['idUtilisateur'])
                ->where('jour_dispo', $date)
                ->where('heure_dispo_debut <=', $heure)
                ->where('heure_dispo_fin >=', $heure)
                ->first();
            $dispo = $newAgenda
                ? array_filter($newAgenda->slots, function ($sl) use ($heure) {
                    return (strtotime($sl['debut']) <= strtotime($heure)) && (strtotime($heure) < strtotime($sl['fin']));
                })
                : false;
            $dispo = reset($dispo);
            if (!$dispo) {
                $response = [
                    'statut'  => 'no',
                    'message' => "Aucune disponibilité dans l'agenda du médecin pour la date/heure choisie.",
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_NOT_FOUND);
            }

            $oldAgenda = model("AgendasModel")->where('proprietaire_id', $consult->medecin_user_id['idUtilisateur'])
                ->where('jour_dispo', $consult->date)
                ->where('heure_dispo_debut <=', $consult->heure)
                ->where('heure_dispo_fin >=', $consult->heure)
                ->first();
            $heure = $consult->heure;
            $oldDispo = $newAgenda
                ? array_filter($oldAgenda->slots, function ($sl) use ($heure) {
                    return (strtotime($sl['debut']) <= strtotime($heure)) && (strtotime($heure) < strtotime($sl['fin']));
                })
                : false;
            $oldDispo = reset($oldDispo);

            // modifier l'agenda du médecin
            $newAgenda->unsetSlot($dispo['id']);
            $oldAgenda->setSlot($oldDispo['id']);
            model("AgendasModel")->where('id', $newAgenda->id)->set('slots', $newAgenda->slots)->update();
            model("AgendasModel")->where('id', $oldAgenda->id)->set('slots', $oldAgenda->slots)->update();
        }
        if (isset($input['statut'])) {
            $statut = array_search(ucwords($input['statut']), ConsultationEntity::statuts);

            if (!($statut === null || $statut === false)) {
                $input['statut'] = (int)$statut;
            } else {
                $message = "Le statut n'est pas valide.";
                unset($input['statut']);
            }
        }
        // $cons = model("ConsultationsModel")->where($identifier['name'],  $identifier['value']);
        $cons = model("ConsultationsModel")->where('id', $consult->id);

        foreach ($input as $key => $value) {
            $cons->set($key, $value);
        }
        // if (isset($input['bilan'])) {
        //     $cons->set("statut", ConsultationEntity::TERMINE);
        // }

        $input ? $cons->update() : null;
        $titles = [];
        // if (isset($input['documents'])) {
        if ($documents) {
            if ($input['titres']) {
                $input['titres'] = json_decode($input['titres']);
                $cpt = 0;
                foreach ($documents as $doc) {
                    $title = $input['titres'][$cpt] ?? $doc->getClientName();
                    $titles[] = $title;
                    $docInfo = getDocInfo($title, $doc, 'uploads/consultations/documents/');
                    $docInfo['titre'] = $title;
                    $docID   = model('DocumentsModel')->insert($docInfo);

                    model("ConsultationDocumentsModel")->insert(["consultation_id" => $consult->id, "document_id" => $docID]);
                    $data['documents'][] = ['idDocument' => $docID, 'url' => base_url($docInfo['uri']), 'titre' => $title];
                    $cpt++;
                }
            } else {
                $response = [
                    'statut'  => 'no',
                    'message' => "Les titres des documents sont requis.",
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
        }
        model("ConsultationsModel")->db->transCommit();

        $response = [
            'statut'  => 'ok',
            'message' => $message ?? 'Consultation mise à jour.',
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une ordonnance à cette consultation
     *
     * @param  int|string $identifier
     * @return ResponseInterface The HTTP response.
     */
    public function setOrdonnance($identifier)
    {
        // restrint seulement aux médecins
        // if (!auth()->user()->inGroup('medecin')) {
        //     $response = [
        //         'statut' => 'no',
        //         'message' => 'Action non authorisée pour ce profil.',
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        // }

        $rules = [
            'instructions'  => 'if_exist',
            'observations'  => [
                'rules'  => 'required',
                'errors' => ['required' => "Précisez les observations."]
            ],
            "prescription.*.dosage" => [
                "rules" => 'if_exist',
                "errors"  => [
                    'required' => 'précisez le dosage de chaque prescription.',
                ],
            ],
            "prescription.*.frequence" => [
                "rules" => 'if_exist',
                "errors"  => [
                    'required' => 'précisez la frequence de chaque prescription.',
                ],
            ],
            "prescription.*.medecine" => [
                "rules" => 'required',
                "errors"  => [
                    'required' => 'précisez le médicament de chaque prescription.',
                ],
            ],
            "prescription.*.length" => 'if_exist',
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'envoyer cette demande.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        $identifier = $this->getIdentifier($identifier);
        $consult = model("ConsultationsModel")->where($identifier['name'], $identifier['value'])->first();
        if (!$consult) {
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'identifier la consultation.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        // Restrint au médecin auteur de la consultation
        if ($consult->medecin_user_id['idUtilisateur'] != $this->request->utilisateur->idUtilisateur) {
            $response = [
                'statut'  => 'no',
                'message' => "Vous n'êtes pas authorisé à effectuer cette consultation.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        // enregistre l'ordonnance ou met à jour si une existe déjà
        $input['consultation_id'] = $consult->id;
        $ordonnance = model("OrdonnancesModel")->where('consultation_id', $consult->id)->first();
        if ($ordonnance) {
            $ordonnance->fill($input);
            model("OrdonnancesModel")->update($ordonnance->id, $ordonnance);
        } else {
            $ordonnance = new OrdonnanceEntity($input);
            $ordonnance->id = model("OrdonnancesModel")->insert($ordonnance);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Ordonnance Enregistrée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Initie une demande d'avis d'expert pour cette consultation
     *
     * @param  mixed $identifier
     * @return ResponseInterface The HTTP response.
     */
    public function askAvisExpert($identifier)
    {
        // restrint seulement aux médecins
        if (!auth()->user()->inGroup('medecin')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'description'  => 'if_exist',
            'idMedecin'  => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => "Précisez le destinataire la demande.", 'numeric' => 'Identification de médecin invalide.']
            ],
            // 'skill' => [
            //     'rules'  => 'required|is_not_unique[skills.nom]',
            //     'errors' => ['required' => "Précisez la compétence d'expertise.", 'is_not_unique' => 'Compétence de consultation invalide.']
            // ],
            'idSkill'    => [
                'rules'  => 'required|is_not_unique[skills.id]',
                'errors' => ['required' => 'Précisez la compétence de l\'expertise.', 'is_not_unique' => 'Compétence de consultation invalide.']
            ],
            "documents"  => 'if_exist|uploaded[documents]',
            "titres"     => [
                'rules'  => 'required_with[documents]',
                'errors' => ['required_with' => "Précisez les titres de documents."],
            ],
            // 'withAssur' => [
            //     'rules'  => 'required|permit_empty',
            //     'errors' => ['required' => 'Vous devez préciser si avec assurance ou non.']
            // ],
            'idSouscription' => [
                'rules'  => 'required_with[withAssur]|permit_empty|is_not_unique[souscriptions.id]',
                'errors' => ['required' => 'Souscription requise pour consultation avec asssurance.']
            ],
            "attachements.*" => [
                "rules" => 'if_exist|is_not_unique[documents.id]',
                "errors"  => [
                    'is_not_unique' => 'Document inconu.',
                ],
            ],
        ];
        // $pay_rules = [
        //     'operateur'  => [
        //         'rules'  => 'required|is_not_unique[paiement_modes.operateur]',
        //         'errors' => ['required' => 'Opérateur non défini.', 'is_not_unique' => 'Opérateur invalide'],
        //     ],
        //     'telephone'  => [
        //         'rules'  => 'required|numeric',
        //         'errors' => ['required' => 'Numéro de téléphone requis pour ce mode de paiement.', 'numeric' => 'Numéro de téléphone invalide.']
        //     ],
        //     'returnURL'  => [
        //         'rules'  => 'required|valid_url',
        //         'errors' => ['required' => 'L\'URL de retour doit être spécifiée pour ce mode de paiement.', 'valid_url' => 'URL de retour non conforme.']
        //     ],
        //     // 'pays'       => [
        //     //     'rules'  => 'if_exist|is_not_unique[paiement_pays.code]',
        //     //     'errors' => ['is_not_unique' => 'Pays non pris en charge.'],
        //     // ],
        //     'operateur'  => [
        //         'rules'  => 'required|is_not_unique[paiement_modes.operateur]',
        //         'errors' => ['required' => 'Opérateur non défini.', 'is_not_unique' => 'Opérateur invalide'],
        //     ],
        // ];
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'envoyer cette demande.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        // try {
        //     if (!$this->validate($pay_rules)) {
        //         $hasError = true;
        //         throw new \Exception('');
        //     }
        // } catch (\Throwable $th) {
        //     $errorsData = $this->getErrorsData($th, isset($hasError));
        //     $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
        //     $response = [
        //         'statut'  => 'no',
        //         'message' => $validationError ? $errorsData['errors'] : "Impossible d'envoyer cette demande.",
        //         'errors'  => $errorsData['errors'],
        //     ];
        //     return $this->sendResponse($response, $errorsData['code']);
        // }
        $input = $this->getRequestInput($this->request);

        $skill = model('MedecinSkillsModel')
            ->select('nom, description, skills.id, medecin_id, description_perso, cout, isExpert, cout_expert')
            ->join('skills', 'skills.id = medecin_skills.skill_id')
            ->where('skill_id', $input['idSkill'])
            ->where('medecin_id', $input['idMedecin'])
            ->first() ?? [];

        $identifier = $this->getIdentifier($identifier);

        $consult = model("ConsultationsModel")->where($identifier['name'], $identifier['value'])->first();
        if (!$consult) {
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'identifier la consultation.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        // si paiement avec souscription
        if (isset($input['withAssur']) && $input['withAssur']) {
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

            // Enregistrement des infos de paiement
            $paiementInfo = [
                'code'      => random_string('alnum', 10),
                'montant'   => $prixCouvert,
                'statut'    => PaiementEntity::VALIDE,
                'mode_id'   => model("PaiementModesModel")->where('operateur', 'PORTE_FEUILLE')->findColumn('id')[0] ?? 1,
                'auteur_id' => $souscription->assurance->assureur_id,
            ];
        } else {
            $prixCouvert = 0;
        }
        // on enregistre les informations avec le statut en attente.
        // enregistrement Avis
        $avisInfo = [
            'medecin_sender_id'   => $this->request->utilisateur->id,
            'medecin_receiver_id' => $input['idMedecin'],
            'consultation_id'     => $consult->id,
            'skill'               => $skill['nom'],
            'cout'                => (float)$skill['cout_expert'],
            'description'         => $input['description'] ?? null,
            'statut'              => $prixCouvert > $skill['cout_expert'] ? AvisExpertEntity::EN_COURS : AvisExpertEntity::EN_ATTENTE,
        ];
        $avisInfo = array_filter($avisInfo);
        model("AvisExpertModel")->db->transBegin();
        $avisInfo['id'] = model("AvisExpertModel")->insert($avisInfo);
        model("ConsultationsModel")->update($consult->id, ["withExpertise" => true]);
        if (isset($input['attachements'])) {
            $idAvis  = $avisInfo['id'];
            $attachs = array_map(function ($e) use ($idAvis) {
                return ['avis_id' => $idAvis, 'document_id' => $e];
            }, $input['attachements']);
            model('AvisAttachementsModel')->insertBatch($attachs);
        }
        // Enregistrement Transaction
        $transactInfo = [
            "code"            => random_string('alnum', 10),
            "motif"           => "Avis Expert pour consultation " . $consult->code,
            "beneficiaire_id" => $consult->patient_user_id['idUtilisateur'],
            "prix_total"      => $skill['cout_expert'],
            "tva_taux"        => 0, //$tva->taux,
            "valeur_tva"      => 0, //$prixTVA,
            "net_a_payer"     => $skill['cout_expert'],
            "avance"          => $prixCouvert,
            "reste_a_payer"   => $skill['cout_expert'] - $prixCouvert,
            "pay_option_id"   => 1,
            "etat"            => $prixCouvert >= $skill['cout_expert'] ? TransactionEntity::TERMINE : TransactionEntity::EN_COURS,
        ];
        // Enregistrement Ligne de transaction
        $ligneInfo = [
            "produit_id"         => $avisInfo['id'],
            "produit_group_name" => 'Avis Expert',
            "souscription_id"    => isset($souscription) ? $souscription->id : null,
            "quantite"           => 1,
            "prix_unitaire"      => $skill['cout_expert'],
            "prix_total"         => $skill['cout_expert'],
            // "reduction_code"     => $reduction->code,
            "prix_reduction"     => 0,
            "prix_total_net"     => $skill['cout_expert'],
        ];

        $ligneInfo['id'] = model("LignetransactionsModel")->insert($ligneInfo);
        $transactInfo['id'] = model("TransactionsModel")->insert($transactInfo);
        model("TransactionLignesModel")->insert(['transaction_id' => $transactInfo['id'], 'ligne_id' => $ligneInfo['id']]);

        if (isset($input['withAssur']) && $input['withAssur']) {
            $paiementInfo['transaction_id'] = $transactInfo['id'];
            model("PaiementsModel")->insert($paiementInfo);
        }

        // Enregistre les documents associés
        if (isset($input['titres'])) {
            $files     = $this->request->getFiles();
            $documents = $files["documents"] ?? [];
            $cpt = 0;
            foreach ($documents as $doc) {
                $title = $input['titres'][$cpt] ?? $doc->getClientName();
                $docInfo = getDocInfo($title, $doc, 'uploads/messages/documents/');
                $docInfo['titre'] = $title;
                $docID   = model('DocumentsModel')->insert($docInfo);

                model("AvisDocumentsModel")->insert(["avis_id" => $avisInfo['id'], "document_id" => $docID]);
                $data['documents'][] = ['idDocument' => $docID, 'url' => base_url($docInfo['uri']), 'titre' => $title];
                $cpt++;
            }
        }
        model("AvisExpertModel")->db->transCommit();
        $sender   = $this->request->utilisateur;
        $receiver = model("UtilisateursModel")
            ->select('email, tel1, nom, prenom')
            ->where('id', $input['idMedecin'])
            ->first();
        // Notifie en cas de paiement complet
        $patient = model("UtilisateursModel")->where('id', $consult->patient['idUtilisateur'])->first();
        if ($prixCouvert >= $skill['cout_expert']) {
            $recipients = [$sender, $patient];
            $this->sendAvisDemandConfirmedMail($recipients, $skill->nom);
        } else {
            $this->sendAvisDemandConfirmedMail([$sender], $skill->nom);
            $this->sendAvisToPayMail($patient);
        }
        $this->sendAvisDemandedMail($receiver);

        $response = [
            'statut'  => 'ok',
            'message' => 'Demande transmise.',
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    public function getVilles()
    {
        $villes = model("VillesModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($villes) ? count($villes) : 'Aucune') . ' ville(s) trouvé(s).',
            'data'    => $villes,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une localisation de consultation pour un médecin
     * (réservée aux médecins uniquement)
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     *//*
    public function addLocalisation()
    {
        $rules = [
            'etablissement' => [
                'rules' => 'required|alpha_numeric_punct',
                'errors' => ['required' => 'Précisez l\'établissement.', 'alpha_numeric_punct' => 'Valeur inappropriée.']
            ],
            'adresse'      => [
                'rules' => 'required|alpha_numeric_punct',
                'errors' => ['required' => 'Précisez l\'adresse.', 'alpha_numeric_punct' => 'Valeur inappropriée.']
            ],
            'ville'        => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Précisez la ville.', 'numeric' => 'Valeur inappropriée.']
            ],
            'canal'        => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Précisez le canal.', 'numeric' => 'La valeur de canal inconnue.']
            ],
            'isdefault'    => [
                'rules' => 'if_exist|in_list[0,1]',
                'errors' => ['in_list' => 'Valeur inconnue.']
            ],
            // 'competences'  => ['rules' => 'required',
            //                    'errors' => ['required' => 'Précisez au moins une compétence.']
            //             ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'statut' => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $input = $this->getRequestInput($this->request);

        $userModel = new UtilisateurModel();
        $user      = $userModel->asArray()->where('email', $this->request->userEmail)->first();
        $userID    = $user['id_utilisateur'];

        // $skillsModel  = new SkillsModel();
        $medCanModel          = new MedecinCanauxModel();
        $canal['canal_id']    = (int)$input['canal'];
        $canal['user_med_id'] = $userID;
        try {
            $medCanModel->insert($canal);
        } catch (\Throwable $th) {
        }
        // $canaux       = MedecincanneauxModel::getcaneauxList();
        // $medCanModel  = new MedecincanneauxModel();
        // $canaux       = MedecincanneauxModel::getcaneauxList();

        $medLocModel           = new MedecinLocalisationModel();
        $data['etablissement'] = htmlspecialchars($input['etablissement']);
        $data['adresse']       = htmlspecialchars($input['adresse']);
        $data['user_med_id']   = $userID;
        $data['ville_id']      = (int)$input['ville'];
        // $data['type'] = $canaux[(int)$input['canal']-1]['name'];
        // $data['skills']    = json_encode($skillsModel->getbulkSkillsNames($input['competences']));
        $data['default'] = isset($input['isdefault']) ? (int)$input['isdefault'] : 0;
        $medLocModel->insert($data);


        $response = [
            'statut'  => 'ok',
            'message' => 'Adresse Ajoutée.',
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }*/

    /**
     * Vérifie le code de consultation fourni.
     *
     * @param string $consultCode
     * @return ResponseInterface The HTTP response.
     */
    public function verifCode(string $consultCode)
    {
        $consult = model("consultationsModel")
            ->where("code", $consultCode)
            ->first();

        if (!$consult) {
            $response = [
                'statut'  => 'no',
                'message' => 'Consultation introuvable.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        $statuts = [null, ConsultationEntity::statuts[ConsultationEntity::VALIDE], ConsultationEntity::statuts[ConsultationEntity::ENCOURS], ConsultationEntity::statuts[ConsultationEntity::TRANSMIS]];
        if (!array_search($consult->statut, $statuts)) {
            echo "trouvé";
            $response = [
                'statut'  => 'no',
                'message' => "Consultation $consult->statut ne peut être démarrée.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        if ($consult->medecin_user_id['idUtilisateur'] != $this->request->utilisateur->idUtilisateur) {
            $response = [
                'statut'  => 'no',
                'message' => "Vous n'êtes pas authorisé à effectuer cette consultation.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        $dateCode = new \DateTime($consult->date . ' ' . $consult->heure);
        $dateCode->modify("+" . ConsultationEntity::EPIRATION_TIME . " days");
        if (strtotime(date("Y-m-d H:i:s") > strtotime($dateCode->format('Y-m-d H:i:s')))) {
            model("consultationsModel")->update($consult->id, ['statut' => ConsultationEntity::EXPIREE]);
            $response = [
                'statut'  => 'no',
                'message' => "Consultation expirée le $dateCode.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        // if ($consult->statut == ConsultationEntity::statuts[ConsultationEntity::VALIDE]) {
        //     $statut = ConsultationEntity::statuts[ConsultationEntity::ENCOURS];
        //     model("consultationsModel")->update($consult->id, ["statut" => ConsultationEntity::ENCOURS]);
        // }
        $response = [
            'statut' => 'ok',
            'message' => 'Code valide.',
            // 'data'    => ["code" => $consult->code, "statut" => $statut ?? $consult->statut]
        ];
        return $this->sendResponse($response);
    }


    /**
     * generateCode()
     * 
     * genere un code alphanumérique aléatoire dont la longeur est spécifiée en paramètre
     * 
     * @param length_of_string int 
     * @return string code génére
     */
    private function generateCode($length_of_string)
    {
        // String of acepted alphanumeric character
        $str_result = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ#$!';
        return substr(str_shuffle($str_result), 0, $length_of_string);
    }

    // private function sendRdvAddedMail(string $recipient, string $nomComplet, string $date, string $heure)
    public static function sendRdvAddedMail(string $recipient, string $nomComplet, string $date, string $heure)
    {
        $date = date('d M Y', strtotime($date));
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient);
        $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
        $email->setSubject('Nouveau Rendez-vous');
        $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Vous venez de recevoir une demande de consultation Pour la date du $date à $heure.<br><br>
                            InchAssur-" . date('d-m-Y H:i'));
        $tentative = 0;
        while ($tentative < 3) {
            try {
                $email->send();
                return true;
            } catch (\Exception $e) {
                log_message('warnig', $e->getMessage());
            }
            $tentative++;
        }
        return false;
    }

    public static function sendAvisToPayMail(object $recipient)
    {
        $msg  = "Demande d'expertise initiée, Rendez-vous dans votre espace personnel sous la rubrique consultations pour procéder au paiement.";
        $dest = [$recipient['tel1']];
        sendSmsMessage($dest, "InchAssur", $msg);

        $nomComplet = $recipient['nom'] . " " . $recipient['prenom'];
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient['email']);
        $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
        $email->setSubject('Demande Avis Expert Initiée');
        $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Votre demande d'avis d'expert à bien été initiée, Rendez-vous dans votre espace personnel,
                            sous la rubrique consultations afin de procéder au paiement.<br><br>
                            InchAssur-" . date('d-m-Y H:i'));
        $tentative = 0;
        while ($tentative < 3) {
            try {
                $email->send();
                return true;
            } catch (\Exception $e) {
                log_message('warnig', $e->getMessage());
            }
            $tentative++;
        }

        return false;
    }

    public static function sendAvisDemandedMail(object $recipient)
    {
        $msg  = "Bonjour " . $recipient->prenom . ", Vous venez de recevoir une demande d'avis expert. Merci de faire confiance à IncHAssur.";
        $dest = [$recipient->tel1];
        sendSmsMessage($dest, "InchAssur", $msg);

        $nomComplet = $recipient->nom . " " . $recipient->prenom;
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient->email);
        $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
        $email->setSubject('Nouveau Rendez-vous');
        $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Vous venez de recevoir une demande d'avis expert.<br><br>
                            InchAssur-" . date('d-m-Y H:i'));
        $tentative = 0;
        while ($tentative < 3) {
            try {
                $email->send();
                return true;
            } catch (\Exception $e) {
                log_message('warnig', $e->getMessage());
            }
            $tentative++;
        }
        return false;
    }

    // private function sendRdvConfirmedMail(string $recipient, string $date, string $heure, string $code)
    public static function sendRdvConfirmedMail(object $recipient, string $date, string $heure, string $code)
    {
        $nomComplet = $recipient->nom . " " . $recipient->prenom;
        $date = date('d-M-Y', strtotime($date));

        $msg  = "Bonjour $recipient->prenom, votre rendez-vous numéro $code du $date à $heure est confirmé. Merci de faire confiance à IncHAssur.";
        $dest = [$recipient->tel1];
        sendSmsMessage($dest, "InchAssur", $msg);

        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient->email);
        $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
        $email->setSubject('Confirmation de consultation');
        $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Rendez-vous numéro $code du $date à $heure confirmé.<br>
                            <br>Merci de le rajouter à votre agenda.<br><br><br>
                            InchAssur-" . date('d-m-Y H:i'));
        $tentative = 0;
        while ($tentative < 3) {
            try {
                $email->send();
                return true;
            } catch (\Exception $e) {
                log_message('warnig', $e->getMessage());
            }
            $tentative++;
        }
        return false;
    }

    public static function sendAvisDemandConfirmedMail(array $recipients, string $skillName)
    {
        $email = Services::email();
        foreach ($recipients as $recipient) {
            $nomComplet = $recipient->nom . " " . $recipient->prenom;

            $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
            $email->setTo($recipient);
            $email->setCC(['tonbongkevin@gmail.com', 'ibikivan1@gmail.com']);
            $email->setSubject('Confirmation de consultation');
            $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Votre demande d'expertise pour $skillName a été envoyée.<br>
                            InchAssur-" . date('d-m-Y H:i'));
            // $tentative = 0;
            // while ($tentative < 3) {
            //     try {
            $email->send();
            //     } catch (\Exception $e) {
            //         log_message('warnig', $e->getMessage());
            //     }
            //     $tentative++;
            // }

            $msg  = "Demande d'expertise pour $skillName envoyée.";
            $dest = [$recipient->tel1];
            sendSmsMessage($dest, "InchAssur", $msg);
        }
        return true;
    }
}
