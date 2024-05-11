<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;

class SkillsController extends BaseController
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
        $skills = model("SkillsModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($skills) ? count($skills) : 'Aucun') . ' skill(s) trouvé(s).',
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
                'rules' => 'required|is_unique[skills.nom]',
                'errors' => ['required' => 'Le nom de la compétence est requis.', 'is_unique' => 'ce skill existe déjà.']
            ],
            'description' => [
                'rules' => 'required',
                'errors' => ['required' => 'La description de la compétence est requise.',]
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

        model("Skillsmodel")->insert(['nom' => $input['nom'], 'description' => $input['description']]);
        $response = [
            'statut'  => 'ok',
            'message' => 'Skill Ajouté.',
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
        model("Skillsmodel")->update($id, $skill);
        $response = [
            'statut'  => 'ok',
            'message' => 'Skill Modifié.',
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
        model("Skillsmodel")->delete($id);
        $response = [
            'statut'  => 'ok',
            'message' => 'Skill Supprimé.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associe un motif
     */
    public function setMotifs(int $id)
    {
        $rules = [
            'motifs'   => 'required',
            'motifs.*' => 'integer|is_not_unique[motifs.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("SkillsMotifModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            // $model->where("skill_id", (int)$id)->delete();
            foreach ($input['motifs'] as $idMotif) {
                $model->insert(["skill_id" => (int)$id, "motif_id" => (int)$idMotif]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Motif(s) associé(s) au skill.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce(s) motif(s) au skill.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Renvoie les motifs associés au skill désigné.
     *
     * @param  int $skillID
     * @return ResponseInterface The HTTP response.
     */
    public function getMotifs(int $skillID)
    {
        $motifIDs = model("SkillsMotifModel")->where('skill_id', (int)$skillID)->findColumn("motif_id");
        if ($motifIDs) {
            $motifs = model("MotifsModel")->whereIn('id', $motifIDs)->findAll();
        } else {
            $motifs = [];
        }

        $response = [
            'statut'  => 'ok',
            'message' => (count($motifs) ? count($motifs) : 'Aucun') . ' motif(s) associé(s) à ce skill.',
            'data'    => $motifs,
        ];
        return $this->sendResponse($response);
    }

    /**
     * supprime un motif associé à la cmpétence
     *
     * @param  int $idSkill
     * @param  int $idMotif
     * @return ResponseInterface The HTTP response.
     */
    public function delMotifs(int $idSkill, int $idMotif)
    {
        model("SkillsMotifModel")->where("skill_id", $idSkill)->where("motif_id", $idMotif)->delete();
        $response = [
            'statut'  => 'ok',
            'message' => 'Association du skill au motif Supprimée.',
        ];
        return $this->sendResponse($response);
    }
}
