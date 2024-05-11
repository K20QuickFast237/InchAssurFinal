<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;

class RdvsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des rdvs d'un utilisateur
     *
     * @return ResponseInterface The HTTP response.
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
        $rdvs = model("RdvsModel")
            ->where("emetteur_user_id", $utilisateur->id)
            ->orwhere("destinataire_user_id", $utilisateur->id)
            ->groupBy('destinataire_user_id', 'desc')
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => (count($rdvs) ? count($rdvs) : 'Aucun') . ' rendez-vous trouvé(s).',
            'data'    => $rdvs,
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
        if (!auth()->user()->can('consultations.viewAll')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rdvs = model("RdvsModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => (count($rdvs) ? count($rdvs) : 'Aucun') . ' rendez-vous trouvé(s).',
            'data'    => $rdvs,
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
        $rdv = model("RdvsModel")->where($identifier['name'], $identifier['value'])->first();
        if (
            $rdv &&
            (auth()->user()->inGroup('administrateur') ||
                $rdv->emetteur_user_id == $this->request->utilisateur->id ||
                $rdv->destinataire_user_id == $this->request->utilisateur->id)
        ) {
            $response = [
                'statut'  => 'ok',
                'message' => 'Détails du rendez-vous.',
                'data'    => $rdv,
            ];
            return $this->sendResponse($response);
        }
        $response = [
            'statut'  => 'no',
            'message' => $rdv ? ' Action non authorisée pour ce profil.' : "Rendez-vous Inconnu.",
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
    }

    /**
     * Cree une conversdation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
    }

    /** @todo think about the use cases
     * Modifie une converesation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
    }

    /** @todo think about the use cases
     * Supprime une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
    }
}
