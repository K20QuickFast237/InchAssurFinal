<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;

class MotifsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des competences
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        $skills = model("MotifsModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($skills) ? count($skills) : 'Aucun') . ' motif(s) trouvé(s).',
            'data'    => $skills,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une competence
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
                'rules' => 'required|is_unique[motifs.nom]',
                'errors' => ['required' => 'Le nom du motif est requis.', 'is_unique' => 'ce motif existe déjà.']
            ],
            'description' => [
                'rules' => 'required',
                'errors' => ['required' => 'La description du motif est requise.',]
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter ce motif.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $input = $this->getRequestInput($this->request);

        model("Motifsmodel")->insert(['nom' => $input['nom'], 'description' => $input['description']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Motif Ajouté.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Modifie une competence
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
            'nom' => 'if_exist',
            'description' => 'if_exist',
        ];

        $input = $this->getRequestInput($this->request);
        if (isset($input['nom'])) {
            $skill['nom'] = $input['nom'];
        }
        if (isset($input['description'])) {
            $skill['description'] = $input['description'];
        }
        model("Motifsmodel")->update($id, $skill);
        $response = [
            'statut'  => 'ok',
            'message' => 'Motif Modifié.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprime une competence
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        model("Motifsmodel")->delete($id);
        $response = [
            'statut'  => 'ok',
            'message' => 'Motif Supprimé.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie les skills associés au motif désigné.
     *
     * @param  int $motifID
     * @return ResponseInterface The HTTP response.
     */
    public function getSkills(int $motifID)
    {
        $skillIDs = model("SkillsMotifModel")->where('motif_id', (int)$motifID)->findColumn("skill_id");
        if ($skillIDs) {
            $skills = model("SkillsModel")->whereIn('id', $skillIDs)->findAll();
        } else {
            $skills = [];
        }
        $response = [
            'statut'  => 'ok',
            'message' => (count($skills) ? count($skills) : 'Aucun') . ' skill(s) associé(s) à ce motif.',
            'data'    => $skills,
        ];
        return $this->sendResponse($response);
    }
}
