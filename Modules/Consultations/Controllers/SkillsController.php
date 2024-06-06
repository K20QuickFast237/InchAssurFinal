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
     * Retourne la liste des competences du médecin spécifié
     *
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function index($medIdentity = null)
    {
        if ($medIdentity) {
            $identifier = $this->getIdentifier($medIdentity, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
            $medName = "ce médecin";
        } else {
            $med = $this->request->utilisateur;
            $medName = "vous";
        }

        $medSkillModel = model("MedecinSkillsModel");
        $skills = $medSkillModel->select('skill_id as idSkill, description_perso, cout, isExpert, cout_expert as coutExpert, skills.nom, skills.description')
            ->join('skills', 'skill_id = skills.id', 'left')
            ->where('medecin_id', $med->id)
            ->findAll();

        $skills = array_map(function ($s) {
            if (isset($s['description_perso'])) {
                $s['description'] = $s['description_perso'];
            }
            unset($s['description_perso']);
            $s['idSkill']  = (int)$s['idSkill'];
            $s['cout']     = (float)$s['cout'];
            $s['isExpert'] = (bool)$s['isExpert'];
            return $s;
        }, $skills);

        $response = [
            'statut'  => 'ok',
            'message' => $skills ? count($skills) . ' skill(s) trouvé(s).' : "Aucun skill trouvé pour $medName.",
            'data'    => $skills,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a skill
     *
     * @param  int $id - the specified skill Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $data = model("SkillsModel")->where('id', $id)->first();
            $response = [
                'statut'  => 'ok',
                'message' => 'Détails du skill.',
                'data'    => $data ?? throw new \Exception('Skill introuvable.'),
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Skill introuvable.',
                'data'    => [],
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function showAll()
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
     * Associe un ou plusieurs motifs à la compétence
     * 
     * @param  int $id the skill identifier
     * @return ResponseInterface The HTTP response.
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

    /**
     * Défini les compétences d'un médecin
     *
     * @param  int|string $medIdentity
     * @return ResponseInterface The HTTP response.
     */
    public function setMedSkill($medIdentity = null)
    {
        if ($medIdentity) {
            if (!auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($medIdentity, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            if (!auth()->user()->inGroup('medecin')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $med = $this->request->utilisateur;
        }

        $rules = [
            'idSkill' => [
                'rules' => 'required|is_not_unique[skills.id]',
                'errors' => ['required' => 'Identification du skill requis.', 'is_not_unique' => 'ce skill est inconnu.']
            ],
            "description" => "if_exist",
            "cout" => [
                'rules' => 'required|numeric',
                'errors' => ['required' => 'Le coût est requis.', 'numeric' => 'La valeur du coût est incorrect.']
            ],
            "isExpert" => [
                'rules' => 'if_exist|integer|in_list[0,1]',
                'errors' => ['in_list' => 'valeur d\'expertise inappropriée.']
            ],
            "coutExpert" => [
                'rules' => 'if_exist|numeric',
                'errors' => ['numeric' => 'La valeur du coût d\'expertise est incorrecte.']
            ],
        ];

        $input = $this->getRequestInput($this->request);

        $model = model("MedecinSkillsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }

            $skillInfos = [
                "medecin_id" => $med->id,
                "skill_id" => $input['idSkill'],
                "description_perso" => $input['description'] ?? null,
                "cout" => $input['cout'],
                "isExpert" => $input['isExpert'] ?? false,
                "cout_expert" => $input['coutExpert'] ?? null,
            ];

            $model->insert($skillInfos);

            $response = [
                'statut'  => 'ok',
                'message' => "Skill Défini pour le médecin.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce skill au médecin.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    public function updateMedSkill(int $skillID, $medIdentity = null)
    {
        if ($medIdentity) {
            if (!auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($medIdentity, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            if (!auth()->user()->inGroup('medecin')) {
                $response = [
                    'statut'  => 'no',
                    'message' => ' Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $med = $this->request->utilisateur;
        }

        $rules = [
            "description" => "if_exist",
            "cout" => [
                'rules' => 'if_exist|numeric',
                'errors' => ['numeric' => 'La valeur du coût est incorrect.']
            ],
            "isExpert" => 'if_exist|permit_empty',
            "coutExpert" => [
                'rules' => 'if_exist|numeric',
                'errors' => ['numeric' => 'La valeur du coût d\'expertise est incorrecte.']
            ],
        ];

        $input = $this->getRequestInput($this->request);

        $model = model("MedecinSkillsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }

            $model->where('medecin_id', $med->id)
                ->where('skill_id', $skillID);
            isset($input['coutExpert']) ? $model->set("cout_expert", $input['coutExpert']) : null;
            isset($input['description']) ? $model->set("description_perso", $input['description']) : null;
            isset($input['cout']) ? $model->set("cout", $input['cout']) : null;
            isset($input['isExpert']) ? $model->set("isExpert", (bool)$input['isExpert']) : null;

            $model->update();
            $response = [
                'statut'  => 'ok',
                'message' => "Skill mis à jour pour le médecin.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible de mettre à jour ce skill du médecin.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $response = [
            'statut'  => 'no',
            'message' => "Aucune information à modifier.",
            'data'    => [],
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_NOT_MODIFIED);
    }

    /**
     * Renvoie la liste des medecins experts pour la compétence identifiée
     *
     * @param  int $skillID
     * @return ResponseInterface The HTTP response.
     */
    public function getMedExperts(int $skillID)
    {
        $expertIds = model("MedecinSkillsModel")
            ->where('skill_id', $skillID)
            ->where('isExpert', true)
            ->findColumn('medecin_id');
        $experts = $expertIds ? model('UtilisateursModel')->getBulkSimplifiedArray($expertIds) : false;
        $response = [
            'statut'  => 'ok',
            'message' => $experts ? count($experts) . ' medecin(s) trouvé(s).' : "Aucun medecin trouvé pour cette compétence.",
            'data'    => $experts,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie la liste des medecins ayant la compétence identifiée
     *
     * @param  int $skillID
     * @return ResponseInterface The HTTP response.
     */
    public function getMeds(int $skillID)
    {
        $medInfos = model("MedecinSkillsModel")->select('medecin_id, isExpert')
            ->where('skill_id', $skillID)
            ->findAll();

        $medInfos = array_combine(array_column($medInfos, 'medecin_id'), array_column($medInfos, 'isExpert'));

        $meds = $medInfos ? model('UtilisateursModel')->getBulkSimplifiedArray(array_keys($medInfos)) : false;
        $meds = array_map(function ($med) use ($medInfos) {
            $med['isExpert'] = (bool)$medInfos[$med['idUtilisateur']];
            return $med;
        }, $meds);
        $response = [
            'statut'  => 'ok',
            'message' => $meds ? count($meds) . ' medecin(s) trouvé(s).' : "Aucun medecin trouvé pour cette compétence.",
            'data'    => $meds,
        ];
        return $this->sendResponse($response);
    }
}
