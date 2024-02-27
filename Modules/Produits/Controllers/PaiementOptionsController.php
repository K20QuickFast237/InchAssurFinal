<?php

namespace  Modules\Produits\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DataException;
use Modules\Produits\Entities\PaiementOptionsEntity;
use Modules\Produits\Entities\ReductionsEntity;

class PaiementOptionsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;


    /**
     * Retrieve all paiement options records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $modes = model("PaiementOptionListsModel")->findAll();
        foreach ($modes as $key => $mode) {
            switch ($mode["nom"]) {
                case 'Paiement Unique':
                    $modes[$key] = array_merge(
                        $mode,
                        ["tauxDepotInitial" => null,]
                    );
                    break;

                case 'Paiement Etagé':
                    $modes[$key] = array_merge(
                        $mode,
                        [
                            "tauxDepotInitial" => null,
                            "dureeEtape"       => null,
                        ]
                    );
                    break;

                case 'Paiement Planifié':
                    $modes[$key] = array_merge(
                        $mode,
                        [
                            "tauxDepotInitial" => null,
                            "tauxCycle"        => null,
                            "longueurCycle"    => null,
                            "nombreCycle"      => null,
                        ]
                    );
                    break;

                default:
                    $mode = array_merge(
                        $mode,
                        ["tauxDepotInitial" => null,]
                    );
                    break;
            }
        }

        $response = [
            'status' => 'ok',
            'message' => 'Options de paiements disponibles.',
            'data' => $modes,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a Paiement Option
     *
     * @param  int $id - the specified option Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $mode = model("PaiementOptionsModel")->find($id) ?? throw new \Exception();
            $entityName = model("PaiementOptionListsModel")->getEntityName($mode->type);

            $response = [
                'status'  => 'ok',
                'message' => "Détails de l'option de paiement.",
                'data'    => new $entityName($mode->toArray()),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status' => 'no',
                'message' => 'Option de paiement introuvable.',
                'data' => [],
                'error' => $th->getMessage()
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Creates a new Paiement Option record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $rules = [
            "type"             => "required|string|is_not_unique[paiement_options_lists.nom]",
            "nom"              => "required|string",
            "description"      => "if_exist|string",
            "tauxDepotInitial" => "required|numeric",
            "dureeEtape"       => "if_exist|integer",
            "longueurCycle"    => "if_exist|required_with[tauxCycle,nombreCycle]|integer",
            "tauxCycle"        => "if_exist|required_with[longueurCycle,nombreCycle]|numeric",
            "nombreCycle"      => "if_exist|required_with[longueurCycle,tauxCycle]|integer",
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $modelName = model("PaiementOptionListsModel")->getModelName($input['type']);
            $entityName = model("PaiementOptionListsModel")->getEntityName($input['type']);
            $input['id'] = model($modelName)->insert(new $entityName($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette option de paiement.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Option de paiement ajoutée.',
            'data'          => new $entityName($input),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a paiement option, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            "type"             => "required|string|is_not_unique[paiement_options_lists.nom]",
            "nom"              => "if_exist|string",
            "description"      => "if_exist|string",
            "tauxDepotInitial" => "required|numeric",
            "dureeEtape"       => "if_exist|integer",
            "longueurCycle"    => "if_exist|required_with[tauxCycle,nombreCycle]|integer",
            "tauxCycle"        => "if_exist|required_with[longueurCycle,nombreCycle]|numeric",
            "nombreCycle"      => "if_exist|required_with[longueurCycle,tauxCycle]|integer",
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $modelName = model("PaiementOptionListsModel")->getModelName($input['type']);
            $entityName = model("PaiementOptionListsModel")->getEntityName($input['type']);
            $input['id'] = $id;
            $payOpt = new $entityName($input);
            model($modelName)->update($id, $payOpt);
        } catch (DataException $de) {
            $response = [
                'statut'  => 'ok',
                'message' => "Aucune modification apportée.",
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette option de paiement.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Reduction mise à jour.',
            'data'          => array_filter($payOpt->toArray()),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Delete the designated paiement option record in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            model("PaiementOptionsModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => "Identification de l'option de paiement impossible.",
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Option de paiement supprimée.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }
}
