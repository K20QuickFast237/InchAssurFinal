<?php

namespace  Modules\Utilisateurs\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Utilisateurs\Entities\PortefeuillesEntity;
use CodeIgniter\Database\Exceptions\DataException;
use Modules\Paiements\Entities\PaiementEntity;
use Modules\Paiements\Entities\TransactionEntity;

class PortefeuillesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['text'];

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
        // if (!$user->can('pockets.update')) {
        if (!$user->inGroup('administrateur')) {
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

    public function recharge()
    {

        $rules = [
            'montant'    => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => 'Opérateur non défini.', 'numeric' => 'Montant invalide'],
            ],
            'operateur'  => [
                'rules'  => 'required|is_not_unique[paiement_modes.operateur]',
                'errors' => ['required' => 'Opérateur non défini.', 'is_not_unique' => 'Opérateur invalide'],
            ],
            'telephone'  => [
                'rules'  => 'required|numeric',
                'errors' => ['required' => 'Numéro de téléphone requis pour ce mode de paiement.', 'numeric' => 'Numéro de téléphone invalide.']
            ],
            'returnURL'  => [
                'rules'  => 'required|valid_url',
                'errors' => ['required' => 'L\'URL de retour doit être spécifiée pour ce mode de paiement.', 'valid_url' => 'URL de retour non conforme.']
            ],
        ];
        $input = $this->getRequestInput($this->request);
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if ($input['operateur'] != 'PORTE_FEUILLE' && !$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'envoyer cette demande.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        // Récupérer le portefeuille
        $utilisateur = $this->request->utilisateur;
        $pocket = model('PortefeuillesModel')->where('utilisateur_id', $utilisateur->id)->first();
        $amount = (float)$input['montant'];

        // Définir les éléments de la transaction
        $transactInfo = [
            "code"            => random_string('alnum', 10),
            "motif"           => "Recharge portefeuille",
            "beneficiaire_id" => $utilisateur->id,
            "prix_total"      => $amount,
            "tva_taux"        => 0, //$tva->taux,
            "valeur_tva"      => 0, //$prixTVA,
            "net_a_payer"     => $amount,
            "avance"          => $amount,
            "reste_a_payer"   => 0,
            "pay_option_id"   => 1,
            "etat"            => TransactionEntity::EN_COURS,
        ];
        $transactInfo['id'] = model('TransactionsModel')->insert($transactInfo);

        $paiementInfo = [
            'code'      => random_string('alnum', 10),
            'montant'   => $amount,
            'statut'    => PaiementEntity::EN_COURS,
            'mode_id'   => model("PaiementModesModel")->where('operateur', $input['operateur'])->findColumn('id')[0] ?? 1,
            'auteur_id' => $utilisateur->id,
            'transaction_id' => $transactInfo['id'],
        ];
        $paiementInfo['id'] = model('PaiementsModel')->insert($paiementInfo);

        $monetbil_args = array(
            'amount'      => $amount,
            'phone'       => $input['telephone'] ?? $utilisateur->tel1,
            'country'     => $input['pays'] ?? null,
            'phone_lock'  => false,
            'locale'      => 'fr', // Display language fr or en
            'operator'    => $input['operateur'],
            'item_ref'    => $transactInfo['id'],
            'payment_ref' => $paiementInfo['code'],
            'user'        => $utilisateur->code,
            'return_url'  => $input['returnURL'],
            'notify_url'  => base_url('paiements/notfyRecharge'),
            'logo'        => base_url("uploads/images/logoinch.jpeg"),
        );
        $data = ['url' => \Monetbil::url($monetbil_args)];

        $response = [
            'statut'  => 'ok',
            'message' => "Recharge initiée.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }
}
