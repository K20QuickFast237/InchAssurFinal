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
        if (
            $consult &&
            (auth()->user()->inGroup('administrateur') ||
                $consult->medecin_user_id['idUtilisateur'] == $this->request->utilisateur->id ||
                $consult->patient_user_id['idUtilisateur'] == $this->request->utilisateur->id)
        ) {
            $response = [
                'statut'  => 'ok',
                'message' => 'Détails de la consultation.',
                'data'    => $consult,
            ];
            return $this->sendResponse($response);
        }
        $response = [
            'statut'  => 'no',
            'message' => $consult ? ' Action non authorisée pour ce profil.' : "Consultation Inconnue.",
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
    }

    /**
     * Modifie une consultation (bilan, objet, heure et date)
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
            'bilan' => 'if_exist',
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
            model("ConsultationsModel")->db->transBegin();
            model("AgendasModel")->where('id', $newAgenda->id)->set('slots', $newAgenda->slots)->update();
            model("AgendasModel")->where('id', $oldAgenda->id)->set('slots', $oldAgenda->slots)->update();
        }
        // $cons = model("ConsultationsModel")->where($identifier['name'],  $identifier['value']);
        $cons = model("ConsultationsModel")->where('id', $consult->id);
        foreach ($input as $key => $value) {
            $cons->set($key, $value);
        }
        $cons->update();
        model("ConsultationsModel")->db->transCommit();

        $response = [
            'statut'  => 'ok',
            'message' => 'Consultation mise à jour.',
        ];
        return $this->sendResponse($response);
    }

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
        $email->setCC(['tonbongkevin@gmail.com']);
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
        $email->setCC(['tonbongkevin@gmail.com']);
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
        $email->setCC(['tonbongkevin@gmail.com']);
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
