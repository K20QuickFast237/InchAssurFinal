<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;

class LocalisationsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des localisations
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier && auth()->user()->inGroup('administrateur')) {
            $identifier = $this->getIdentifier($identifier, 'id');
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }

        $locationInfos = model("MedecinLocalisationsModel")
            ->select("localisation_id, isDefault")
            ->where("medecin_id", $utilisateur->id)
            ->findAll();
        $locationIDs = array_column($locationInfos, 'localisation_id');
        $default = array_filter($locationInfos, fn ($l) => $l->isDefault);
        // $locationIDs = model("MedecinLocalisationsModel")->where("medecin_id", $utilisateur->id)->findColumn("localisation_id");
        if ($locationIDs) {
            $locations = model("LocalisationsModel")->whereIn("id", $locationIDs)->findAll() ?? [];
        } else {
            $locations = [];
        }
        $locations = array_map(function ($l) use ($default) {
            if ($l->id == $default['localisation_id']) {
                $l->isDefault = true;
            } else {
                $l->isDefault = false;
            }
            return $l;
        }, $locations);

        $response = [
            'statut'  => 'ok',
            'message' => (count($locations) ? count($locations) : 'Aucune') . ' localisation(s) trouvée(s).',
            'data'    => $locations,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a location
     *
     * @param  int $id - the specified location Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $data = model("LocalisationsModel")->where('id', $id)->first();
            $response = [
                'statut'  => 'ok',
                'message' => 'Détails de la Localisation.',
                'data'    => $data ?? throw new \Exception('Localisation introuvable.'),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Localisation introuvable.',
                'data'    => [],
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Retourne la liste des localisations disponibles
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => ' Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $locations = model("LocalisationsModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($locations) ? count($locations) : 'Aucune') . ' localisation(s) trouvée(s).',
            'data'    => $locations,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une ville
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            'adresse' => [
                'rules' => 'required',
                'errors' => ['required' => 'L\'adresse est requise.',]
            ],
            'etablissement' => [
                'rules' => 'if_exist|is_unique[localisations.adresse]',
                'errors' => ["is_unique" => "Cet établissement a déjà été enregistré"]
            ],
            'ville' => 'if_exist',
            'latitude' => 'if_exist',
            'longitude' => 'if_exist',
            'coordonnées' => 'if_exist',
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette ville.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        model("LocalisationsModel")->insert($input);
        $response = [
            'statut'  => 'ok',
            'message' => 'Localisation Ajoutée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie une ville
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        /* Ne peut être modifié que par un administrateur ou un utilisateur étant le seul à y être associé */
        if (!(auth()->user()->inGroup('administrateur') || $this->canModify($id))) {
            $response = [
                'statut'  => 'no',
                'message' => 'Vous ne pouvez pas modifier cette localisation, accès refusé.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'adresse'       => 'if_exist',
            'ville'         => 'if_exist',
            'etablissement' => 'if_exist',
            'latitude'      => 'if_exist',
            'longitude'     => 'if_exist',
            'coordonnées'   => 'if_exist',
        ];
        $input = $this->getRequestInput($this->request);

        try {
            model("LocalisationsModel")->update($id, $input);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Vous ne pouvez pas modifier cette localisation, accès refusé.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $response = [
            'statut'  => 'ok',
            'message' => 'Localisation Modifiée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime une localisation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        if ((auth()->user()->inGroup('administrateur') || $this->canModify($id))) {
            model("LocalisationsModel")->delete($id);
            $response = [
                'statut'  => 'ok',
                'message' => 'Localisation Supprimée.',
            ];
            return $this->sendResponse($response);
        } else {
            $response = [
                'statut'  => 'no',
                'message' => 'Vous ne pouvez pas modifier cette localisation, accès refusé.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Associe un medecin à une localisation
     *
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function setMedLocation($medIdentity)
    {
        $rules = [
            'localisations'   => 'required',
            'localisations.*' => 'integer|is_not_unique[localisations.id]',
            'default'         => 'if_exist|permit_empty'
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("MedecinLocalisationsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            // $model->where("skill_id", (int)$id)->delete();
            foreach ($input['localisations'] as $idLocation) {
                $model->insert(["medecin_id" => (int)$medIdentity, "localisation_id" => (int)$idLocation]);
            }
            if ($input['default'] && !(array_search($input['default'], $input['localisations']) === false)) {
                $model->where("medecin_id", $medIdentity)
                    ->where("localisation_id", $input['default'])
                    ->set('isDefault', true)
                    ->update();
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Localisation(s) Définie(s) pour le médecin.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce(s) localisations(s) au médecin.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Supprime une association entre un medecin et une localisation
     *
     * @param  int $idLocation
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function delMedLocation($idLocation, $medIdentity)
    {
        $identifier = $this->getIdentifier($medIdentity, 'id');
        $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        model("MedecinLocalisationsModel")->where("medecin_id", $med->id)->where("localisation_id", $idLocation)->delete();
        $response = [
            'statut'  => 'ok',
            'message' => "Localisation retirée pour ce médecin.",
        ];
        return $this->sendResponse($response);
    }


    private function canModify(int $idLocation)
    {
        $cond = model("MedecinLocalisationsModel")->where("localisation_id", $idLocation)->findColumn("medecin_id");
        return $cond && count($cond) == 1 && $cond[0]['medecin_id'] == $this->request->utilisateur->id;
    }
}
