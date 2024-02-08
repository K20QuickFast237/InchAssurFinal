<?php

namespace Modules\Assurances\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Assurances\Entities\AssurancesEntity;
use Modules\Produits\Entities\ProduitsEntity;

class AssurancesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    public function index($identifier = null)
    {
        if ($identifier) {
            $identifier = $this->getIdentifier($identifier, 'id');
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }
        /** @todo penser à spécifier certains champs pour la liste vue que le listing doit être avec le strict nécessaire */
        $data = model("AssurancesModel")->where("assureur_id", $utilisateur->id)
            ->where("etat", ProduitsEntity::ACTIF)
            ->findAll();
        $response = [
            'statut' => 'ok',
            'message' => $data ? 'Assurances disponibles.' : "Aucune assurance disponible.",
            'data' => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    public function allInsurances()
    {
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        /** @todo penser à spécifier certains champs pour la liste vue que le listing doit être avec le strict nécessaire */
        $data = model("AssurancesModel")->findAll();
        $response = [
            'statut' => 'ok',
            'message' => $data ? 'Assurances disponibles.' : "Aucune assurance disponible.",
            'data' => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of an assurance
     *
     * @param  int $id - the assurance Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        try {
            $identifier = $this->getIdentifier($id, 'id');
            $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
            $response = [
                'statut' => 'ok',
                'message' => "Détails de l'assurance.",
                'data' => $assurance,
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Assurance introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
    }

    /**
     * Retrieve in deep the details of an assurance
     *
     * @param  int $id - the assurance Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurInfos($id = null)
    {
        try {
            $identifier = $this->getIdentifier($id, 'id');
            $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
            $assurance->questionnaire;
            $assurance->reductions;
            $assurance->services;
            $assurance->documentation;
            $assurance->payOptions;
            $assurance->images;
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Assurance introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        $response = [
            'statut' => 'ok',
            'message' => "Détails de l'assurance.",
            'data' => $assurance,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Creates a new assurance record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        if (!auth()->user()->can('assurances.create')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $rules = [
            'nom'              => [
                'rules'  => 'required|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]|min_length[3]|is_unique[assurances.nom]',
                'errors' => [
                    'min_length'  => 'le nom est trop court',
                    'required'    => 'Le nom est requis',
                    'regex_match' => 'Le nom ne peut contenir que des lettres, chiffres, espaces et les ponctuations simples.',
                    'is_unique'   => 'une assurance ayant ce nom existe déjà',
                ],
            ],
            'prix'             => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'required' => 'Le prix est requis',
                    'numeric'  => 'Le prix doit être un chiffre',
                ],
            ],
            'description'      => [
                'rules'   => 'required|min_length[3]|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]',
                'errors'  => [
                    'regex_match' => 'La description ne peut contenir que les lettes, chiffres, espaces et les ponctuations simples.',
                    'min_length'  => 'La description est trop courte.',
                    'required'    => 'La description est requise.'
                ],
            ],
            'shortDescription' => [
                'rules'   => 'required|min_length[3]|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]',
                'errors'  => [
                    'regex_match' => 'La description ne peut contenir que les lettes, chiffres, espaces et les ponctuations simples.',
                    'min_length'  => 'La description est trop courte.',
                    'required'    => 'La courte description est requise.'
                ],
            ],
            'duree'            => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'numeric'  => 'Valeur de {field} inappropriée.',
                    'required' => 'La valeur de {field} est requise'
                ],
            ],
            'categorie'        => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'La valeur de {field} est requise',
                    'numeric'  => 'Valeur de {field} inappropriée.'
                ],
            ],
            'type'             => [
                'rules' => 'required|numeric',
                'errors' => [
                    'required' => 'La valeur de {field} est requise',
                    'numeric'  => 'Valeur de {field} inappropriée.'
                ],
            ],
            'piecesAJoindre'    => 'if_exist',
            'piecesAJoindre.*'  => 'if_exist|string|is_not_unique[document_titres.nom]',
            'image'           => [
                'rules'  => 'uploaded[image]',
                'errors' => ['uploaded' => 'Une image est requise'],
            ],
        ];

        $input  = $this->getRequestInput($this->request);
        $images = $this->request->getFiles();
        $defaultImg = $this->request->getFile("image");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }

            $input['assureur_id'] = $this->request->utilisateur->id; //The current user (assureur) identifier
            $input['code'] = random_string('alnum', 10);
            model("AssurancesModel")->db->transBegin();
            $assurance = new AssurancesEntity($input);
            $assurance->image = saveImage($defaultImg, 'uploads/assurances/images/');
            // $input['idAssurance'] = model("AssurancesModel")->insert();
            $assurance->id = model("AssurancesModel")->insert($assurance);
            model("AssuranceCategoriesModel")->insert(["assurance_id" => $assurance->id, "categorie_id" => $input['categorie']]);

            model("AssuranceImagesModel")->insert(["assurance_id" => $assurance->id, "image_id" => $assurance->image]);
            if (isset($images['images'])) {
                foreach ($images['images'] as $key => $img) {
                    $imgID = saveImage($img, 'uploads/assurances/images/');
                    model("AssuranceImagesModel")->insert(["assurance_id" => $assurance->id, "image_id" => $imgID]);
                }
            }
            model("AssurancesModel")->db->transCommit();
            $assurance->image;
        } catch (\Throwable $th) {
            model("AssurancesModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'enregistrer cette Assurance.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Assurance enregistrée.",
            'data'    => $assurance,
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update an assurance, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'nom'              => [
                'rules'  => 'if_exist|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]|min_length[3]|is_unique[assurances.nom]',
                'errors' => [
                    'min_length'  => 'le nom est trop court',
                    'required'    => 'Le nom est requis',
                    'regex_match' => 'Le nom ne peut contenir que des lettres, chiffres, espaces et les ponctuations simples.',
                    'is_unique'   => 'une assurance ayant ce nom existe déjà',
                ],
            ],
            'prix'             => [
                'rules'  => 'if_exist|numeric',
                'errors' => [
                    'required' => 'Le prix est requis',
                    'numeric'  => 'Le prix doit être un chiffre',
                ],
            ],
            'description'      => [
                'rules'   => 'if_exist|min_length[3]|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]',
                'errors'  => [
                    'regex_match' => 'La description ne peut contenir que les lettes, chiffres, espaces et les ponctuations simples.',
                    'min_length'  => 'La description est trop courte.',
                    'required'    => 'La description est requise.'
                ],
            ],
            'shortDescription' => [
                'rules'   => 'if_exist|min_length[3]|regex_match[/[0-9a-z áàéèíóúùòç_\'-]/i]',
                'errors'  => [
                    'regex_match' => 'La description ne peut contenir que les lettes, chiffres, espaces et les ponctuations simples.',
                    'min_length'  => 'La description est trop courte.',
                    'required'    => 'La courte description est requise.'
                ],
            ],
            'duree'            => [
                'rules'  => 'if_exist|numeric',
                'errors' => [
                    'numeric'  => 'Valeur de {field} inappropriée.',
                    'required' => 'La valeur de {field} est requise'
                ],
            ],
            'categorie'        => [
                'rules' => 'if_exist|numeric',
                'errors' => [
                    'required' => 'La valeur de {field} est requise',
                    'numeric'  => 'Valeur de {field} inappropriée.'
                ],
            ],
            'type'             => [
                'rules' => 'if_exist|numeric',
                'errors' => [
                    'required' => 'La valeur de {field} est requise',
                    'numeric'  => 'Valeur de {field} inappropriée.'
                ],
            ],
            'piecesAJoindre'    => 'if_exist',
            'piecesAJoindre.*'  => 'if_exist|string|is_not_unique[document_titres.nom]',
        ];

        $input = $this->getRequestInput($this->request);
        $model = model("AssurancesModel");
        $assur = $model->find($id);
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if (isset($input['piecesAJoindre']) && is_string($input['piecesAJoindre'])) {
                $input['piecesAJoindre'] = json_decode($input['piecesAJoindre']);
            }
            $assur->fill($input);
            $model->update($id, $assur);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette Assurance.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Assurance mise à jour.',
            'data'          => $assur,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve services provided by identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurServices($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data = $assurance->services;

        // try {
        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Services de l'assurance." : "Aucun service disponible pour cette assurance.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'statut'  => 'no',
        //         'message' => 'Assurance introuvable.',
        //         'data'    => [],
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        // }
    }

    /**
     * Associate services to be provided by identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurServices($id)
    {
        $rules = [
            'services'   => 'required',
            'services.*' => 'integer|is_not_unique[services.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("AssuranceServicesModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            foreach ($input['services'] as $idService) {
                $model->insert(["assurance_id" => (int)$id, "service_id" => (int)$idService]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Service(s) associé(s) à l'assurance.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ces services.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Associate services to be provided by identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurCategories($id)
    {
        $user = auth()->user();
        $condition = model("AssurancesModel")->where('id', $id)->first();
        if (!$user->can('assurances.create')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        } elseif (!$condition || $condition->assureur_id != $this->request->utilisateur->id) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour cet utilisateur.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'categories'   => 'required',
            'categories.*' => [
                'rules'  => 'integer|is_not_unique[categorie_produits.id]',
                'errors' => [
                    'integer'       => 'Categorie non identifiable.',
                    'is_not_unique' => 'Categorie non reconnue.'
                ],
            ],
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model = model("AssuranceCategoriesModel");
            // $model->db->transBegin();
            foreach ($input['categories'] as $idCategorie) {
                try {
                    $model->insert(["assurance_id" => (int)$id, "categorie_id" => (int)$idCategorie]);
                } catch (\Throwable $th) {
                }
            }
            // $model->db->transCommit();
        } catch (\Throwable $th) {
            // $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ces services.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Assurance associée à la(aux) catégorie(s).",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    public function setAssurdefaultCategory($id)
    {
        $user = auth()->user();
        $condition = model("AssurancesModel")->where('id', $id)->first();
        if (!$user->can('assurances.create')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        } elseif (!$condition || $condition->assureur_id != $this->request->utilisateur->id) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour cet utilisateur.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $rules = [
            'categorie'  => [
                'rules'  => 'required|integer|is_not_unique[categorie_produits.id]',
                'errors' => [
                    'required'      => 'Categorie non identifiée.',
                    'integer'       => 'Categorie non identifiable.',
                    'is_not_unique' => 'Categorie non reconnue.'
                ],
            ],
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer cette catégorie.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $condition = model("AssuranceCategoriesModel")
            ->where("assurance_id", $id)
            ->where("categorie_id", $input['categorie'])
            ->first();

        if ($condition) {
            model("AssurancesModel")->update($id, ["categorie_id" => $input['categorie']]);
        } else {
            $response = [
                'statut'  => 'no',
                'message' => "Catégorie par défaut mise à jour",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Assurance(s) associé(s) à la catégorie.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve reductions of identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurReductions($id)
    {
        // $identifier = $this->getIdentifier($id, 'id');
        $identifier = $this->getIdentifier($id);
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $assurance->reductions;
        // try {
        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Reductions de l'assurance." : "Aucune réduction disponible pour cette assurance.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'statut'  => 'no',
        //         'message' => 'Aucun service trouvéAssurance introuvable.',
        //         'data'    => [],
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        // }
    }

    /**
     * Associate reductions to the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurReductions($id)
    {
        $rules = [
            'reductions'   => 'required',
            'reductions.*' => 'integer|is_not_unique[reductions.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("AssuranceReductionsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            foreach ($input['reductions'] as $idReduction) {
                $model->insert(["assurance_id" => (int)$id, "reduction_id" => (int)$idReduction]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Réduction(s) associée(s) à l'assurance.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ces reductions.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Retrieve the Quetionary of identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurQuestionnaire($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $assurance?->questionnaire;
        // try {
        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Questionnaire de l'assurance." : "Assurance ou questionnaire introuvable.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'statut'  => 'no',
        //         'message' => 'Aucun service trouvéAssurance introuvable.',
        //         'data'    => [],
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        // }
    }

    /**
     * Associate a Quetionary to the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurQuestionnaire($id)
    {
        $rules = [
            'questions'   => 'required',
            'questions.*' => 'integer|is_not_unique[questions.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("AssuranceQuestionsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            foreach ($input['questions'] as $idQuestion) {
                $model->insert(["assurance_id" => (int)$id, "question_id" => (int)$idQuestion]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Question(s) associée(s) à l'assurance.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ces questions.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Retrieve the Paiement Options of identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurPayOptions($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $assurance->payOptions;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Options de paiement de l'assurance." : "Aucune option de paiement pour cette assurance.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associate Paiement Options to the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurPayOptions($id)
    {
        $rules = [
            'payOptions'   => 'required',
            'payOptions.*' => 'integer|is_not_unique[paiement_options.id]',
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("AssurancePayOptionsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();

            foreach ($input['payOptions'] as $idPayOption) {
                $model->insert(["assurance_id" => (int)$id, "paiement_option_id" => (int)$idPayOption]);
            }
            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Option(s) de paiements associée(s) à l'assurance.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer cette(ces) option(s) de paiement.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Retrieve the Documentation provided by the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurDocumentation($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $assurance->documentation;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Documentation fournie par l'assurance." : "Aucune documentation disponible pour cette assurance.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associate Documents to the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurDocumentation($id)
    {
        $rules = [
            'titre'    => ['rules' => 'required|is_not_unique[document_titres.nom]', 'errors' => [
                'is_not_unique' => "Ce titre n'est pas reconnu"
            ]],
            'url'      => 'if_exist',
            'document' => 'if_exist|uploaded[document]',
            // 'documents'   => 'required',
            // 'documents.*' => 'integer|is_not_unique[documents.id]',
        ];
        $input    = $this->getRequestInput($this->request);
        $document = $this->request->getFile('document');

        $model = model("AssuranceDocumentsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->db->transBegin();
            if (isset($input['url'])) {
                $docID = model("DocumentsModel")->insert([
                    "titre" => $input['titre'],
                    "uri"   => $input['url'],
                    "isLink" => true,
                ]);
            } elseif ($document) {
                $docID = saveDocument($input['titre'], $document, 'uploads/assurances/documents/');
            }
            $model->insert(["assurance_id" => (int)$id, "document_id" => $docID]);

            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Documents associé(s) à l'assurance.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce(s) document(s) à l'assurance.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    /**
     * Retrieve the Documentation provided by the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurImages($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $assurance->images;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Images de l'assurance." : "Aucune image pour cette assurance.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associate image(s) to the identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurImages($id)
    {
        $rules = [
            'images' => [
                'rules'  => 'uploaded[images]',
                'errors' => ['uploaded' => 'Une(ou plusieurs) image est requise'],
            ],
        ];
        $images = $this->request->getFiles();
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            if ($images) {
                foreach ($images['images'] as $img) {
                    $imgID = saveImage($img, 'uploads/assurances/images/');
                    model("AssuranceImagesModel")->insert(["assurance_id" => $id, "image_id" => $imgID]);
                }
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'associer cette(ces) image(s) à l'assurance.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Image ajoutée à l'assurance.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Set the identified image as the default image for the identified assurance
     *
     * @param  int $id The ID of the assurance
     * @return ResponseInterface The HTTP response.
     */
    public function setAssurDefaultImg($id)
    {
        $rules = [
            'idImage' => [
                'rules' => 'required|integer|is_not_unique[assurance_images.image_id]',
                'errors' => [
                    'is_not_unique' => "Image inconnue pour cette assurance",
                    'integer'       => "La valeur de image est inappropriée",
                    'required'      => "Une information sur l'image est manquante",
                ]
            ],
        ];
        $input    = $this->getRequestInput($this->request);
        $model = model("AssuranceImagesModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $model->where('isDefault', true)->set(['isDefault' => false])->update();
            $model->where('image_id', $input['idImage'])->set(['isDefault' => true])->update();
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de modifier l'image par défaut.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Image par défaut mise à jour.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    public function getAssursOfCategory($id)
    {
        $assuranceIDs = model('AssuranceCategoriesModel')->where('categorie_id', $id)->findColumn('assurance_id');
        $assurances   = $assuranceIDs ? model('AssurancesModel')->whereIn('id', $assuranceIDs)->findAll() : [];
        $response = [
            'statut' => 'ok',
            'message' => $assurances ? 'Assurances de cette catégorie.' : "Aucune assurance pour cette catégorie.",
            'data' => $assurances,
        ];
        return $this->sendResponse($response);
    }
}
