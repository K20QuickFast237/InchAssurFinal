<?php

namespace Modules\Consultations\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\Database\Exceptions\DataException;

class MotifsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des motifs
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
        $motifs = model("MotifsModel")->findAll() ?? [];

        $response = [
            'statut'  => 'ok',
            'message' => (count($motifs) ? count($motifs) : 'Aucun') . ' motif(s) trouvé(s).',
            'data'    => $motifs,
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


    /**
     * Renvoie la liste des motifs de consultation d'un médecin,
     * en fonction de ses skills où des motifs par lui défini
     * 
     * @param  int|string $codeMedecin
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function index($medIdentity = null)
    {
        /*
            récuperer l'id du médecin,
            lister ses motifs,
            retourner le résultat.
        */
        if ($medIdentity) {
            $identifier = $this->getIdentifier($medIdentity, 'id');
            $med = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
            $medName = "ce médecin";
        } else {
            $med = $this->request->utilisateur;
            $medName = "vous";
        }

        $medId = $med->id;

        $data = $this->getMedMotifs([$medId])[0] ?? null;

        $response = [
            'statut' => 'ok',
            'message' => $data ? 'motif(s) trouvé(s).' : "Aucun motif trouvé pour $medName.",
            'data'   => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie la liste des medecins pouvant consulter pour le motif identifié
     *
     * @param  int $motifId
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getMeds(int $motifId)
    {
        /*
            Récupérer tous les skills couvrant le motif
            Récupérer les medecins ayant ce skills (préciser si expertise)
            Récupérer les autres données nédessaires
        */
        try {
            // récuperation des skills
            $skillIds = model("SkillsMotifModel")->where("motif_id", $motifId)->findColumn("skill_id");
            $skills = model("SkillsModel")->join("medecin_skills", "skills.id = skill_id", "right")
                ->whereIn("skills.id", $skillIds)
                ->findAll();

            $medIds = array_column($skills, "medecin_id");

            // récupérer les localisations et canaux pour ces medecins
            $locations = model("LocalisationsModel")->join("medecin_localisations", "localisations.id = localisation_id", "left")
                ->select("etablissement, adresse, ville, medecin_id, isDefault, localisation_id")
                ->whereIn("medecin_id", $medIds)
                ->asArray()
                ->findAll();
            $canaux = model("CanauxModel")->join("medecin_canaux", "canaux.id = canal_id", "left")
                ->whereIn("medecin_id", $medIds)
                ->asArray()
                ->findAll();

            // récupérer les identités des médecins
            $meds = $medIds ? model('UtilisateursModel')->getBulkSimplifiedArray($medIds) : false;
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'ok',
                'message' => "Aucun medecin trouvé.",
            ];
            return $this->sendResponse($response);
        }
        // rassembler les informations et répondre
        for ($i = 0; $i < count($meds); $i++) {
            $sks = array_values(array_filter($skills, fn ($s) => $s["medecin_id"] == $meds[$i]['idUtilisateur']));
            $meds[$i]['skills'] = array_map(fn ($sk) => [
                "idSkill" => $sk["id"],
                "nom" => $sk["nom"],
                "description" => $sk["description_perso"] ? $sk["description_perso"] : $sk["description"],
                "cout" => $sk["cout"],
                "isExpert" => (bool)$sk["isExpert"],
                "cout_expert" => $sk["isExpert"] ? $sk["cout_expert"] : null,
            ], $sks);
            $locats = array_values(array_filter($locations, fn ($l) => $l["medecin_id"] == $meds[$i]['idUtilisateur']));
            $meds[$i]['localisation'] = array_map(fn ($loc) =>
            [
                "idLocalisation" => $loc['localisation_id'],
                "etablissement"  => $loc['etablissement'],
                "adresse"        => $loc['adresse'],
                "ville"          => $loc['ville'],
                "isDefault"      => (bool)$loc['isDefault'],
            ], $locats);
            $cans = array_values(array_filter($canaux, fn ($c) => $c["medecin_id"] == $meds[$i]['idUtilisateur']));
            $meds[$i]['canaux'] = array_map(fn ($can) => ["idCanal" => $can['id'], "nom" => $can['nom']], $cans);
        }
        $response = [
            'statut'  => 'ok',
            'message' => $meds ? count($meds) . ' medecin(s) trouvé(s).' : "Aucun medecin trouvé pour ce motif.",
            'data'    => $meds,
        ];
        return $this->sendResponse($response);
    }

    /**
     * getmedMotifs
     *
     * Renvoie la liste des motifs de consultation des médecins,
     * en fonction de leurs skills où des motifs par eux défini.
     * Conserve l'ordre des ids de medecin fourni.
     * 
     * @param  array $medIds
     * @return array
     */
    private function getMedMotifs(array $medIds)
    {
        /*
            À partir de la liste d'id de medecins,
            récupérer la liste des skills et motifs associés,
            pour chaque skill, 
            Si la liste des motifs est disponible, la renvoyer,
            si non, récupérer les motifs par défaut associés au skill
        */
        $medSkillModel = model("MedecinSkillsModel");
        $data = $medSkillModel->select('skill_id, nom, motifs, medecin_id as medID')
            ->join('skills', 'skill_id = skills.id', 'left')
            ->whereIn('medecin_id', $medIds)
            ->findAll();

        if (!$data) {
            return [];
        }
        // skills sans motif défini
        // $noMotifsData  = array_filter($data, fn($val, $key) => (!$val['motifs']), ARRAY_FILTER_USE_BOTH);

        // liste des ids de skills n'ayant pas de motif associé
        $noMotifSkills = array_map( // remplacer en array_column
            fn ($val) => $val['skill_id'],
            // $noMotifsData
            array_filter($data, fn ($val, $key) => (!$val['motifs']), ARRAY_FILTER_USE_BOTH)
        );

        //récupération des motifs de ces skills
        $skillMotifModel = model("SkillsMotifModel");
        $motifs = $skillMotifModel->findBulkMotifs($noMotifSkills);

        //association des skills aux motifs
        $motifiedSkills = [];


        foreach ($noMotifSkills as $skillId) {
            $motifiedSkills[$skillId] = array_values(array_map(
                fn ($val) => [
                    'id'          => $val['id'],
                    'nom'         => $val['nom'],
                    'description' => $val['description']
                ],
                array_filter($motifs, fn ($v, $k) => $v['skill_id'] === $skillId, ARRAY_FILTER_USE_BOTH)
            ));
        }

        //association des motifs aux données
        $getList   = fn () => $motifiedSkills;
        $arguments = array_fill(0, count($data), $getList);
        $result = [];
        foreach ($medIds as $elment) {
            $result[] = array_map(
                fn ($val, $suplData) => [
                    $val['nom'] => [
                        'motifs' => $suplData()[$val['skill_id']]
                    ]
                ],
                array_filter($data, fn ($v, $k) => (int)$v['medID'] === $elment, ARRAY_FILTER_USE_BOTH),
                $arguments
            );
        }

        return $result;
    }
}
