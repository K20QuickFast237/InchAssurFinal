<?php

namespace Modules\Consultations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
// use CodeIgniter\CodeIgniter;

class AvisExpertsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;


    /**
     * Retourne la liste des consultations d'un utilisateur
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

        $avis = model("AvisExpertModel")
            ->where("medecin_sender_id", $utilisateur->id)
            ->orwhere("medecin_receiver_id", $utilisateur->id)
            ->groupBy('medecin_sender_id', 'desc')
            ->orderBy('dateCreation', 'desc')
            ->findAll();

        $response = [
            'statut'  => 'ok',
            'message' => (count($avis) ? count($avis) : 'Aucun') . ' avis trouvé(s).',
            'data'    => $avis,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retourne la liste de tous les avis d'expert enregistrés
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $avis = model("AvisExpertModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => (count($avis) ? count($avis) : 'Aucun') . ' avis trouvé(s).',
            'data'    => $avis,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retourne les détails de l'avis d'expert spécifié
     *
     * @param  int $id
     * @return ResponseInterface The HTTP response.
     */
    public function show(int $id)
    {
        $avis = model("AvisExpertModel")->where('id', $id)->first();
        $avis->documents;
        $avis->attachements;
        $response = [
            'statut'  => 'ok',
            'message' => "Détails de l'avis.",
            'data'    => $avis,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie un avis afin d'y apporter bilan et documents
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($identifier)
    {
        $rules = [
            'heure' => [
                'rules'  => 'if_exist|valid_date[H:i]',
                'errors' => ['valid_date' => 'Format d\'heure attendu hh:mm.']
            ],
            'date'  => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'Format de date attendu YYYY-MM-DD.']
            ],
            'objet' => 'if_exist',
            'bilan' => 'if_exist',
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour ce Rendez-vous.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        $identifier = $this->getIdentifier($identifier);
        $consult = model('ConsultationsModel')->where($identifier['name'], $identifier['value'])->first();
        if (isset($input['bilan'])) {
            if ($this->request->utilisateur->id != $consult->medecin_user_id['idUtilisateur']) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Cette modification ne vous est pas authorisée.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
        }
        if (isset($input['heure']) || isset($input['date'])) {
            // Vérifier que les valeurs choisies sont disponibles dans l'agenda du médecin
            $date  = $input['date'] ?? $consult->date;
            $heure = $input['heure'] ?? $consult->heure;
            $newAgenda = model("AgendasModel")->where('proprietaire_id', $consult->medecin_user_id['idUtilisateur'])
                ->where('jour_dispo', $date)
                ->where('heure_dispo_debut <=', $heure)
                ->where('heure_dispo_fin >=', $heure)
                ->first();
            $dispo = $newAgenda
                ? array_filter($newAgenda->slots, function ($sl) use ($heure) {
                    return (strtotime($sl['debut']) <= strtotime($heure)) && (strtotime($heure) < strtotime($sl['fin']));
                })
                : false;
            $dispo = reset($dispo);
            if (!$dispo) {
                $response = [
                    'statut'  => 'no',
                    'message' => "Aucune disponibilité dans l'agenda du médecin pour la date/heure choisie.",
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_NOT_FOUND);
            }

            $oldAgenda = model("AgendasModel")->where('proprietaire_id', $consult->medecin_user_id['idUtilisateur'])
                ->where('jour_dispo', $consult->date)
                ->where('heure_dispo_debut <=', $consult->heure)
                ->where('heure_dispo_fin >=', $consult->heure)
                ->first();
            $heure = $consult->heure;
            $oldDispo = $newAgenda
                ? array_filter($oldAgenda->slots, function ($sl) use ($heure) {
                    return (strtotime($sl['debut']) <= strtotime($heure)) && (strtotime($heure) < strtotime($sl['fin']));
                })
                : false;
            $oldDispo = reset($oldDispo);

            // modifier l'agenda du médecin
            $newAgenda->unsetSlot($dispo['id']);
            $oldAgenda->setSlot($oldDispo['id']);
            model("ConsultationsModel")->db->transBegin();
            model("AgendasModel")->where('id', $newAgenda->id)->set('slots', $newAgenda->slots)->update();
            model("AgendasModel")->where('id', $oldAgenda->id)->set('slots', $oldAgenda->slots)->update();
        }
        // $cons = model("ConsultationsModel")->where($identifier['name'],  $identifier['value']);
        $cons = model("ConsultationsModel")->where('id', $consult->id);
        foreach ($input as $key => $value) {
            $cons->set($key, $value);
        }
        $cons->update();
        model("ConsultationsModel")->db->transCommit();

        $response = [
            'statut'  => 'ok',
            'message' => 'Consultation mise à jour.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime un document de l'avis expert
     *
     * @param  int|string $avisIdent
     * @param  int $docId
     * @return ResponseInterface The HTTP response.
     */
    public function delateDoc(int|string $avisIdent, $docId)
    {
        $identifier = $this->getIdentifier($avisIdent);
        try {
            $avis = model('AvisExpertModel')->where($identifier['name'], $identifier['value'])->first();
            // ne peut etre supprimé que par le médecin faisant la demande
            if ($this->request->utilisateur->id != $avis->medecin_receiver_id['idUtilisateur']) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Cette modification ne vous est pas authorisée.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            model('AvisDocumentsModel')->where('document_id', $docId)->where('avis_id', $avis->id)->delete();
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Document ou Avis introuvable.',
            ];
            return $this->sendResponse($response);
        }
        $response = [
            'statut'  => 'ok',
            'message' => 'Document supprimé.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime une pièce jointe de l'avis expert
     *
     * @param  int|string $avisIdent
     * @param  int $docId
     * @return ResponseInterface The HTTP response.
     */
    public function delateAttach(int|string $avisIdent, $docId)
    {
        $identifier = $this->getIdentifier($avisIdent);
        try {
            $avis = model('AvisExpertModel')->where($identifier['name'], $identifier['value'])->first();
            // ne peut etre supprimé que par le médecin faisant la demande
            if ($this->request->utilisateur->id != $avis->medecin_sender_id['idUtilisateur']) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Cette modification ne vous est pas authorisée.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            model('AvisAttachementsModel')->where('document_id', $docId)->where('avis_id', $avis->id)->delete();
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'pièce jointe ou Avis introuvable.',
            ];
            return $this->sendResponse($response);
        }
        $response = [
            'statut'  => 'ok',
            'message' => 'pièce jointe supprimée.',
        ];
        return $this->sendResponse($response);
    }
}
