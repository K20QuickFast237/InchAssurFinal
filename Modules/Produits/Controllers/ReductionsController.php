<?php

namespace  Modules\Produits\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Produits\Entities\ReductionsEntity;

class ReductionsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;


    /**
     * Retrieve all product reductions records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $response = [
            'status' => 'ok',
            'message' => 'Reductions disponibles.',
            'data' => model("ReductionsModel")->findAll(),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a reduction
     *
     * @param  int $id - the specified reduction Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $data = model("ReductionsModel")->where('id', $id)->first();
            $response = [
                'status'  => 'ok',
                'message' => 'Détails de la reduction.',
                'data'    => $data ?? throw new \Exception('Reduction introuvable.'),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status'  => 'no',
                'message' => 'Reduction introuvable.',
                'data'    => [],
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Creates a new reduction record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            "description"    => 'required',
            "code"           => [
                'rules'  => 'required|is_unique[reductions.code]',
                'errors' => ['unique' => 'Ce code est déjà utilisé pour une reduction.']
            ],
            "valeur"         => 'required_without[taux]|numeric',
            "taux"           => 'required_without[valeur]|less_than_equal_to[100]',
            "dateExpiration" => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => ['valid_date' => "Format de date attendu YYYY-MM-DD."],
            ],
            "nombreUtilise"  => 'required|integer',
            "nombreUsageMax" => 'required|integer',
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $input['auteur_id'] = 1;
            $input['id'] = model("ReductionsModel")->insert(new ReductionsEntity($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette reduction.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Reduction ajoutée.',
            'data'          => new ReductionsEntity($input),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a reduction, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            "description"    => 'if_exist',
            "code"           => 'if_exist',
            "valeur"         => 'if_exist|numeric',
            "taux"           => 'if_exist|less_than_equal_to[100]',
            "dateExpiration" => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => "Format de date attendu YYYY-MM-DD."],
            ],
            "nombreUtilise"  => 'if_exist|integer',
            "nombreUsageMax" => 'if_exist|integer',
        ];

        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $input['id'] = $id;
            $reduction =  new ReductionsEntity($input);
            model("ReductionsModel")->update($id, $reduction);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette reduction.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Reduction mise à jour.',
            'data'          => array_filter($reduction->toArray()),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Delete the designated reduction records in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            model("ReductionsModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Identification de la categorie impossible.',
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Reduction supprimée.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }
}
