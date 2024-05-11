<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;

class LanguesController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des villes
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        $villes = model("LanguesModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($villes) ? count($villes) : 'Aucune') . ' langue(s) trouvée(s).',
            'data'    => $villes,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une langue
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
                'errors' => ['required' => 'Le nom de la langue est requis.',]
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette ville.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        model("Languesmodel")->insert(['nom' => $input['nom']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Langue Ajoutée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie une langue
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
                'errors' => ['required' => 'Le nom de la langue est requis.',]
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de modifier cette langue.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input = $this->getRequestInput($this->request);
        model("Languesmodel")->update($id, ['nom' => $input['nom']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Langue Modifiée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime une langue
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        model("Languesmodel")->delete($id);
        $response = [
            'statut'  => 'ok',
            'message' => 'Langue Supprimée.',
        ];
        return $this->sendResponse($response);
    }
}
