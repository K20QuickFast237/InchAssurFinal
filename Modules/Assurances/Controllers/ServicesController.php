<?php

namespace  Modules\Assurances\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Assurances\Entities\ServicesEntity;

class ServicesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    /**
     * Retrieve all services records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $response = [
            'status' => 'ok',
            'message' => 'Services disponibles.',
            'data' => model("ServicesModel")->findAll(),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a service
     *
     * @param  int $id - the specified service Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $data = model("ServicesModel")->where($identifier['name'], $identifier['value'])->first();
        try {
            $response = [
                'status' => 'ok',
                'message' => 'Détails du service.',
                'data' => $data ?? throw new \Exception("Service introuvable"),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status' => 'no',
                'message' => 'Service introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Creates a new service record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            'nom'             => [
                'rules'  => 'required|is_unique[services.nom]',
                'errors' => ['is_unique' => 'Ce service existe déja.']
            ],
            'description'     => 'if_exist',
            'quantite'        => 'if_exist|integer',
            'taux_couverture' => [
                'rules'  => 'required_without[prix_couverture]',
                'errors' => ['required_without' => 'La couverture (taux ou valeur) est obligatoire']
            ],
            'prix_couverture' => [
                'rules'  => 'required_without[taux_couverture]',
                'errors' => ['required_without' => 'La couverture (taux ou valeur) est obligatoire']
            ]
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }

            $id = model("ServicesModel")->insert(new ServicesEntity($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de créer ce service.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input['idService'] = $id;
        $response = [
            'statut'        => 'ok',
            'message'       => 'Service ajouté.',
            'data'          => $input,
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a service, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $input = $this->getRequestInput($this->request);

        $rules = [
            'nom'            => 'if_exist',
            'description'    => 'if_exist',
            'tauxCouverture' => 'if_exist',
            'prixCouverture' => 'if_exist',
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $identifier = $this->getIdentifier($id, 'id');
            model("ServicesModel")->update($identifier['value'], new ServicesEntity($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour ce service.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Service mis à jour.',
            'data'          => $input,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Delete the designated service records in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            model("ServicesModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Identification du service impossible.',
                'errors' => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Service supprimé.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }

    /*
        /**
         * Retrieves the list of states.
         *
         * @return ResponseInterface The HTTP response.
         *
        public function getStates()
        {
            $entity = new ServicesEntity();
            $response = [
                'status' => 'ok',
                'message' => 'Status de services.',
                'data' => $entity->statesList,
            ];
            return $this->sendResponse($response);
        }
    */
}
