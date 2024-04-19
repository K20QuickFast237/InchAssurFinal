<?php

namespace Modules\Paiements\Controllers;

use \OpenApi\Annotations as OA;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Config\Services;
use Paiement\Models\PaiementModel;
use Transaction\Models\LignetransactionModel;
use Transaction\Models\TransactionModel;

use User\Models\UtilisateurModel;

/**
 * une ligne de transaction ne peut être créée à une date différente de celle de la transaction
 */
class TransactionsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    /**
     * getAllUserTransacts
     * 
     * Renvoie la liste des transactions de l'utilisateur courant où
     * celles de celui dont le code est passé en paramètre
     *
     * @param  string $userCode
     * @return CodeIgniter\HTTP\ResponseInterface
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

        $userID = (int)$utilisateur->id;
        $transactions = model("TransactionsModel")->where("beneficiaire_id", $userID)
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => count($transactions) . ' transaction(s) trouvée(s)',
            'data'    => $transactions,
        ];
        return $this->sendResponse($response);
    }

    /**
     * getdetails
     * 
     * Renvoie les lignes de transaction constituant les détails de 
     * la transasction
     *
     * @param  int $transactID
     * @return CodeIgniter\HTTP\ResponseInterface
     */
    public function getDetails($transactID)
    {
        $identifier = $this->getIdentifier($transactID, 'id');
        $transaction = model("TransactionsModel")->where($identifier['name'], $identifier['value'])->first();
        if ($transaction) {
            $transaction->lignes;
            $transaction->paiements;
        }

        $response = [
            'statut'  => 'ok',
            'message' => $transaction ? "Détails de la transaction" : "Transaction introuvable.",
            'data'    => $transaction,
        ];
        return $this->sendResponse($response);
    }

    /**
     * getReglements
     * 
     * Renvoie les règlements de la transaction spécifiée par son identifiant
     *
     * @param  int $transactID
     * @return CodeIgniter\HTTP\ResponseInterface
     */
    public function getReglements($transactID)
    {
        $identifier = $this->getIdentifier($transactID, 'id');
        $transaction = model("TransactionsModel")->where($identifier['name'], $identifier['value'])->first();
        if ($transaction) {
            $paiements = $transaction->paiements;
        }

        $response = [
            'statut'  => 'ok',
            'message' => $transaction ? "Paiements associés à la transaction" : "Transaction introuvable.",
            'data'    => $paiements,
        ];
        return $this->sendResponse($response);
    }

    /* 
    /**
     * getAllTransacts
     * 
     * Renvoie la liste de toutes les transactions de la plateforme (pour admin)
     *
     * @param  string $userCode
     * @return CodeIgniter\HTTP\ResponseInterface
     *
    public function getAllTransacts(): \CodeIgniter\HTTP\ResponseInterface
    {
        $transactModel = new TransactionModel();
        try {
            $transacts = $transactModel->getAllTransacts();
            $response = [
                'statut'  => 'ok',
                'message' => count($transacts) . ' transaction(s) trouvée(s)',
                'data'    => $transacts,
            ];
            return $this->getResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Echec de Traitement.',
                'errors'  => $th->getMessage()
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function indexFirst($identifier = null)
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
        $incidents = model("IncidentsModel")->where("auteur_id", $utilisateur->id)
            ->where("etat", IncidentsEntity::ACTIF)
            ->orderBy('id', "DESC")
            ->findAll();

        $response = [
            'statut' => 'ok',
            'message' => count($incidents) ? 'Incidents déclarés.' : 'Aucun incident déclaré.',
            'data' => $sinistres ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * getdetails
     * 
     * Renvoie les lignes de transaction constituant les détails de 
     * la transasction
     *
     * @param  int $transactID
     * @return CodeIgniter\HTTP\ResponseInterface
     *
    public function getDetails(int $transactID)
    {
        $lignetransactModel = new LigneTransactionModel();
        try {
            $details  = $lignetransactModel->getTransactLignes($transactID);
            $response = [
                'statut'  => 'ok',
                'message' => count($details) . ' élément(s) trouvé(s)',
                'data'    => $details,
            ];
            return $this->getResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Echec de Traitement.',
                'errors'  => $th->getMessage()
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAllReglements()
    {
        $reglementModel = new PaiementModel();
        try {
            $reglements = $reglementModel->getAllReglements();
            $response = [
                'statut'  => 'ok',
                'message' => count($reglements) . ' reglement(s) trouvé(s)',
                'data'    => $reglements,
            ];
            return $this->getResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Echec de Traitement.',
                'errors'  => $th->getMessage()
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function teste()
    {
        $data = [
            'type' => 'Totale',
            'prix' => 500,
            'net_a_payer' =>  500,
            'avance' => 500,
            'reste' => 0,
            'payeur_id' => 19
        ];
        $transactModel = new TransactionModel();
        // $id = $transactModel->insert($data);

        return $this->getResponse([
            'message' => 'OK',
            'id' => $id ?? null,
        ]);
    }
    */
}
