<?php

namespace Modules\Agendas\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Agendas\Entities\AgendaEntity;
use Modules\Consultations\Entities\ConsultationEntity;

class AgendasController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    /**
     * getAgenda
     * 
     * Renvoie l'agenda de l'utilisateur dont le code est spécifié
     * 
     * @param  string $code
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getAgenda(string $code = null)
    {
        $agendaModel = new AgendaModel();
        if ($code) {
            $medID = UtilisateurModel::staticGetIdByCode(htmlspecialchars($code));
        } else {
            $medID = UtilisateurModel::staticGetIdByEmail($this->request->userEmail);
        }
        $agenda = $agendaModel->fromTodayAgenda($medID);

        if (!$agenda) {
            $response = [
                'statut'  => 'no',
                'message' => 'Agenda vide.',
                'token'   => $this->request->newToken ?? '',
            ];
            return $this->getResponse($response);
        }

        $agenda = $this->formatAgenda($agenda);

        $response = [
            'statut'  => 'ok',
            'message' => 'Agenda trouvé.',
            'data'    => $agenda,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * OldaddAgenda
     * 
     * Renvoie l'agenda du médecin spécifié
     *
     * @param  int $medID
     * @param  int $agendaID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function OldaddAgenda(int $medID, int $agendaID = null)
    {
        $rules = [
            'jour'  => [
                'rules' => 'required|in_list[Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche]',
                'errors' => ['required' => 'Précisez le jour.', 'in_list' => 'Jour doit être dans la liste [Lundi,Mardi,Mercredi,Jeudi,Vendredi,Samedi,Dimanche].']
            ],
            'debut' => [
                'rules' => 'required|valid_date[H:i]',
                'errors' => ['required' => 'Précisez l\'heure de debut.', 'valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'fin'   => [
                'rules' => 'required|valid_date[H:i]',
                'errors' => ['required' => 'Précisez l\'heure de fin.', 'valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
        ];
        $input = $this->getRequestInput($this->request);
        if (!$this->validate($rules)) {
            $response = [
                'statut'  => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }
        $agendaInfos = [
            'jour_dispo'          => (string)htmlspecialchars($input['jour']),
            'heure_dispo_debut'   => date('H:i', strtotime($input['debut'])),
            'heure_dispo_fin'     => date('H:i', strtotime($input['fin'])),
            'proprietaire_id' => (int)$medID,
        ];

        if (isset($input['titre'])) {
            $agendaInfos['titre'] = (string)htmlspecialchars($input['titre']);
        }

        $agendaModel = new AgendaModel();
        if ($agendaID === null) {  // on veut ajouter
            $agendaInfos['id_agenda'] = $agendaModel->insert($agendaInfos);
            $message = 'Agenda ajouté.';
            $statusCode = ResponseInterface::HTTP_CREATED;
        } else {  // on veut mettre à jour
            $agendaModel->update($agendaID, $agendaInfos);
            $agendaInfos['id_agenda'] = $agendaID;
            $message = 'Agenda modifié.';
            $statusCode = ResponseInterface::HTTP_OK;
        }

        $response = [
            'statut'  => 'ok',
            'message' => $message,
            'data'    => $agendaInfos,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response, $statusCode);
    }

    /**
     * addAgenda
     * 
     * Ajoute où met à jour l'agenda d'un medecin
     *
     * @param  string $medCode
     * @param  int $agendaID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addAgenda1(string $medCode, int $agendaID = null)
    {
        // echo "med: $medCode, agenda: $agendaID";
        // print_r($this->getRequestInput($this->request));
        // exit();
        /* data attendue:
            [
                'debut': 'YYYY-mm-dd HH:ii',
                'fin': 'YYYY-mm-dd HH:ii',
                'label': 'a short text'
            ]
        */
        $rules = [
            'debut' => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de debut.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'fin'   => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de fin.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
        ];
        $input = $this->getRequestInput($this->request);
        if (!$this->validate($rules)) {
            $response = [
                'statut'  => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $agendaInfos = [
            'jour_dispo'          => date('Y-m-d', strtotime((string)htmlspecialchars($input['debut']))),
            'heure_dispo_debut'   => date('H:i', strtotime((string)htmlspecialchars($input['debut']))),
            'heure_dispo_fin'     => date('H:i', strtotime((string)htmlspecialchars($input['fin']))),
            'proprietaire_id' => UtilisateurModel::staticGetIdByCode($medCode),
        ];

        if (isset($input['label'])) {
            $agendaInfos['titre'] = ucfirst((string)htmlspecialchars($input['label']));
        }

        $agendaModel = new AgendaModel();
        if ($agendaID === null) {  // on veut ajouter
            $agendaInfos['id_agenda'] = $agendaModel->insert($agendaInfos);
            $message = 'Agenda ajouté.';
            $statusCode = ResponseInterface::HTTP_CREATED;
        } else {  // on veut mettre à jour
            $agendaModel->update($agendaID, $agendaInfos);
            $agendaInfos['id_agenda'] = $agendaID;
            $message = 'Agenda modifié.';
            $statusCode = ResponseInterface::HTTP_OK;
        }

        $data = [];
        $data = array_merge($data, self::formatAgenda([0 => $agendaInfos])[0]);
        $data['id'] = $agendaInfos['id_agenda'];
        unset($data['id_agenda']);
        $response = [
            'statut'  => 'ok',
            'message' => $message,
            'data'    => $data,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response, $statusCode);
    }

    /**
     * addAgenda
     * 
     * Ajoute où met à jour l'agenda d'un medecin
     *
     * @param  string $medCode
     * @param  int $agendaID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addAgenda2(string $medCode, int $agendaID = null)
    {
        /* data attendue:
            [
                'debut': 'YYYY-mm-dd HH:ii',
                'fin': 'YYYY-mm-dd HH:ii',
                'label': 'a short text'
            ]
        */
        $rules = [
            'debut' => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de debut.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'fin'   => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de fin.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
        ];
        $input = $this->getRequestInput($this->request);
        if (!$this->validate($rules)) {
            $response = [
                'statut'  => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (isset($input['label'])) {
            $agendaInfos['titre'] = ucfirst((string)htmlspecialchars($input['label']));
        }

        $agendaModel = new AgendaModel();
        $agenda      = [];
        if ($agendaID === null) {  // on veut ajouter
            /*
                On part de la date de debut et on ajoute la duréé d'une consultation
                jusqu'à ce qu'on se trouve à l'heure de fin 
            */
            $jour    = date('Y-m-d', strtotime((string)htmlspecialchars($input['debut'])));
            $fin     = date('H:i', strtotime((string)htmlspecialchars($input['fin'])));
            $current = date('H:i', strtotime((string)htmlspecialchars($input['debut'])));
            $i = 1;
            while ($current < $fin) {
                $end     = date('H:i', strtotime("$current + " . ConsultationModel::DEFAULT_DUREE . " minutes"));
                $agendaInfos = [
                    'jour_dispo'          => $jour,
                    'heure_dispo_debut'   => $current,
                    'heure_dispo_fin'     => $end,
                    'statut'              => AgendaModel::AVAILABLE,
                    'proprietaire_id' => UtilisateurModel::staticGetIdByCode($medCode),
                ];
                $current = $end;
                //on insère
                $agendaInfos['id_agenda'] = $agendaModel->insert($agendaInfos);
                $agenda[] = $agendaInfos;
            }
            $message = 'Agenda ajouté.';
            $statusCode = ResponseInterface::HTTP_CREATED;
        }

        $data = [];
        $data = array_merge($data, self::formatAgenda([0 => $agendaInfos])[0]);
        $data['id'] = $agendaInfos['id_agenda'];
        unset($data['id_agenda']);
        $response = [
            'statut'  => 'ok',
            'message' => $message,
            'data'    => $data,
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response, $statusCode);
    }

    /**
     * addAgenda
     * 
     * Ajoute où met à jour l'agenda d'un medecin
     *
     * @param  string $medCode
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     * @OA\Schema(
     *  schema="AgendaData",
     *  description="Données d'enregistrement d'un agenda de médecin",
     *  type="object",
     *  title="Agenda",
     *  @OA\Property(property="debut", type="string", pattern="2023-12-16T08:30:00"),
     *  @OA\Property(property="fin", type="string", pattern="2023-12-16T08:30:00"),
     *  @OA\Property(property="duree", type="string", pattern="Disponible")
     * )
     * 
     * 
     * @param  string $medCode
     * @param  int $agendaID si spécifié, entraine une modification au lieu d'un ajout.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function addAgenda(string $medCode = null, int $agendaID = 0)
    {
        /* data attendue:
            [
                'debut': 'YYYY-mm-dd HH:ii',
                'fin': 'YYYY-mm-dd HH:ii',
                'label': 'a short text'
            ]
        */
        $rules = [
            'debut' => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de debut.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'fin'   => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de fin.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'duree' => [
                'rules' => 'if_exist|less_than[121]',
                'errors' => ['less_than' => 'Durée de consultation trop lomgue.']
            ],
        ];

        $input = $this->getRequestInput($this->request);

        if (!$this->validate($rules)) {
            $response = [
                'statut'  => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $jour  = date('Y-m-d', strtotime((string)htmlspecialchars($input['debut'])));
        $fin   = date('H:i:s', strtotime((string)htmlspecialchars($input['fin'])));
        $debut = date('H:i:s', strtotime((string)htmlspecialchars($input['debut'])));
        $duree = $input['duree'] ?? null;
        //* On evite les chevauchements
        $agendaModel = new AgendaModel();
        if ($medCode) {
            $userID = UtilisateurModel::staticGetIdByCode($medCode);
        } else {
            $userID = UtilisateurModel::staticGetIdByEmail($this->request->userEmail);
        }
        $currents = $agendaModel->asArray()->where('proprietaire_id', $userID)->findAll();
        foreach ($currents as $current) {
            $current['debut'] = date('Y-m-d H:i', strtotime($current['jour_dispo'] . ' ' . $current['heure_dispo_debut']));
            $current['fin'] = date('Y-m-d H:i', strtotime($current['jour_dispo'] . ' ' . $current['heure_dispo_fin']));
            // if ( ( ($current['heure_dispo_debut'] < $debut) && ($debut < $current['heure_dispo_fin']) ) || ( ($current['heure_dispo_debut'] < $fin) && ($fin < $current['heure_dispo_fin']) ) ) {
            if ((($current['debut'] < $debut) && ($debut < $current['fin'])) || (($current['debut'] < $fin) && ($fin < $current['fin']))) {
                $response = [
                    'statut'  => 'no',
                    'message' => 'Cette plage chevauche une autre.',
                    'token'   => $this->request->newToken ?? '',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
        }
        //*/
        $agendaInfos = [
            'jour_dispo'          => $jour,
            'heure_dispo_debut'   => $debut,
            'heure_dispo_fin'     => $fin,
            'titre'               => ucfirst((string)htmlspecialchars($input['label'] ?? 'Disponible')),
            'statut'              => AgendaModel::AVAILABLE,
            'proprietaire_id' => $userID,
        ];
        /*
            On part de la date de debut et on ajoute la duréé d'une consultation
            jusqu'à ce qu'on se trouve à l'heure de fin 
        */
        $i     = 1;
        $slots = [];
        while ($debut < $fin) {
            $duree = $duree ?? ConsultationModel::DEFAULT_DUREE;
            $end = date('H:i:s', strtotime("$debut + " . $duree . " minutes"));
            ($end > $fin) ? $end = $fin : $end;
            $slots[] = [
                'id'      => $i,
                'debut'   => $debut,
                'fin'     => $end,
            ];
            $debut = $end;
            $i++;
        }

        $agendaInfos['slots'] = json_encode($slots);

        if ($agendaID) {
            $agendaModel->update($agendaID, $agendaInfos);
            $agendaInfos['id_agenda'] = $agendaID;
            $message = 'Agenda mis a jour.';
        } else {
            //on insère
            $agendaInfos['id_agenda'] = $agendaModel->insert($agendaInfos);
            $message = 'Agenda ajouté.';
        }

        $response = [
            'statut'  => 'ok',
            'message' => $message,
        ];
        return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
    }

    public function updateAgenda($agendaID)
    {
        $input = $this->getRequestInput($this->request);
        return $this->addAgenda(null, $agendaID);
    }

    /** @deprecated no more used instead, use delCreneau
     * 
     * Supprime l'agenda spécifié
     *
     * @param  int $agendaID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteAgenda(int $agendaID)
    {
        $agendaModel = new AgendaModel();
        /* À implémenter.
            Vérifier que ce médecin n'as pas de rdv pour cette datte
            Si oui notifier les informés de  l'annulation du rendez-vous
        */
        try {
            $agendaModel->delete($agendaID);
        } catch (\Throwable $th) {
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Agenda supprimé.',
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }

    /**
     * delCreneau
     * 
     * Supprime dans l'agenda spécifié le créneau identifié
     *
     * @param  int $agendaID
     * @param  int $crenauID
     * @return \CodeIgniter\HTTP\ResponseInterface
     *//*
    public function delCreneau(int $agendaID, int $crenauID)
    {
        $agendaModel = new AgendaModel();
        $slots = $agendaModel->where('id_agenda', $agendaID)->findColumn('slots')[0];

        if ($slots) {
            $slots = array_filter($slots, function ($value) use ($crenauID) {
                if ($value['id'] == $crenauID) {
                    return false;
                }
                return true;
            });
            $slots = array_values($slots);
        } else {
            try {
                $agendaModel->set('statut', AgendaModel::NOT_AVAILABLE)
                    ->where('id_agenda', $agendaID)
                    ->update();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        $agendaModel->set('slots', json_encode($slots))
            ->where('id_agenda', $agendaID)
            ->update();

        $response = [
            'statut'  => 'ok',
            'message' => 'Créneau supprimé.',
            'token'   => $this->request->newToken ?? '',
        ];
        return $this->getResponse($response);
    }*/

    // Supprime une disponibilité dans l'agenda d'un medecin si aucun 
    // RDV n'est pris dans cette disponibilité
    public function delDispo(int $agendaID)
    {
        /*  
            Recupérer les slots, 
            Si l'un des slots a une visibilité à 0 (ou false)
                on empêche la suppression en retournant le slot reservé,
            Si non
                on supprimme la disponibilité
        */
        $agendaModel = new AgendaModel();
        // $slots = $agendaModel->where('id_agenda', $agendaID)->findColumn('slots')[0];
        $agenda = $agendaModel->asArray()->where('id_agenda', $agendaID)->first();
        $slots  = $agenda['slots'];

        $usedSlots = array_filter($slots, fn ($slot) => (isset($slot['visible']) && !$slot['visible']));

        if (count($usedSlots)) {
            // Non suppression renvoie de message
            $response = [
                'statut'  => 'no',
                'message' => "Impossible de supprimer la disponibilité car des Rendez-vous sont pris dans cette plage",
                'data'    => $usedSlots
            ];
            return $this->getResponse($response);
        } else {
            // Suppression de la disponibilite
            $agendaModel->delete($agendaID);
            $response = [
                'statut'  => 'ok',
                'message' => "Disponibilité Supprimmée"
            ];
            return $this->getResponse($response);
        }
    }



    // fonctions utilitaires
    public static function formatAgenda0(array $agenda): array
    {
        $result = [];
        $dates = AgendaModel::DAY;
        for ($i = 1; $i <= count($dates); $i++) {
            $result[$dates[$i]][0]['id'] = '';
            $result[$dates[$i]][0]['debut'] = '';
            $result[$dates[$i]][0]['fin'] = '';
            $result[$dates[$i]][0]['titre'] = '';
        }
        for ($i = 0; $i < count($agenda); $i++) {
            $data['id']    = $agenda[$i]['id_agenda'];
            $data['debut'] = $agenda[$i]['heure_dispo_debut'];
            $data['fin']   = $agenda[$i]['heure_dispo_fin'];
            $data['titre'] = $agenda[$i]['titre'];
            if ($result[$agenda[$i]['jour_dispo']][0]['id'] === '') {
                $result[$agenda[$i]['jour_dispo']][0] = $data;
            } else {
                $result[$agenda[$i]['jour_dispo']][] = $data;
            }
        }

        return $result;
    }
    public static function formatAgenda1(array $agenda): array
    {
        $jours = [];
        $jour = '';
        $result = [];
        $j = -1;
        for ($i = 0; $i < count($agenda); $i++) {
            if (!in_array($agenda[$i]['jour_dispo'], $jours, true)) {
                $jours[] = $agenda[$i]['jour_dispo'];
                $jour = $agenda[$i]['jour_dispo'];
                $j++;
            }
            $slot = [
                'id'  => $agenda[$i]['id_agenda'],
                'hour' => $jour . 'T' . $agenda[$i]['heure_dispo_debut'],
            ];
            $result[$j]['day']     = $jour;
            $result[$j]['slots'][] = $slot;
        }

        return $result;
    }

    /**
     * formate les données de d'un agenda
     *
     * @param  array $agendas
     * @return array
     * 
     * @OA\Schema(
     *  schema="AgendaResult",
     *  description="Données d'un agenda",
     *  type="object",
     *  title="AgendaResult",
     *  @OA\Property(property="day", type="string", pattern="2023-01-01", nullable="false"),
     *  @OA\Property(property="debut", type="string", pattern="2023-01-01T07:30:00", nullable="false"),
     *  @OA\Property(property="fin", type="string", pattern="2023-01-01T14:00:00", nullable="false"),
     *  @OA\Property(property="label", type="string", default="Disponible", nullable="false"),
     *  @OA\Property(
     *      property="slots",
     *      type="array",
     *      @OA\Items(
     *          @OA\Property(property="id", type="integer"),
     *          @OA\Property(property="hour", type="string", example="2023-01-01T07:30:00"),
     *      )
     *  )
     * )
     * 
     * @OA\Schema(
     *  schema="OldAgendaResult",
     *  description="un agenda de medecin",
     *  type="object",
     *  title="AgendaResult",
     *  @OA\Property(property="day", type="string", pattern="2023-12-16", nullable="false"),
     *  @OA\Property(property="debut", type="string", pattern="2023-12-16T08:30:00", nullable="false"),
     *  @OA\Property(property="fin", type="string", pattern="2023-12-16T08:30:00", nullable="false"),
     *  @OA\Property(property="label", type="string", default="Disponible", nullable="false"),
     *  @OA\Property(property="slots", type="object")
     * )
     * 
     */
    public static function formatAgenda(array $agendas): array
    {   //print_r($agendas);
        $result = [];
        foreach ($agendas as $agenda) {
            $data['code']  = $agenda['id_agenda'];
            $data['id']    = $agenda['id_agenda'];
            $data['day']   = $agenda['jour_dispo'];
            $jour = $data['day'];
            $data['debut'] = $jour . 'T' . $agenda['heure_dispo_debut'];
            $data['fin']   = $jour . 'T' . $agenda['heure_dispo_fin'];
            $data['label'] = $agenda['titre'];
            $data['dateCreation'] = $agenda['dateCreation'];
            // Extraction des slots invisibles (ayant eu une consultation)
            /* Fisrt try
                $data['slots'] = array_map(function ($value) use ($jour){
                    $slot['id']   = $value['id'];
                    $slot['hour'] = $jour.'T'.$value['debut'];
                    return $slot;
                }, $agenda['slots']);
            */
            ///* Second try
            $data['slots'] = array_map(function ($value) use ($jour) {
                $slot['id']   = $value['id'];
                $slot['hour'] = $jour . 'T' . $value['debut'];
                if (isset($value['visible']) && !$value['visible']) {
                    return;
                }
                return $slot;
            }, $agenda['slots']);
            // $data['slots'] = array_filter($data['slots'], fn($value) => ($value != null) ? $value : false );
            $data['slots'] = array_values(array_filter($data['slots'], fn ($value) => $value ?? false));

            //*/
            /* Working
                for ($i=0; $i < count($agenda['slots']); $i++) {
                    if (isset($agenda['slots'][$i]['visible']) && !$agenda['slots'][$i]['visible']) {
                        continue;
                    }
                    $data['slots'][$i]['id']   = $agenda['slots'][$i]['id'];
                    $data['slots'][$i]['hour'] = $jour.'T'.$agenda['slots'][$i]['debut'];
                }
            //*/
            $result[] = $data;
        }
        return $result;
    }


    // for test purposes only
    public function test()
    {
        /*
        $data2 = date('H:i:s', strtotime('2022-12-12 20:35:26'));
        $data = date('H:i:s', strtotime('14-12-2022 15:35:26 + 30 minutes'));
        if ($data2 > $data) {
            echo "$data2 > $data";
        }else {
            echo "$data2 < $data";
        }
        */
        $agendaModel = new AgendaModel();
        $data = $agendaModel->asArray()->where('proprietaire_id', 19)
            ->orderBy('jour_dispo', 'asc')
            // ->groupBy('jour_dispo')
            ->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => count($data) . ' resultats',
            'data'    => $data,
        ];
        return $this->getResponse($response);
    }


    // namespace Modules\Consultations\Controllers;

    // use App\Controllers\BaseController;
    // use CodeIgniter\HTTP\ResponseInterface;
    // use CodeIgniter\API\ResponseTrait;
    // use App\Traits\ControllerUtilsTrait;
    // use App\Traits\ErrorsDataTrait;

    //************************************************************************** */
    /**
     * Retourne la liste des rdvs d'un utilisateur
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier) {
            /* // Tout le monnde peut voir l'agenda d'un medecin 
                    if (!auth()->user()->inGroup('administrateur')) {
                        $response = [
                            'statut' => 'no',
                            'message' => 'Action non authorisée pour ce profil.',
                        ];
                        return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
                    }
                    */
            $identifier = $this->getIdentifier($identifier, 'id');
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }
        $agenda = model("AgendasModel")
            ->where("proprietaire_id", $utilisateur->id)
            ->where("statut", AgendaEntity::AVAILABLE)
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => (count($agenda) ? count($agenda) : 'Aucun') . ' Agenda trouvé(s).',
            'data'    => $agenda,
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
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $agendas = model("AgendasModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => (count($agendas) ? count($agendas) : 'Aucun') . ' Agenda(s) trouvé(s).',
            'data'    => $agendas,
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
        $agenda = model("AgendasModel")->where($identifier['name'], $identifier['value'])->first();
        $response = [
            'statut'  => 'ok',
            'message' => $agenda ? 'Détails de l\'agenda' : 'Agenda introuvable',
            'data'    => $agenda,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Cree un agenda
     *
     * @param  int|string $medIdentify 
     * @param  int $agendaID si spécifié, entraine une modification au lieu d'un ajout.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function create($medIdentify = null, int $agendaID = null)
    {
        if (!auth()->user()->can('agendas.create')) {
            if ($medIdentify && !auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
        }
        /* data attendue:
            [
                'debut': 'YYYY-mm-dd HH:ii',
                'fin': 'YYYY-mm-dd HH:ii',
                'label': 'a short text'
            ]
        */
        $rules = [
            'debut' => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de debut.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'fin'   => [
                'rules' => 'required|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de fin.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'titre' => [
                'rules' => 'if_exist|max_length[50]',
                'errors' => ['max_length' => 'Le titre doit contenir au plus 50 caractères.']
            ],
            'duree' => [
                'rules' => 'if_exist|less_than[121]|greater_than[10]',
                'errors' => ['less_than' => 'Durée de consultation trop longue.', 'greater_than' => 'Durée de consultation trop courte.']
            ],
        ];

        $input = $this->getRequestInput($this->request);

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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cet agenda.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        if (strtotime($input['debut']) < strtotime(now()) || strtotime($input['debut']) < strtotime($input['fin'])) {
            $response = [
                'statut'  => 'no',
                'message' => "Date de debut incohérente. ",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $jour  = date('Y-m-d', strtotime((string)htmlspecialchars($input['debut'])));
        $fin   = date('H:i:s', strtotime((string)htmlspecialchars($input['fin'])));
        $debut = date('H:i:s', strtotime((string)htmlspecialchars($input['debut'])));
        $duree = $input['duree'] ?? null;

        //* On evite les chevauchements
        $agendaModel = model("AgendasModel");
        if ($medIdentify) {
            $identifier = $this->getIdentifier($medIdentify);
            $medecin = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $medecin = $this->request->utilisateur;
        }

        $userID = $medecin->id;
        $currents = $agendaModel->asArray()->where('proprietaire_id', $userID)->findAll();

        foreach ($currents as $current) {
            $current['debut'] = date('Y-m-d H:i', strtotime($current['jour_dispo'] . ' ' . $current['heure_dispo_debut']));
            $current['fin'] = date('Y-m-d H:i', strtotime($current['jour_dispo'] . ' ' . $current['heure_dispo_fin']));

            // if ((($current['heure_dispo_debut'] <= $debut) && ($debut < $current['heure_dispo_fin'])) || (($current['heure_dispo_debut'] < $fin) && ($fin <= $current['heure_dispo_fin']))) {
            if ((($current['debut'] <= $input['debut']) && ($input['debut'] < $current['fin'])) || (($current['debut'] < $input['fin']) && ($input['fin'] <= $current['fin']))) {
                $response = [
                    'statut'  => 'no',
                    'message' => 'Cette plage chevauche une autre.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
        }
        //*/
        $agendaInfos = [
            'jour_dispo'          => $jour,
            'heure_dispo_debut'   => $debut,
            'heure_dispo_fin'     => $fin,
            'titre'               => ucfirst((string)htmlspecialchars($input['titre'] ?? 'Disponible')),
            'statut'              => AgendaEntity::AVAILABLE,
            'proprietaire_id' => $userID,
        ];
        /*
            On part de la date de debut et on ajoute la duréé d'une consultation
            jusqu'à ce qu'on se trouve à l'heure de fin 
        */
        $i     = 1;
        $slots = [];
        while ($debut < $fin) {
            $duree = $duree ?? ConsultationEntity::DEFAULT_DUREE;
            $end = date('H:i:s', strtotime("$debut + " . $duree . " minutes"));
            ($end > $fin) ? $end = $fin : $end;
            $slots[] = [
                'id'     => $i,
                'debut'  => $debut,
                'fin'    => $end,
                'occupe' => false,
            ];
            $debut = $end;
            $i++;
        }

        // $agendaInfos['slots'] = json_encode($slots);
        $agendaInfos['slots'] = $slots;

        // if ($agendaID) {
        //     // on met à jour
        //     $agendaModel->update($agendaID, $agendaInfos);
        //     $agendaInfos['id'] = $agendaID;
        //     $message = 'Agenda mis a jour.';
        //     $RespCode = ResponseInterface::HTTP_OK;
        // } else {

        //on insère
        $agendaInfos['id_agenda'] = $agendaModel->insert($agendaInfos);
        $message = 'Agenda ajouté.';
        $RespCode = ResponseInterface::HTTP_CREATED;
        // }

        $response = [
            'statut'  => 'ok',
            'message' => $message,
        ];
        return $this->sendResponse($response, $RespCode);
    }

    /** @todo think about the use cases
     * Modifie une converesation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'debut' => [
                'rules' => 'if_exist|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de debut.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'fin'   => [
                'rules' => 'if_exist|regex_match[/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}/]',
                'errors' => ['required' => 'Précisez la date/heure de fin.', 'regex_match' => 'Format de date attendu YYYY-mm-dd HH:ii.']
            ],
            'label' => [
                'rules' => 'if_exist|max_length[50]',
                'errors' => ['max_length' => 'Le titre doit contenir au plus 50 caractères.']
            ],
            'duree' => [
                'rules' => 'if_exist|less_than[121]|greater_than[10]',
                'errors' => ['less_than' => 'Durée de consultation trop longue.', 'greater_than' => 'Durée de consultation trop courte.']
            ],
            'statut' => [
                'rules' => 'if_exist|permit_empty',
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cet agenda.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        $agenda = model("AgendasModel")->asArray()->where('id', $id)->first();
        if (!$agenda) {
            $response = [
                'statut'  => 'no',
                'message' => "L'agenda spécifié est introuvable.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_FOUND);
        }
        // if ($agenda['proprietaire_id'] == $this->request->utilisateur->id) {
        if ($agenda['proprietaire_id'] == $this->request->utilisateur->id || auth()->user()->inGroup('administrateur')) {
            $condition = array_filter($agenda['slots'], fn ($sl) => isset($sl['occupe']) && $sl['occupe']);
            if ($condition) {
                $response = [
                    'statut'  => 'no',
                    'message' => "Vous nepouvez pas modifier cet agenda, il contient des plages occupées.",
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
            }
            if (isset($input['debut'])) {
                $jour  = date('Y-m-d', strtotime((string)htmlspecialchars($input['debut'])));
                $debut = date('H:i:s', strtotime((string)htmlspecialchars($input['debut'])));
                $agenda['jour_dispo'] = $jour;
                $agenda['heure_dispo_debut'] = $debut;
            }
            if (isset($input['fin'])) {
                $jour = date('Y-m-d', strtotime((string)htmlspecialchars($input['fin'])));
                $fin  = date('H:i:s', strtotime((string)htmlspecialchars($input['fin'])));
                $agenda['jour_dispo'] = $jour;
                $agenda['heure_dispo_fin'] = $fin;
            }
            if (isset($input['titre'])) {
                $agenda['titre'] = $input['titre'];
            }
            if (isset($input['statut'])) {
                $agenda['statut'] = (bool)$input['statut'];
            }
            if (isset($input['duree'])) {
                $agenda['duree'] = (int)$input['duree'];
            }
            $i     = 1;
            $slots = [];
            $debut = $debut ?? date('H:i:s', strtotime($agenda['heure_dispo_debut']));
            $fin   = $fin ?? date('H:i:s', strtotime($agenda['heure_dispo_fin']));
            while ($debut < $fin) {
                $duree = $agenda['duree'] ?? ConsultationEntity::DEFAULT_DUREE;
                $end = date('H:i:s', strtotime("$debut + " . $duree . " minutes"));
                ($end > $fin) ? $end = $fin : $end;
                $slots[] = [
                    'id'     => $i,
                    'debut'  => $debut,
                    'fin'    => $end,
                    'occupe' => false,
                ];
                $debut = $end;
                $i++;
            }
        } else {
            $response = [
                'statut'  => 'no',
                'message' => "Vous n'avez pas le droit de modifier cet agenda.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }

        $agenda['slots'] = $slots;
        model("AgendasModel")->update($agenda['id'], $agenda);
        $response = [
            'statut'  => 'ok',
            'message' => 'Agenda mis à jour.',
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
     * delCreneau
     * 
     * Supprime dans l'agenda spécifié le créneau identifié
     *
     * @param  int $agendaID
     * @param  int $crenauID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delCreneau(int $agendaID, int $crenauID)
    {
        $agendaModel = model("AgendasModel");
        $slots = $agendaModel->where('id', $agendaID)->findColumn('slots')[0];

        if ($slots) {
            $slots = array_filter($slots, function ($value) use ($crenauID) {
                if ($value['id'] == $crenauID) {
                    return false;
                }
                return true;
            });
            $slots = array_values($slots);
        } else {
            try {
                $agendaModel->set('statut', AgendaEntity::NOT_AVAILABLE)
                    ->where('id', $agendaID)
                    ->update();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }

        $agendaModel->set('slots', json_encode($slots))
            ->where('id', $agendaID)
            ->update();

        $response = [
            'statut'  => 'ok',
            'message' => 'Créneau supprimé.',
        ];
        return $this->sendResponse($response);
    }
    // }
}
