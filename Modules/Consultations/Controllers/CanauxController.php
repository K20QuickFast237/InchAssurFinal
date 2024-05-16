<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Consultations\Entities\LangueEntity;

class CanauxController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des canaux, ou ceux du médecin spécifié.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier) {
            $identifier = $this->getIdentifier($identifier, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
            $canauxIDs = model("MedecinCanauxModel")->where("medecin_id", $med->id)->findColumn("canal_id") ?? [];
            if ($canauxIDs) {
                $canaux = model("CanauxModel")->whereIn("id", $canauxIDs)->findAll();
            } else {
                $canaux = [];
            }
        } else {
            $canaux = model("CanauxModel")->findAll() ?? [];
        }

        $response = [
            'statut'  => 'ok',
            'message' => (count($canaux) ? count($canaux) . ' canaux' : 'Aucun canal') . ' trouvée(s).',
            'data'    => $canaux,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute un canal
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        /* Strictement réservé aux administrateurs */
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => ' Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'nom' => [
                'rules' => 'required',
                'errors' => ['required' => 'Le nom du canal est requis.',]
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter ce canal.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        model("CanauxModel")->insert(['nom' => $input['nom']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Canal Ajouté.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie un canal
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        /* Strictement réservé aux administrateurs */
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => ' Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'nom' => [
                'rules' => 'required',
                'errors' => ['required' => 'Le nom du canal est requis.',]
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de modifier ce canal.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input = $this->getRequestInput($this->request);
        model("CanauxModel")->update($id, ['nom' => $input['nom']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Canal Modifié.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime un canal
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        /* Strictement réservé aux administrateurs */
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        model("CanauxModel")->delete($id);
        $response = [
            'statut'  => 'ok',
            'message' => 'Canal Supprimé.',
        ];
        return $this->sendResponse($response);
    }

    /** 
     * Associe des canaux au médecin spécifié.
     *
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function setMedCanal($medIdentity)
    {
        if ($medIdentity) {
            if (!auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($medIdentity, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            if (!auth()->user()->inGroup('medecin')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $med = $this->request->utilisateur;
        }

        $rules = [
            'canaux'   => 'required',
            'canaux.*' => 'integer|is_not_unique[canaux.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("MedecinCanauxModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            foreach ($input['canaux'] as $idCanal) {
                $model->insert(["medecin_id" => $med->id, "canal_id" => (int)$idCanal]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Canal(aux) Défini(s) pour le médecin.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce(s) canal(aux) au médecin.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Supprime une association entre un medecin et un canal
     *
     * @param  int $idCanal
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function delMedCanal($idCanal, $medIdentity)
    {
        $identifier = $this->getIdentifier($medIdentity, 'id');
        $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        model("MedecinCanauxModel")->where("medecin_id", $med->id)->where("canal_id", $idCanal)->delete();
        $response = [
            'statut'  => 'ok',
            'message' => "Canal retiré pour ce médecin.",
        ];
        return $this->sendResponse($response);
    }
}
