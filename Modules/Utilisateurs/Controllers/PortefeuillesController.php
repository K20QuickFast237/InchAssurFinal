<?php

namespace  Modules\Utilisateurs\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Utilisateurs\Entities\PortefeuillesEntity;

class PortefeuillesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    // protected $helpers = ['text', 'Modules\Images\Images'];

    /**
     * Retrieve all pockets records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user->can('pockets.list')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $data = model("PortefeuillesModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => $data ? 'Portefeuilles disponibles.' : "Aucun portefeuille disponible pour le moment",
            'data'    => $data,
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
        $user = auth()->user();
        if (!$user->can('pockets.show')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        try {
            $data = model("PortefeuillesModel")->where('id', $id)->first();
        } catch (\Throwable $th) {
            $response = [
                'status'  => 'no',
                'message' => 'Reduction introuvable.',
                'data'    => [],
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        $response = [
            'statut'  => 'ok',
            'message' => 'Détails du Portefeuille.',
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Creates a new pocket record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user->can('pockets.create')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $rules = [
            "idUtilisateur"  => [
                'rules'  => 'required|is_not_unique[utilisateurs.id]|is_unique[portefeuilles.utilisateur_id]',
                'errors' => [
                    'required'  => "l'identifiant du propiétaire est requis.",
                    'is_unique' => 'Cet utilisateur pocède déjà un portefeuille.',
                    'is_not_unique' => "Cet utilisateur n'est pas reconnu.",
                ]
            ],
            "solde"          => [
                'rules'  => 'required|less_than[1000001]',
                'errors' => [
                    'required'  => 'Le solde du portefeuille est requis.',
                    'less_than' => 'Solde du portefeuille suppérieure à la limite autorisée.'
                ]
            ],
            // "devise"         => 'if_exist',
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette reduction.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input['devise']  = 'XAF';
        $infoPortefeuille = new PortefeuillesEntity($input);
        $infoPortefeuille->id = model("PortefeuillesModel")->insert($infoPortefeuille);

        $response = [
            'statut'  => 'ok',
            'message' => 'Portefeuille ajoutée.',
            'data'    => $infoPortefeuille,
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a pocket, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $user = auth()->user();
        if (!$user->can('pockets.update')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $rules = [
            "idUtilisateur"  => [
                'rules'  => 'if_exist|is_not_unique[portefeuilles.utilisateur_id]',
                'errors' => ['is_not_unique' => 'Portefeuille non reconnu.'],
            ],
            "solde"          => [
                'rules'  => 'if_exist|less_than[1000000]',
                'errors' => [
                    'less_than' => 'La valeur du solde est invalide.',
                ]
            ],
            // "devise"         => 'if_exist',
        ];
        $input = $this->getRequestInput($this->request);
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $infoPortefeuille = new PortefeuillesEntity($input);
            model("PortefeuillesModel")->update($id, $infoPortefeuille);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de modifier ce portefeuille.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Portefeuille Mis à jour.',
            'data'          => model("PortefeuillesModel")->find($id),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Delete the designated pocket record in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        $user = auth()->user();
        if (!$user->can('pockets.delete')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        try {
            model("PortefeuillesModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Identification du portefeuille impossible.',
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Portefeuille supprimé.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }

    public function getUserPocket($identifier = null)
    {
        $user = auth()->user();
        if ($identifier) {
            if (!$user->can('pockets.getUserPocket')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($identifier);
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Détails du Portefeuille.',
            'data'    => $utilisateur->pocket,
        ];
        return $this->sendResponse($response);
    }
}
