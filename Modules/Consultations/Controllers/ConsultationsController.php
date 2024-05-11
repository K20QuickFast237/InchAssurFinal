<?php

namespace Modules\Consultations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
// use CodeIgniter\CodeIgniter;

class ConsultationsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;


    public function getVilles()
    {
        $villes = model("VillesModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => count($villes) . ' ville(s) trouvée(s).',
            'message' => (count($villes) ? count($villes) : 'Aucune') . ' ville(s) trouvé(s).',
            'data'    => $villes,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une localisation de consultation pour un médecin
     * réservée aux médecins uniquement
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
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
    }




    /****************************************************************/

    /**
     * getMotifs
     * 
     * Renvoie la liste des motifs disponibles
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getMotifs()
    {
        $data = model("MotifsModel")->findAll();

        if ($data === []) {
            $response = [
                'statut'  => 'no',
                'message' => 'Aucun motif disponible.',
                'data'    => [],
            ];
            return $this->sendResponse($response);
        }

        $response = [
            'statut'  => 'ok',
            'message' => count($data) . ' motif(s) trouvé(s).',
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * addMotif
     * 
     * Ajoute un motif
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addMotif()
    {
        $rules = [
            'nom' => 'required|is_unique[IA_motifs.nomMotif]',
        ];
        $input = $this->getRequestInput($this->request);

        if (!$this->validateRequest($input, $rules)) {
            $response = [
                'statut' => 'no',
                'message' => 'Motif déjà existant.',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $modelMotif = new motifModel();
        $motifInfos = [
            'nomMotif'  => (string)htmlspecialchars($input['nom']),
            'description' => (string)htmlspecialchars($input['description'] ?? ''),
        ];
        $data['id_motil'] = $modelMotif->insert($motifInfos);
        $data['nom'] = $motifInfos['nomMotif'];
        $data['description'] = $motifInfos['description'];
        $response = [
            'statut'        => 'ok',
            'message'       => 'Motif ajouté avec succès.',
            'data'          => $data,
        ];
        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * updateMotif
     * 
     * Met à jour le motif dont l'identifiant est passé en paramètre
     * 
     * @var id_motif identifiant du motif
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateMotif(int $id_motif)
    {
        $rules = [
            'nomMotif' => [
                'rules' => 'if_exist|is_unique[IA_motifs.nomMotif]',
                'errors' => [
                    'is_unique' => 'Un motif existe déjà avec ce nom',
                ],
            ],
        ];

        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            $response = [
                'statut' => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $input = $this->getRequestInput($this->request);
        $modelService = new motifModel();
        if (isset($input['nomMotif'])) {
            $data['nomMotif'] = $input['nomMotif'];
        }
        if (isset($input['description'])) {
            $data['description'] = $input['description'];
        }

        $modelService->update($id_motif, $data);
        // $data['allService']=$modelService->asArray()->findall();
        $input['id_motif'] = $id_motif;
        $response = [
            'statut'        => 'ok',
            'message'       => 'Motif Modifé avec succès.',
            'data'          => $input,
        ];
        return $this->getResponse($response);
    }

    /**
     * newRdv
     * 
     * Ajoute un rendez-vous où une consultation si le paramètre $consult est spécifié.
     * NB: un rendez-vous est une consultation en Attenete avec bilan vide
     *
     * @param  bool $consult indique quúne consultattion doit être ajoutée
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function newRdv($consult = false)
    {
        // $id = $consultModel->insert($consultationInfos);
        // $input['id_consultation'] = $id;
        $input = $this->request->getPost() ? $this->request->getPost() : json_decode($this->request->getBody(), true);

        $input['userEmail'] = $this->request->userEmail;
        $userID = UtilisateurModel::staticGetIdByCode($input['destinataire']);
        $consult = $this->saveRDV($input, $consult);
        // retirer la disponibilité dans l'agenda du médecin
        $date  = date('Y-m-d', strtotime($input['heure'] ?? date('Y-m-d')));
        $heure = date('H:i', strtotime($input['heure'] ?? date('H:i')));
        $agendaModel = new AgendaModel();
        $agendaMed   = $agendaModel->asArray()->select('id_agenda, slots')
            ->where('proprietaire_user_id', $userID)
            ->where('jour_dispo', $date)
            ->first();
        // ->findAll();
        // $agendaMed = $agendaModel->asArray()->first();\
        //vide le slot
        $agendaslots = $agendaMed['slots'];
        // echo "nombre: ".count($agendaslots);
        for ($i = 0; $i < count($agendaslots); $i++) {
            if ($agendaslots[$i]['debut'] == $heure) {
                // isset($agendaslots[$i]['visible']) ? $agendaslots[$i]['visible'] == 0 : $agendaslots[$i]['visible'] = 0;
                $agendaslots[$i]['visible'] = 0;
                $agendaModel->set('slots', json_encode($agendaslots))
                    ->where('id_agenda', $agendaMed['id_agenda'])
                    ->update();
                break;
            }
        }

        if ($consult['statut']) {
            $message = $consult['consultation']['message'] ?? null;
            unset($consult['consultation']['message']);
            $response = [
                'statut'  => 'ok',
                'message' => $message ?? 'Rendez-vous ajouté.',
                'data'    => $consult['consultation'],
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
        } else {
            $response = [
                'statut' => 'no',
                'message' => $consult['message'],
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }
    }

    /**
     * saveRDV
     * 
     * enregistre un rdv, ou une consultation si le second paramètre est true
     *
     * @param  array $input
     * @param  boolean $consult
     * @return void
     */
    public function saveRDV(array $input, $consult = false)
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'objet' => ['rules' => 'required', 'errors' => ['required' => 'Précisez l\'objet du Rendez-vous.']],
            'heure'  => [
                'rules' => 'required|valid_date[Y-m-d H:i]',
                'errors' => ['required' => "Précisez l'heure du Rendez-vous.", 'valid_date' => "Format d'heure attendu YYYY-MM-DD HH:ii."]
            ],
            /* Do not remove, for other format
                // 'date'  => ['rules' => 'required|valid_date[Y-m-d]',
                //             'errors' => ['required' => 'Précisez la date du Rendez-vous.', 'valid_date' => 'Format de date attendu YYYY-MM-DD.']
                //            ],
                // 'heure' => ['rules' => 'required|valid_date[H:i]',
                //             // 'errors' => ['required' => 'Précisez l\'heure du Rendez-vous.', 'regex_match' => 'Format d\'heure attendu hh:mm.']
                //             'errors' => ['required' => 'Précisez l\'heure du Rendez-vous.', 'valid_date' => 'Format d\'heure attendu hh:mm.']
                //            ],
            */
            // 'withAssur' => ['rules' => 'if_exist|numeric|in_list[0,1]', 
            // 'withAssur' => ['rules' => 'if_exist|string', 
            // 'errors' => ['if_exist' => 'Précisez si avec assurance ou non.', 'numeric' => 'Valeur inappropriée pour le statut avec ou sans assurance.', 'in_list' => 'Avec ou sans assurance ne peut être que 1 ou 0']
            // 'errors' => ['if_exist' => 'Précisez si avec assurance ou non.', 'string' => 'Valeur inappropriée pour le statut avec ou sans assurance.']
            //    ],
            'duree' => [
                'rules' => 'if_exist|numeric',
                'errors' => ['if_exist' => 'Précisez la duree du Rendez-vous', 'numeric' => 'Duree invalide.']
            ],
            'canal' => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Précisez le canal de consultation.', 'numeric' => 'Canal de consultation non numérique invalide.']
            ],
            'ville' => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Précisez la ville de consultation.', 'numeric' => 'Ville de consultation non numérique invalide.']
            ],
            'skill' => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Précisez la compétence de consultation.', 'numeric' => 'Compétence de consultation non numérique invalide.']
            ],
            'emetteur' => [
                'rules' => 'required|is_not_unique[IA_utilisateurs.code]',
                'errors' => ['is_not_unique' => 'Emetteur inconnu.', 'required' => 'Précisez l\'emetteur du Rendez-vous.']
            ],
            'destinataire' => [
                'rules' => 'required|is_not_unique[IA_utilisateurs.code]',
                'errors' => ['is_not_unique' => 'Destinataire inconnu.', 'required' => 'Précisez le destinataire du Rendez-vous.']
            ],
        ]);
        // $input = $request->getPost() ?? json_decode($request->getBody(), true);
        if (!$validation->run($input)) {
            return [
                'statut' => false,
                'message' => $validation->getErrors(),
            ];
        }

        $consultationInfos = [
            'objet'  => (string)htmlspecialchars($input['objet']),
            'description' => (string)htmlspecialchars($input['description'] ?? null),
            // 'date'  => date('Y-m-d', strtotime($input['date'] ?? date('Y-m-d'))), Do not remove, For other format
            'date'  => date('Y-m-d', strtotime($input['heure'] ?? date('Y-m-d'))),
            'heure' => date('H:i', strtotime($input['heure'] ?? date('H:i'))),
            // 'duree'  => (int)$input['duree'],
            // 'isAssured' => (int)$input['withAssur'] ?? 0,
            'canal_id'  => (int)$input['canal'],
            'ville_id'  => (int)$input['ville'],
            'skill_id'  => (int)$input['skill'],
            'prix'      => (float)$input['prix'],
            // 'isAssured' => (bool)$input['withAssur'],
            'bilan' => '',
            // 'statut'=> (int)ConsultationModel::ENATTENTE, // plus besoin de validation pour uns consultation payée
            'statut' => (int)ConsultationModel::VALIDE,
            'patient_user_id' => UtilisateurModel::staticGetIdByCode((string)$input['emetteur']),
            'medecin_user_id' => UtilisateurModel::staticGetIdByCode((string)$input['destinataire']),
        ];
        if (isset($input['withAssur'])) {
            $consultationInfos['isAssured'] = (int)$input['withAssur'] ? 1 : 0;
        }

        if (isset($input['duree'])) {
            $consultationInfos['duree'] = (int)$input['duree'];
        } else {
            $consultationInfos['duree'] = ConsultationModel::DEFAULT_DUREE;
        }

        // if (isset($input['withAssur'])) {
        if ($consultationInfos['isAssured']) {
            if (isset($input['assuranceID']) && isset($input['souscriptionID'])) {
                $souscriptID = (int)$input['souscriptionID'];
                $consultationInfos['assurance_id']    = (int)$input['assuranceID'];
                $consultationInfos['souscription_id'] = $souscriptID;

                //mettre a jour la souscription          note: on ne peut pas arriver ici avec une souscription a etat inactif
                $transactModel     = new TransactionModel();
                $souscriptionModel = new SouscriptionModel();
                $souscription = $souscriptionModel->asArray()->select('credit, max_consultation_nbr, dispo_consultation_nbr')
                    ->where('id_souscription', $souscriptID)
                    ->first();


                $credit = $souscription['credit'] ?? null;
                $maxConsult   = $souscription['max_consultation_nbr'] ?? null;
                $dispoConsult = $souscription['dispo_consultation_nbr'] ?? null;
                $prixProduit  = $consultationInfos['prix'];
                $payeurID     = ProduitModel::getProprioIDFromID($consultationInfos['assurance_id']);
                $benefID      = $consultationInfos['patient_user_id'];
                if ($maxConsult && ($dispoConsult >= 1) && ($credit > 1)) {
                    $souscription['dispo_consultation_nbr']--;
                    $souscription['credit'] = $souscription['credit'] - $consultationInfos['prix'];
                    $souscription['etat'] = (int)(
                        ($souscription['dispo_consultation_nbr'] ? SouscriptionModel::ACTIVE : SouscriptionModel::INACTIVE) ||
                        ($souscription['credit'] > 1 ? SouscriptionModel::ACTIVE : SouscriptionModel::INACTIVE)
                    );
                    $prix          = $prixProduit;
                    $transactionID = $transactModel->initiateTransaction($benefID, $prixProduit, $prix, $payeurID, "Souscription Consultation");
                } elseif ($maxConsult && ($dispoConsult >= 1) && ($credit > 0)) {
                    $souscription['dispo_consultation_nbr']--;
                    $souscription['etat'] = $souscription['dispo_consultation_nbr'] ? SouscriptionModel::ACTIVE : SouscriptionModel::INACTIVE;
                    $prix          = $prixProduit * $credit;
                    $transactionID = $transactModel->initiateTransaction($benefID, $prixProduit, $prix, $payeurID, "Souscription Consultation");
                } elseif (($credit > 1)) {
                    $newcredit = $souscription['credit'] - $consultationInfos['prix'];
                    $souscription['credit'] = $newcredit > 1 ? $newcredit : 0;
                    $souscription['etat']   = $newcredit > 1 ? SouscriptionModel::ACTIVE : SouscriptionModel::INACTIVE;
                    $prix          = $prixProduit;
                    $transactionID = $transactModel->initiateTransaction($benefID, $prixProduit, $prix, $payeurID, "Souscription Consultation");
                } elseif ($maxConsult && ($dispoConsult >= 1)) {
                    $souscription['dispo_consultation_nbr']--;
                    $souscription['etat'] = $souscription['dispo_consultation_nbr'] ? SouscriptionModel::ACTIVE : SouscriptionModel::INACTIVE;
                    $prix          = $prixProduit;
                    $transactionID = $transactModel->initiateTransaction($benefID, $prixProduit, $prix, $payeurID, "Souscription Consultation");
                }

                $souscriptionModel->update($souscriptID, $souscription);
            } else {
                echo "Identifiants d\'assurance et de souscription requis pour consultation avec asssurance";
                throw new \Exception("Identifiant d'assurance et de souscription requis pour une consultation avec assurance", 1);
            }
        }

        if (isset($input['isSecondAdvice'])) {
            $consultationInfos['isSecondAdvice'] = (bool)$input['isSecondAdvice'];
        }

        if (isset($input['previousID'])) {
            $consultationInfos['previous_id'] = (int)$input['previousID'];
        }

        if (isset($input['previousCode'])) {
            $prevConsult = ConsultationModel::getconsultDataFromCode($input['previousCode']);
            $consultationInfos['previous_id'] = $prevConsult->id_consultation;
        }

        $consultModel = new ConsultationModel();
        $consultationInfos['code'] = $this->generatecodeConsult($consultModel);

        if ($consult) {
            $consultationInfos['bilan']  = (string)(htmlspecialchars($input['bilan'] ?? ''));
            $consultationInfos['statut'] = (int)ConsultationModel::VALIDE;
            $input['message'] = 'Consultation Ajoutée.';
        } else {
            $consultationInfos['statut'] = (int)ConsultationModel::VALIDE;
        }

        $id = $consultModel->insert($consultationInfos);
        if (isset($transactionID)) { // ajout de la ligne de consultation correspondante
            $ligneTransactModel = new LigneTransactionModel();
            $ligneTransaction = [
                'transaction_id' => $transactionID,
                'produit_id'     => $id,
                'typeproduit_id' => TypeProduitModel::getID('Consultation'),
                'quantite'       => 1,
                'prixUnitaire'   => $consultationInfos['prix'],
                'prixTotal'      => $consultationInfos['prix'] * 1,
            ];
            $ligneTransactModel->insert($ligneTransaction);
        }
        $input['id_consultation'] = $id;
        return [
            'statut'      => true,
            'consultation' => $input,
        ];
    }

    /**
     * EConsult
     * 
     * Ajoute une consultation en ligne.
     *
     * @return void
     */
    public function EConsult()
    {
        $rules = [
            'motif' => ['rules' => 'required', 'errors' => ['required' => 'Précisez le motif de la consultation..']],
            'date'  => [
                'rules' => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['if_exist' => 'Précisez la date de la consultation..', 'valid_date' => 'Format de date attendu YYYY-MM-DD.']
            ],
            'heure' => [
                'rules' => 'if_exist|valid_date[H:i]',
                'errors' => ['if_exist' => 'Précisez l\'heure de la consultation..', 'valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'duree' => [
                'rules' => 'if_exist|numeric',
                'errors' => ['if_exist' => 'Précisez la duree de la consultation.', 'numeric' => 'Duree invalide.']
            ],
            'emetteur' => [
                'rules' => 'required|is_not_unique[IA_utilisateurs.code]',
                'errors' => ['is_not_unique' => 'Emetteur inconnu.', 'required' => 'Précisez l\'emetteur de la consultation..']
            ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'statut' => 'no',
                'message' => $this->validator->getErrors(),
                // 'message'=> 'Adresse Email ou numéro de téléphone existant.',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $input = $this->getRequestInput($this->request);
        $consultationInfos = [
            'objet' => (string)htmlspecialchars($input['motif']),
            'description' => (string)htmlspecialchars($input['description'] ?? null),
            'code'  => $this->generatecodeConsult(),
            'date'  => date('Y-m-d', strtotime($input['date'] ?? date('Y-m-d'))),
            'heure' => date('H:i:s', strtotime($input['heure'] ?? date('H:i:s'))),
            'duree' => (int)($input['duree'] ?? ConsultationModel::DEFAULT_DUREE),
            'canal' => ConsultationModel::ONLINE_CONSULT,
            'statut' => (int)ConsultationModel::VALIDE,
            'patient_user_id' => UtilisateurModel::staticGetIdByCode((string)$input['emetteur']),
            'medecin_user_id' => UtilisateurModel::adjaId(),
        ];

        $consultModel = new ConsultationModel();

        $consultModel->insert($consultationInfos);
        $response = [
            'statut'  => 'ok',
            'message' => $message ?? 'Rendez-vous ajouté.',
            'data'    => ['code' => $consultationInfos['code']],
            //  'data'    => [$consultationInfos],
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * updateRdv
     * 
     * Met à jour le rendez-vous/consultation dont l'identifiant est passé en paramètre
     * NB: un rendez-vous est une consultation en Attenete avec bilan vide
     *
     * @param  string $consultCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateRdv(string $consultCode = null, bool $consult = false)
    {
        $rules = [
            'date'  => [
                'rules' => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'Format de date attendu YYYY-MM-DD.']
            ],
            'heure' => [
                'rules' => 'if_exist|valid_date[H:i]',
                'errors' => ['valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'duree' => [
                'rules' => 'if_exist|numeric',
                'errors' => ['numeric' => 'Duree invalide.']
            ],
            'canal' => [
                'rules' => 'if_exist|numeric|in_list[0,1,2]',
                'errors' => [
                    'numeric' => 'Canal invalide.',
                    'in_list' => 'Canal inconnu.'
                ]
            ],
            'statut' => [
                'rules' => 'if_exist|numeric|in_list[0,1,2,3,4,5,6,7,8,9]',
                'errors' => [
                    'numeric' => 'statut invalide.',
                    'in_list' => 'statut inconnu.'
                ]
            ],
            'emetteur' => [
                'rules' => 'if_exist|numeric|is_not_unique[IA_utilisateurs.id_utilisateur]',
                'errors' => ['numeric' => 'Mauvaise valeur d\'émetteur', 'is_not_unique' => 'Emetteur inconnu.']
            ],
            'destinataire' => [
                'rules' => 'if_exist|numeric|is_not_unique[IA_utilisateurs.id_utilisateur]',
                'errors' => ['numeric' => 'Mauvaise valeur de destinateire', 'is_not_unique' => 'Destinataire inconnu.']
            ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'statut'  => 'no',
                'message' => 'Consultation inconnnue.',
                'errors'  => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($consultCode !== null) {
            try {
                $consult = ConsultationModel::getconsultDataFromCode($consultCode);
                $consultID = (int)$consult->id_consultation;
            } catch (\Throwable $th) {
                $response = [
                    'statut'  => 'no',
                    'message' => $consult ? 'Consultation inconnnue.' : 'Rendez-vous incommu.',
                    'token'   => $this->request->newToken ?? '',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
            }
        }

        $input = $this->getRequestInput($this->request);
        if (isset($input['objet'])) {
            $consultationInfos['objet'] = (string)htmlspecialchars($input['objet']);
        }
        if (isset($input['date'])) {
            if ((int)$consult->statut === ConsultationModel::ENATTENTE) { // On ne peut reporteter la date que lorsque le rendez-vous/la consultation n'ont pas encore été validé/refusé
                $consultationInfos['date'] = date('Y-m-d', strtotime($input['date']));
            } else {
                unset($input['date']);
            }
        }
        if (isset($input['heure'])) {
            $consultationInfos['heure'] = date('H:i', strtotime($input['heure']));
        }
        if (isset($input['duree'])) {
            $consultationInfos['duree'] = (int)$input['duree'];
        }
        if (isset($input['canal'])) {
            $consultationInfos['canal'] = (int)$input['canal'];
        }
        if (isset($input['statut'])) {
            $consultationInfos['statut'] = (int)$input['statut'];
            if ($consultationInfos['statut'] === ConsultationModel::VALIDE) {
                $user = UtilisateurModel::findUserById($consult->patient_user_id);
                $this->sendRdvConfirmedMail($user->email, $user->nom . ' ' . $user->prenom, $consult->date, $consult->heure, $consult->code);
            }
        }
        if (isset($input['emetteur'])) {
            $consultationInfos['patient_user_id'] = (int)$input['emetteur'];
        }
        if (isset($input['destinataire'])) {
            $consultationInfos['medecin_user_id'] = (int)$input['destinataire'];
        }
        if (isset($input['bilan'])) {
            $consultationInfos['bilan'] = (string)htmlspecialchars($input['bilan']);
        } else {
            $consultationInfos['bilan'] = '';
        }

        $validated = $_GET['validated'] ?? null;
        if ($validated) {
            $consultationInfos['statut'] = ConsultationModel::TRANSMIS;
        }

        if ($consult) {
            $message = 'Consultation modifiée';
        }
        $consultModel = new ConsultationModel();
        $consultModel->update($consultID, $consultationInfos);

        $input['code'] = $consultCode;
        $response = [
            'statut'  => 'ok',
            'message' => $message ?? 'Rendez-vous modifié.',
            'data'    => $input,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * getRdv
     * 
     * renvoie les rendez-vous de l'utilisateur dont le code est passé en paramètre.
     *
     * @param  int $userCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getRdvs(string $userCode = null) // Pour récupérer les rdvs d'un medecin et les consultations d'un patient
    {
        try {
            // $userID = UtilisateurModel::staticGetIdByCode($userCode);
            if ($userCode) {
                $user = UtilisateurModel::findUserByCode($userCode);
            } else {
                $user = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
            }
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Mauvaise authentification',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();

        if (User::is_med($userID)) {
            $rdvs = $consultationModel->getRdvbydata($userID);
            // $rdvs = $consultationModel->asArray()->whereIn('statut', [$consultationModel::ENATTENTE, $consultationModel::REFUSE])
            //                           ->where('medecin_user_id', $userID)
            //                           ->findAll();

            // $rdvs = $this->formatRdvs($rdvs);
            $rdvs = $this->formatConsult($rdvs, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
            $response = [
                'statut'  => 'ok',
                'message' => count($rdvs) . ' Rendez-vous trouvé(s).',
                'data'    => $rdvs,
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            return redirect()->to("consultation/user/$userID");
        }
    }

    /**
     * getRdv
     * 
     * renvoie les rendez-vous non démarré à partir de la date courante de l'utilisateur
     * dont le code est passé en paramètre où non.
     *
     * @param  int $userCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getbecomingRdvs(string $userCode = null) // similaire à getrdvs, mais ne sélectionne que ceux dont la date est > à today()
    {
        try {
            if ($userCode) {
                $user = UtilisateurModel::findUserByCode($userCode);
            } else {
                $user = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
            }
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Mauvaise authentification',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();

        if (User::is_med($userID)) {
            // $rdvs = $consultationModel->getFormTodayRdvbydata($userID);
            $rdvs = $consultationModel->getNonStartedConsult($userID);
            $rdvs = $this->formatConsult($rdvs, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
            $response = [
                'statut'  => 'ok',
                'message' => count($rdvs) . ' Rendez-vous trouvé(s).',
                'data'    => $rdvs,
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            return redirect()->to("consultation/user/$userID");
        }
    }

    public function getstartedRdvs(string $userCode = null)
    {
        try {
            if ($userCode) {
                $user = UtilisateurModel::findUserByCode($userCode);
            } else {
                $user = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
            }
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Mauvaise authentification',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();

        if (User::is_med($userID)) {
            // $rdvs = $consultationModel->getFormTodayRdvbydata($userID);
            $rdvs = $consultationModel->getStartedConsult($userID);
            $rdvs = $this->formatConsult($rdvs, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
            $response = [
                'statut'  => 'ok',
                'message' => count($rdvs) . ' Rendez-vous trouvé(s).',
                'data'    => $rdvs,
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            return redirect()->to("consultation/user/$userID");
        }
    }

    public function getendedRdvs(string $userCode = null)
    {
        try {
            if ($userCode) {
                $user = UtilisateurModel::findUserByCode($userCode);
            } else {
                $user = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
            }
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Mauvaise authentification',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();

        if (User::is_med($userID)) {
            // $rdvs = $consultationModel->getFormTodayRdvbydata($userID);
            $rdvs = $consultationModel->getEndedConsult($userID);
            $rdvs = $this->formatConsult($rdvs, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
            $response = [
                'statut'  => 'ok',
                'message' => count($rdvs) . ' Rendez-vous trouvé(s).',
                'data'    => $rdvs,
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            return redirect()->to("consultation/user/$userID");
        }
    }

    /**
     * detailRdv
     * 
     * Renvoie les détails sur un rendez-vous.
     *
     * @param  int $rdvID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detailRdv(int $rdvID)
    {
        $consultationModel = new ConsultationModel();

        $rdv = $consultationModel->getRdvById($rdvID);

        $response = [
            'statut'  => 'ok',
            'message' => 'Détails du rendez-vous.',
            'data'    => $rdv,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * detailConsultation
     * 
     * Renvoie les détails sur une consultation.
     *
     * @param  mixed $consultIdentify
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detailConsultation($consultIdentify, $coded = null)
    {
        $consultationModel = new ConsultationModel();

        if ($coded) {
            $consult = $consultationModel->getConsultByCode($consultIdentify);
        } else {
            $consult = $consultationModel->getConsultById($consultIdentify);
        }

        if ($consult) {
            $response = [
                'statut' => 'ok',
                'message' => 'Détails de la consultation.',
                'data'    => $consult,
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            $response = [
                'statut' => 'no',
                'message' => 'Aucune correspondance.',
                'data'    => [],
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        }
    }


    /**
     * vefitStateConsultation
     * 
     * Autorise l'acces à une consultation par un médecin
     * Renvoie les status success, expiré .
     *
     * @param  mixed $consultCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function vefitStateConsultation($consultCode)
    {
        $consultationModel = new ConsultationModel();
        if ($consultCode) {
            $consult = $consultationModel->getConsultByCode($consultCode);
        }


        if ($consult) {
            $valide = ConsultationModel::VALIDE;
            $refuse = ConsultationModel::REFUSE; // correspond à reporté
            $encours = ConsultationModel::ENCOURS;
            $termine = ConsultationModel::TERMINE;
            $transmis = ConsultationModel::TRANSMIS;
            // $statusList = array(ConsultationModel::$rdvStatut[$valide], ConsultationModel::$rdvStatut[$refuse], ConsultationModel::$rdvStatut[$encours], ConsultationModel::$rdvStatut[$termine], ConsultationModel::$rdvStatut[$transmis]);
            $statusList = array(ConsultationModel::$rdvStatut[$valide], ConsultationModel::$rdvStatut[$refuse], ConsultationModel::$rdvStatut[$encours], ConsultationModel::$rdvStatut[$transmis]);

            if (in_array($consult['statut'], $statusList)) {
                // On change le statut de cette consultation
                $consultationModel->set('statut', ConsultationModel::ENCOURS)
                    ->where('code', $consultCode)
                    ->update();

                $response = [
                    'statut' => 'ok',
                    'message' => 'Code valide.',
                    'data'    => $consult,
                ];
                return $this->getResponse($response);
            } else {
                $response = [
                    'statut' => 'no',
                    // 'message' => 'Code incorrect.',
                    'message' => 'Souscription terminée, ne peut être démarrée à nouveau.',
                    'data'    => [],
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
        } else {
            $response = [
                'statut' => 'no',
                'message' => 'Code inexistant.',
                'data'    => [],
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * verifProductIncHUser
     * 
     * Vérifie si l'utilisateur à souscrit à une assurance IncH et redirige vers la consultation (ADA).
     * sinon redirige vers la souscription du produit IncH 
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function verifProductIncHUser()
    {
        // 
        $userMo = new UtilisateurModel();
        $produitMo = new ProduitModel();
        $sousMo = new SouscriptionModel();
        $lignesous = new LigneSouscriptionModel();
        $data['patient_user_id'] = (int)$userMo->getIdByEmail($this->request->userEmail);
        $id_sous = $sousMo->asArray()->where('client_user_id', $data['patient_user_id'])
            ->where('etat', SouscriptionModel::VALIDE)
            ->findAll();
        if (!$id_sous) {
            $state = 0;
        } else {
            for ($i = 0; $i < count($id_sous); $i++) {
                $id_pro[$i] = $lignesous->asArray()->where('souscription_id', $id_sous[$i]['id_souscription'])->findAll();
            }
            if (count($id_pro) == 0) {
                $state = 0;
            } else {
                for ($i = 0; $i < count($id_pro); $i++) {
                    $infoproduitIncH = $produitMo->asArray()->where('id_produit', $id_pro[$i][0]['produit_id'])
                        ->where('code', 'IAINCHASSU')
                        ->first();
                    if (!is_null($infoproduitIncH)) {
                        $data['infoproduitIncH'] = $infoproduitIncH;
                        $state = 1;
                        break;
                    } else {
                        $state = 0;
                    }
                }
                $data['infomedecin'] = $userMo->asArray()->where('code', 'IAMEDADA')->first();
            }
        }

        if ($state == 0) {
            $response = [
                'statut' => 'no',
                'message' => "Vous n'avez pas d'assurance IncH, Souscrire à une assurance",
            ];
            return $this->getResponse($response);
        } else {
            $response = [
                'statut' => 'ok',
                'message' => 'Vous avez une assurance IncH valide',
                'data'   => $data,
            ];
            return $this->getResponse($response);
        }
    }

    /**
     * allRdvs
     * 
     * Renvoie tous les rendez-vous pris sur la plateforme à partir de la date courante
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function allRdvs()
    {
    }


    /**
     * getConsultations
     * 
     * Renvoie la liste des consultations de l'utilisateur dont le code 
     * est fourni en paramètre en fonction de sa situation (médecin où patient).
     *
     * @param  string $userCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getConsultations(string $userCode, $forMed = false)
    {
        try {
            // $userID = UtilisateurModel::staticGetIdByCode($userCode);
            $user = UtilisateurModel::findUserByCode($userCode);
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Mauvaise authentification',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();
        if (User::is_med($userID) && $forMed) {
            $consults = $consultationModel->getConsultbydata('medecin_user_id', $userID);
            $consults = $this->formatConsult($consults, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
        } else {
            // $consults = array_merge($consultationModel->getConsultbydata('patient_user_id', $userID), $consults ?? []);
            $consults = $consultationModel->getConsultbydata('patient_user_id', $userID);
            $consults = $this->formatConsult($consults, 'patient_user_id', $userID, $user->nom . ' ' . $user->prenom);
        }
        $response = [
            'statut'  => 'ok',
            'message' => count($consults) . ' Consultation(s) trouvée(s).',
            'data'    => $consults,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }


    /**
     * getConsultations
     * 
     * Renvoie la liste de toutes les consultations de l'utilisateur dont le code
     * est fourni en paramètre, indépendament de sa situation médecin où patient.
     *
     * @param  string $userCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getAllUserConsultations(string $userCode)
    {
        try {
            // $userID = UtilisateurModel::staticGetIdByCode($userCode);
            $user = UtilisateurModel::findUserByCode($userCode);
            $userID = (int)$user->id_utilisateur;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Utilisateur inconnu',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $consultationModel = new ConsultationModel();
        if (User::is_med($userID)) {
            $consults = $consultationModel->getConsultbydata('medecin_user_id', $userID);
            $consults = $this->formatConsult($consults, 'medecin_user_id', $userID, $user->nom . ' ' . $user->prenom);
        }
        $consults2 = $consultationModel->getConsultbydata('patient_user_id', $userID);
        $consults2 = $this->formatConsult($consults2, 'patient_user_id', $userID, $user->nom . ' ' . $user->prenom);

        $response = [
            'statut'  => 'ok',
            'message' => count($consults) + count($consults2) . ' Consultation(s) trouvée(s).',
            'data'    => array_merge($consults, $consults2),
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /*
    public function filterConsult()
    { 
        // print_r($_GET);
        $medlist = [];
        $medlistIDs = [];
        // $medlistCodes = [];

        $userModel = new UtilisateurModel();

        // Si le code est fourni, s'assurer qu'il s'agit bien d'un médecin
        if (isset($_GET['c'])) {
            // $userCode = $_GET['c'];

            //on recupere les profils du user à partir de son code
            // $userProfils = $userModel->allprofils($userCode);
            $user = $userModel->asArray()->join('IA_utilisateurProfils','id_utilisateur = IA_utilisateurProfils.utilisateur_id','right')
                                ->where('code', htmlspecialchars($_GET['c']))
                                ->findAll();
            // if (! in_array(self::Med_PROFIL, $userProfils) ){
            // if (! in_array(self::Med_PROFIL, (array)($user['profil_id'] ?? [])) ){
            if (! in_array(self::Med_PROFIL, array_column($user, 'profil_id')) ){
                $response = [
                    'statut'  => 'ok',
                    'message' => 'Aucun résultat',
                    'data'    => null,
                    'token'   => $this->request->newToken ?? '',
                ];
                return $this->getResponse($response);
            }elseif ($user){
                array_push($medlistIDs, $user[0]['id_utilisateur']);
            }
        }

        // Si le nom est fourni
        if (isset($_GET['nom'])) {
            $user = $userModel->asArray()->join('IA_utilisateurProfils','id_utilisateur = IA_utilisateurProfils.utilisateur_id','right')
                                ->like('nom', ($_GET['nom']))
                                ->findAll();
            $medIds =[];
            for ($i=0; $i < count($user); $i++) { 
                if (self::Med_PROFIL == $user[$i]['profil_id']) {
                    $medIds[] = $user[$i]['id_utilisateur'];
                }
            }
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }

        }

        // si le critère de sélection est une date où une heure
        if (isset($_GET['hour']) || isset($_GET['day'])) {
            $day = isset($_GET['day']) ? (string)$_GET['day'] : null;
            $hour = isset($_GET['hour']) ? (string)$_GET['hour'] : null;
            $agendaModel = new AgendaModel();
            $medIds = $agendaModel->findMedIdsByDate($day, $hour);
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }
        }

        // si le critère de sélection est un assureur
        if (isset($_GET['a'])) {
            $assMedModel = new AssureurMedecinModel();
            $assureur    = $userModel->getUserByCode(htmlspecialchars($_GET['a']));

            $medIds = $assMedModel->asArray()->where('assureur_id', $assureur['id_utilisateur'])
                                    ->findColumn('medecin_id');
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }
        }

        // si le critère de sélection est un motif où un skill
        if (isset($_GET['motif']) && isset($_GET['skill'])) {
            $skillMotifModel = new SkillsMotifModel();
            $medIds = $skillMotifModel->medIDsFromSkillsOrMotifName(htmlspecialchars($_GET['motif']), htmlspecialchars($_GET['skill']));
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }
        }
        elseif (isset($_GET['motif'])) {
            $skillMotifModel = new SkillsMotifModel();
            $medIds = $skillMotifModel->medIDsFromSkillsOrMotifName(htmlspecialchars($_GET['motif']));
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }
        }
        elseif (isset($_GET['skill'])) {
            $skillMotifModel = new SkillsMotifModel();
            $medIds = $skillMotifModel->medIDsFromSkillsOrMotifName(null, htmlspecialchars($_GET['skill']));
            if ($medIds) {
                $medlistIDs = array_merge($medlistIDs, $medIds);
            }
        }

        // si le critère de sélection est une spécialité
        if (isset($_GET['spec'])) {
        }

        // On renvoie la liste des consultations correctement formatée
        $medlistIDs = array_unique($medlistIDs);
        if (count($medlistIDs) === 0) {
            $response = [
                'statut'  => 'ok',
                'message' => 'Aucun résultat',
                'data'    => null,
                'token'   => $this->request->newToken ?? '',
            ];
        return $this->getResponse($response);
        }else {
            // si le nombre d'element par page est précisé--
            if (isset($_GET['n'])) {
                $limit      = abs((int)$_GET['n']);
                $offset     = min(1, abs(((int)($_GET['p']??1)-1)*$limit));
                $maxPage    = (int)(count($medlistIDs)/$limit);
                $medlistIDs = array_slice($medlistIDs, $offset, $limit);
                echo "n: $limit, p: $offset";
            }
            $medlist = $this->getMedInfos($medlistIDs);
            $response = [
                'statut'   => 'ok',
                'message'  => count($medlist).' résultat(s) trouvé(s).',
                'last_page'=> $maxPage ?? 1,
                'data'     => $medlist,
                'token'    => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        }

    }
    */

    /**
     * delAdress
     * 
     * Supprime l'adresse dont l'id est passé en paramètre
     *
     * @param  int $adresssID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delAdress(int $adresssID)
    {
        $medCanModel = new MedecincanneauxModel();
        $medCanModel->delete($adresssID);

        $response = [
            'statut'  => 'ok',
            'message' => 'Adresse Supprimée.',
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * delMotif
     * 
     * Supprime le motif de consultation dont l'id est passé en paramètre
     *
     * @param  int $motifID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delMotif(int $motifID)
    {
        $motifModel = new MotifModel();
        $motifModel->delete($motifID);

        $response = [
            'statut'  => 'ok',
            'message' => 'Motif de Consultation Supprimée.'
        ];
        return $this->getResponse($response);
    }

    /**
     * delSkill
     * 
     * Supprime le skill dont l'id est passé en paramètre
     *
     * @param  int $motifID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delSkill(int $skillID)
    {
        $skillMotifModel = new SkillsMotifModel();
        $skillModel = new SkillModel();

        $skillMotifModel->where('skill_id', $skillID)->delete();
        $skillModel->delete($skillID);

        $response = [
            'statut'  => 'ok',
            'message' => 'Compétence Supprimée.'
        ];
        return $this->getResponse($response);
    }

    public function listCanneaux()
    {
        $data = MedecinCanauxModel::getcaneauxList();

        $result = array_map(
            fn ($key, $val) => ['id' => $key, 'name' => $val,],
            array_keys($data),
            array_values($data)
        );

        $response = [
            'statut'  => 'ok',
            'message' => 'Liste des canneaux.',
            'data'    => $result,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * productMotifs
     * 
     * Rernvoie la liste des motifs de consultation, pour lesquels
     * l'assureur propriétaire du produit dont le code est passé en paramètre,
     * dispose de médecins aptes à consulter
     *
     * @param  string $codeProduit
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function productMotifs(string $codeProduit)
    {
        /*
            À partir du code du produit, retrouver le code de l'assureur
            À partir du code de l'assureur, recupérer distinctement les 
            skills de l'ensemble de ses médecins
            À partir des skills, récupérer distinctement les motifs
        */
        try {
            $canal = $this->request->getVar('canal');
            $ville = $this->request->getVar('ville');
            if (!($canal && $ville)) {
                throw new \Exception("Canal où Ville Inconnue.", 1);
            }
            $villeID = VilleModel::getIdByName(htmlspecialchars($ville));
            $canalID = CanauxModel::getIdByName(htmlspecialchars(str_replace('é', 'e', $canal)));

            $prodMod = new ProduitModel();
            $assureurId = $prodMod->where('code', $codeProduit)->findColumn('fournisseur_user_id')[0];

            $assMedModel = new AssureurMedecinModel();
            // $assurSkills = $assMedModel->findAllAssureurSkills($assureurId);
            $assurSkills = $assMedModel->findAllAssureurSkills($assureurId, $canalID, $villeID);
            if (empty($assurSkills)) {
                throw new \Exception("Aucun Médecin Associé à cet Assureur.", 1);
            }

            $skillMotifModel = new SkillsMotifModel();
            $data = $skillMotifModel->findBulkMotifs($assurSkills);

            $response = [
                'statut' => 'ok',
                'message' => count($data) . ' motif(s) trouvé(s).',
                'data'   => $data,
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Produit Inconnu ou Médecin non associé.',
                'error'  => $th->getMessage(),
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * ownerMotifs
     *
     * Renvoie la liste des motifs de consultation d'un médecin,
     * en fonction de ses skills où des motifs par lui défini
     * 
     * @param  mixed $codeMedecin
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getOwnerMotifs(string $codeMedecin = null)
    {
        /*
            récuperer l'id du médecin,
            lister ses motifs,
            retourner le résultat.
        */
        if ($codeMedecin) {
            $medId = UtilisateurModel::staticGetIdByCode($codeMedecin);
        } else {
            $medId = UtilisateurModel::staticGetIdByEmail($this->request->userEmail);
        }

        $data = $this->getMedMotifs([$medId])[0];
        $response = [
            'statut' => 'ok',
            'message' => 'motif(s) trouvé(s).',
            'data'   => $data,
        ];
        return $this->getResponse($response);
    }

    /**
     * getmedMotifs
     *
     * Renvoie la liste des motifs de consultation des médecins,
     * en fonction de leurs skills où des motifs par eux défini.
     * Conserve l'ordre des ids de medecin fourni.
     * 
     * @param  mixed $medIds
     * @return void
     */
    private function getMedMotifs(array $medIds)
    {
        /*
            À partir de la liste d'id de medecins,
            récupérer la liste des skills et motifs associés,
            pour chaque skill, 
            Si la liste des motifs est disponible, la renvoyer,
            si non, récupérer les motifs par défaut associés au skill
        */
        $medSkillModel = new MedecinSkillsModel();
        $data = $medSkillModel->asArray()->select('skill_id, nom, motifs, utilisateur_id as medID')
            ->join('IA_skills', 'skill_id = id_skill', 'left')
            ->whereIn('utilisateur_id', $medIds)
            ->findAll();

        // skills sans motif défini
        // $noMotifsData  = array_filter($data, fn($val, $key) => (!$val['motifs']), ARRAY_FILTER_USE_BOTH);

        // liste des ids de skills n'ayant pas de motif associé
        $noMotifSkills = array_map( // remplacer en array_column
            fn ($val) => $val['skill_id'],
            // $noMotifsData
            array_filter($data, fn ($val, $key) => (!$val['motifs']), ARRAY_FILTER_USE_BOTH)
        );

        //récupération des motifs de ces skills
        $skillMotifModel = new SkillsMotifModel();
        $motifs = $skillMotifModel->findBulkMotifs($noMotifSkills);

        //association des skills aux motifs
        $motifiedSkills = [];
        foreach ($noMotifSkills as $skillId) {
            $motifiedSkills[$skillId] = array_values(array_map(
                fn ($val) => [
                    'id'          => $val['id_motif'],
                    'nom'         => $val['nomMotif'],
                    'description' => $val['description']
                ],
                array_filter($motifs, fn ($v, $k) => $v['skill_id'] === $skillId, ARRAY_FILTER_USE_BOTH)
            ));
        }

        //association des motifs aux données
        $getList   = fn () => $motifiedSkills;
        $arguments = array_fill(0, count($data), $getList);
        $result = [];
        foreach ($medIds as $elment) {
            $result[] = array_map(
                fn ($val, $suplData) => [
                    $val['nom'] => [
                        'motifs' => $suplData()[$val['skill_id']]
                    ]
                ],
                array_filter($data, fn ($v, $k) => (int)$v['medID'] === $elment, ARRAY_FILTER_USE_BOTH),
                $arguments
            );
        }

        return $result;
    }

    /**
     * getSkillsFromMotif
     * 
     * Renvoie pour les médecins existatnts,
     * la liste des skills permettants de consulter pour le motif spécifié
     *
     * @param  int $motifID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getSkillsFromMotif(int $motifID)
    {
        /* Il est question de sélectionner les skills fonctionnant pour le motif spécifié,
            et de ne conserver que ceux qui sont attribués à des médecins existants
        */
        $skillMotif = new SkillsMotifModel();
        // $skillIDs   = $skillMotif->where('motif_id', $motifID)->findColumn('skill_id');
        $skills   = $skillMotif->skillsFromMotifID($motifID);
        $skillIDs = array_column($skills, 'skill_id');

        $medSkillModel = new MedecinSkillsModel();
        // $medSkillIDs = $medSkillModel->select('skill_id, description_perso')->whereIn('skill_id', $skillIDs)->findAll();
        $medSkillIDs = $medSkillModel->whereIn('skill_id', $skillIDs)->findColumn('skill_id');
        $medSkillIDs = array_unique($medSkillIDs ?? []);

        // formatter la data afin de la retourner
        $skills = array_filter($skills, function ($skill) use ($medSkillIDs) {
            return strlen(array_search($skill['skill_id'], $medSkillIDs));
        });

        if ($skills) {
            $response = [
                'statut' => 'ok',
                'message' => count($skills) . ' spécialité(s) trouvée(s).',
                'data'   => $skills,
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            $response = [
                'statut' => 'no',
                'message' => 'Aucune Spécialité trouvée.',
                'data'   => [],
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FOUND);
            // return $this->getResponse($response, ResponseInterface::HTTP_NO_CONTENT);
        }
    }

    /**
     * getVillesFromCanal
     * 
     * Renvoie pour les médecins existants,
     * la liste des villes permettant une consultation par le canal spécifié
     *
     * @param  int $canalID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getVillesFromCanal(int $canalID)
    {
        /* Il est question de sélectionner les villes pour les médecins ayant le canal
        spécifié dans la liste de leur canaux. on retourne ['id_ville'=>'valeur', 'nomVille'=>'valeur']
        */

        $locModel = new MedecinLocalisationModel();
        $villes   = $locModel->villesFromCanal($canalID);

        if ($villes) {
            $response = [
                'statut' => 'ok',
                'message' => count($villes) . ' ville(s) trouvée(s).',
                'data'   => $villes,
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        } else {
            $response = [
                'statut' => 'no',
                'message' => 'Aucune ville trouvée.',
                'data'   => [],
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_FOUND);
            // return $this->getResponse($response, ResponseInterface::HTTP_NO_CONTENT);
        }
    }

    public function endConsultation(string $codeConsult)
    {
        $consultModel = new ConsultationModel();
        $consultModel->set('statut', ConsultationModel::TERMINE)
            ->where('code', $codeConsult)
            ->update();

        $response = [
            'statut'  => 'ok',
            'message' => 'Consultation Terminée',
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    public function addBilan(string $codeConsult)
    {
        $rules = [
            'titre' => [
                'rules' => 'required',
                'errors' => ['required' => 'Précisez le titre.', 'alpha_numeric_punct' => 'Valeur inappropriée.']
            ],
            'description' => [
                'rules' => 'required',
                'errors' => ['required' => 'Ajoutez un commentaire.', 'alpha_numeric_punct' => 'Valeur inappropriée.']
            ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'statut' => 'no',
                'message' => $this->validator->getErrors(),
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }
        $input = $this->getRequestInput($this->request);
        $auteur = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);

        $consultModel = new ConsultationModel();
        $bilan = $consultModel->where('code', $codeConsult)->findColumn('bilan')[0];
        $bilan = $bilan ? $bilan : [];
        $data = [
            'titre'       => (string)$input['titre'],
            'description' => (string)$input['description'],
            'date'        => date('Y-m-d H:i'),
            'auteur'      => ['nom' => $auteur->nom . ' ' . $auteur->prenom, 'code' => $auteur->code]
        ];
        $bilan[] = $data;
        $consultModel->set('bilan', json_encode($bilan))
            ->where('code', $codeConsult)
            ->update();

        $response = [
            'statut'  => 'ok',
            'message' => 'Bilan Enregistré',
            'data'    => $data,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * myAdvices
     * 
     * Renvoie la listes des avis demandés par le médecin
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function myAskedAdvices()
    {
        /* Renvoyer 
            nom, prenom, email, photo profil, spécialité du médecin receveur de la demande
        */
        $sender = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
        $senderID = $sender->id_utilisateur;

        // try {
        $avisModel = new AvisExpertModel();
        $demandes  = $avisModel->getAskedAdvices($senderID);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'statut' => 'no',
        //         'message' => "Votre demande n'a pu être traitée.",
        //         'error'  => $th->getMessage(),
        //         'token'  => $this->request->newToken ?? '',
        //     ];
        //     return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        // }
        $response = [
            'statut' => 'ok',
            'message' => count($demandes) . " demande(s) d'avis trouvée(s).",
            'data'   => $demandes,
            'token'  => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * myAdvices
     * 
     * Renvoie la listes des avis demandés par le médecin
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function myGivedAdvices()
    {
        /* Renvoyer 
            nom, prenom, email, photo profil, spécialité du médecin receveur de la demande
        */
        $sender = UtilisateurModel::findUserByEmailAddress($this->request->userEmail);
        $senderID = $sender->id_utilisateur;

        try {
            $avisModel = new AvisExpertModel();
            $demandes  = $avisModel->getGivedAdvices($senderID);
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => "Votre demande n'a pu être traitée.",
                'error'  => $th->getMessage(),
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $response = [
            'statut' => 'ok',
            'message' => count($demandes) . " reponse(s) d'avis trouvée(s).",
            'data'   => $demandes,
            'token'  => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }



    // fonctions utilitaires
    private function formatConsult(array $consults, $selectedField, $selectedValue, $name = null)
    {
        $result = [];
        for ($i = 0; $i < count($consults); $i++) {
            $consults[$i][$selectedField] = $selectedValue;
            // $result[$i]['id_consultation']  = $consults[$i]['id_consultation'];
            $result[$i]['objet']  = $consults[$i]['objet'];
            $result[$i]['date']   = $consults[$i]['date'];
            $result[$i]['heure']  = $consults[$i]['heure'];
            $result[$i]['duree']  = $consults[$i]['duree'];
            $result[$i]['code']   = $consults[$i]['code'];
            $result[$i]['id']     = $consults[$i]['id_consultation'];
            $result[$i]['canal']  = $consults[$i]['canal'];
            $result[$i]['statut'] = $consults[$i]['statut'];
            $result[$i]['souscriptionID'] = $consults[$i]['souscription_id'];
            $result[$i]['assuranceCode']  = ProduitModel::getCodeFromID($consults[$i]['assurance_id']);
            $result[$i]['isAssured']      = (bool)$consults[$i]['assuré'] ? true : false;
            $result[$i]['isSecondAdvice'] = (bool)$consults[$i]['isSecondAdvice'] ? true : false;
            $result[$i]['patient']        = is_int($consults[$i]['patient_user_id']) ? $name ?? $consults[$i]['patient_user_id'] : $consults[$i]['nom'] . ' ' . $consults[$i]['prenom'];
            $result[$i]['medecin']        = is_int($consults[$i]['medecin_user_id']) ? $name ?? $consults[$i]['medecin_user_id'] : $consults[$i]['nom'] . ' ' . $consults[$i]['prenom'];
            // $result[$i]['patient'] = is_int($consults[$i]['patient_user_id']) ? $consults[$i]['patient_user_id'] : $consults[$i]['nom'].' '.$consults[$i]['prenom'];
            // $result[$i]['medecin'] = is_int($consults[$i]['medecin_user_id']) ? $consults[$i]['medecin_user_id'] : $consults[$i]['nom'].' '.$consults[$i]['prenom'];
            //Ajout des infos sur le patient demandé
            $userModel = new UtilisateurModel();
            $infoUser = $userModel->asArray()->select('code, nom, prenom, date_naissance, sexe, profession as métier, email, tel1 as telephone, ville, civilite, nbr_enfant')
                ->where('id_utilisateur', $consults[$i]['patient_user_id'])
                ->first();
            // print_r($infoUser);
            $result[$i]['patient'] = $infoUser;
        }
        return $result;
    }


    /**
     * listSkills
     * 
     * Renvoie la liste de toutes des compétences disponible
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listSkills()
    {
        $competence = new SkillsModel();
        $data = $competence->allSkills();

        if (!$data) {
            $response = [
                'statut' => 'ok',
                'message' => 'Aucune Competence disponible',
            ];
            return $this->getResponse($response);
        }

        $response = [
            'statut' => 'ok',
            'message' => count($data) . ' Competence(s) trouvé(s).',
            'data'   => $data,
        ];
        return $this->getResponse($response);
    }



    /**
     * addSkills
     * 
     * Ajoute un Skills
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addSkills()
    {
        $rules = [
            'nom' => 'required|is_unique[IA_skills.nom]',
        ];
        $input = $this->getRequestInput($this->request);
        if (!$this->validateRequest($input, $rules)) {
            $response = [
                'statut' => 'no',
                'message' => 'Une compétence existe déjà avec ce nom.',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        } else {
            if (isset($input['nom'])) {
                $skills = new SkillsModel();
                $SkillsInfos = [
                    'nom'  => $input['nom'],
                ];
                $skills->save($SkillsInfos);
                $response = [
                    'statut'        => 'ok',
                    'message'       => 'Compétence ajouté avec succès!!',
                    'data'          => $SkillsInfos,
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            }
        }
    }


    /**
     * updateSkills
     * 
     * Met à jour le Skills dont l'identifiant est passé en paramètre
     * 
     * @var id_service identifiant du service
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function updateSkills($id_skills)
    {
        $input = $this->getRequestInput($this->request);
        $skill = new SkillsModel();

        if (isset($input['nom'])) {
            $data['nom'] = htmlentities($input['nom']);
        }

        if (isset($input['description'])) {
            $data['description'] = htmlentities($input['description']);
        }

        $skill->update($id_skills, $data);
        $data['id_skills'] = $id_skills;
        $response = [
            'statut'        => 'ok',
            'message'       => 'Compétence Modifée avec succès!!',
            'data'          => $data,
        ];
        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }



    /**
     * public function generatecodeConsult()
     * 
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

    /**
     * public function getFilterMedecin_RDV()
     * 
     * fonction qui retoune la liste de medecin suivant les parametres
     * b_sans_assur; b_avec_assur, b_IA, id_motif, periode
     *
     * @param b_sans_assur bool
     * @param b_avec_assur bool
     * @param b_IA bool
     * @param id_motif int
     * @param periode date
     * @return array
     */


    // public function getFilterMedecin_RDV(bool $b_sans_assur ,  bool $b_avec_assur ,  bool $b_IA , int $id_motif , date $periode  )
    public function getFilterMedecin_RDV()
    {
        $assmedMo = new AssureurMedecinModel();
        $skillMo = new SkillsModel();
        $agendaMo = new AgendaModel();
        $userMo = new UtilisateurModel();
        $medecinskillMo = new MedecinSkillsModel();
        $allmedecins = $medecinskillMo->asArray()->select('distinct(utilisateur_id)')->findAll();
        // print_r($allmedecins);
        if (!is_null($allmedecins)) {
            for ($i = 0; $i < count($allmedecins); $i++) {
                $infoMedecin[$i]['info'] = $userMo->asArray()
                    ->select('id_utilisateur, nom, prenom, b_sans_assur, b_avec_assur')
                    ->where('id_utilisateur', $allmedecins[$i]['utilisateur_id'])
                    ->first();
                $infoMedecin[$i]['specialite'] = $skillMo->whereIn('id_skill', $medecinskillMo->asArray()
                    ->where('utilisateur_id', $allmedecins[$i]['utilisateur_id'])
                    ->findColumn('skill_id'))
                    ->findColumn('nom');
                $infoMedecin[$i]['agenda'] = $agendaMo->asArray()->where('proprietaire_user_id', $allmedecins[$i]['utilisateur_id'])->findAll();
                $infoMedecin[$i]['assureurs'] = $userMo->asArray()
                    ->select('id_utilisateur ,concat(nom, " ",prenom) as nom_complet')
                    ->whereIn('id_utilisateur', $assmedMo->asArray()
                        ->where('medecin_id', $allmedecins[$i]['utilisateur_id'])
                        ->findColumn('assureur_id'))
                    ->findAll();
            }

            print_r(json_encode($infoMedecin));
        } else {
        }

        /*
        $allmedecinskill=$medecinskillMo->asArray()->select('*')->findAll();
        print_r($allmedecinskill);
        if (!is_null($allmedecinskill)) {
            for ($i=0; $i <count($allmedecinskill) ; $i++) { 
                $infoMedecin[$i]['info']=$userMo->asArray()->select('id_utilisateur, nom, prenom, b_sans_assur, b_avec_assur')->where('id_utilisateur',$allmedecinskill[$i]['utilisateur_id'])->first();
                $infoMedecin[$i]['specialite']=$skillMo->asArray()->select('nom')->where('id_skill',$allmedecinskill[$i]['skill_id'])->first();  
                $infoMedecin[$i]['agenda']=$agendaMo->asArray()->select('titre, heure_dispo_debut, heure_dispo_fin, jour_dispo')->where('proprietaire_user_id',$allmedecinskill[$i]['utilisateur_id'])->first();  
                $infoMedecin[$i]['assureurs']=$userMo->asArray()->select('id_utilisateur ,concat(nom, prenom) as nom_complet')->whereIn('id_utilisateur',
                                            $assmedMo->asArray()->where('medecin_id', $allmedecinskill[$i]['utilisateur_id'])->findColumn('assureur_id')
                                        )->findAll();  
               
            }
            
            // print_r(json_encode($infoMedecin));
        }else{

        }
        */
    }

    /**
     * listExpertises
     * 
     * renvoie la liste des skills ayant une expertise
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listExpertises()
    {
        $competence = new MedecinSkillsModel();
        $data = $competence->allExpertises();

        if (!$data) {
            $response = [
                'statut' => 'ok',
                'message' => 'Aucune Expertise trouvée',
                'data'   => [],
                'token'  => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        }

        $response = [
            'statut' => 'ok',
            'message' => count($data) . ' Expertise(s) trouvée(s).',
            'data'   => $data,
            'token'  => $this->request->newToken ?? ''
        ];
        return $this->getResponse($response);
    }

    /**
     * listExpertskill
     * 
     * Renvoie la liste des médecins experts pour le skill précisé
     *
     * @param  int $skillID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listExpertskill(int $skillID)
    {
        return redirect()->to('experts/skill/' . $skillID);
    }

    /**
     * listStatuts
     * 
     * revoie la liste des status de consultation
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function listStatuts()
    {
        $statuts = ConsultationModel::$rdvStatut;
        $data = array_map(
            fn ($key, $val) => ['id' => $key, 'name' => $val],
            array_keys($statuts),
            array_values($statuts)
        );
        $response = [
            'statut' => 'ok',
            'message' => count($data) . ' Statut(s) trouvé(s).',
            'data'   => $data,
        ];
        return $this->getResponse($response);
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
        $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
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

    public static function sendAvisDemandedMail(string $recipient, string $nomComplet)
    {
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient);
        $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
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

    // private function sendRdvConfirmedMail(string $recipient, string $nomComplet, string $date, string $heure, string $code)
    public static function sendRdvConfirmedMail(string $recipient, string $nomComplet, string $date, string $heure, string $code)
    {
        $date = date('d M Y', strtotime($date));
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient);
        $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
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

    public static function sendAvisDemandConfirmedMail(string $recipient, string $nomComplet, string $skillName)
    {
        $email = Services::email();

        $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
        $email->setTo($recipient);
        $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
        $email->setSubject('Confirmation de consultation');
        $email->setMessage("<h2>Bonjour " . $nomComplet . ".</h2>
                            <br>Votre demande d'expertise pour $skillName a été envoyée.<br>
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
}
