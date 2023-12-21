<?php

namespace Modules\Assurances\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use Modules\Assurances\Entities\AssurancesEntity;

class AssurancesController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    public function index()
    {
        /** @todo penser à spécifier certains champs pour la liste vue que le listing doit être avec le strict nécessaire */
        $response = [
            'status' => 'ok',
            'message' => 'Assurances disponibles.',
            'data' => model("AssurancesModel")->findAll(),
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
                'status' => 'ok',
                'message' => "Détails de l'assurance.",
                'data' => $assurance,
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $response = [
                'status' => 'no',
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
                'status' => 'no',
                'message' => 'Assurance introuvable.',
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        $response = [
            'status' => 'ok',
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
            'images'           => [
                'rules'  => 'uploaded[images]',
                'errors' => ['uploaded' => 'Une(ou plusieurs) image est requise'],
            ],
        ];

        $input  = $this->getRequestInput($this->request);
        $images = $this->request->getFiles();

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }

            $input['assureur_id'] = 1; //The current user (assureur) identifier
            $input['code'] = random_string('alnum', 10);
            model("AssurancesModel")->db->transBegin();
            $input['idAssurance'] = model("AssurancesModel")->insert(new AssurancesEntity($input));

            foreach ($images['images'] as $key => $img) {
                $imgID = saveImage($img, 'uploads/assurances/images/');
                if ($key == 0) {
                    $input['image_id'] = $imgID;
                    model("AssuranceImagesModel")->insert(["assurance_id" => $input['idAssurance'], "image_id" => $imgID, "isDefault" => true]);
                } else {
                    model("AssuranceImagesModel")->insert(["assurance_id" => $input['idAssurance'], "image_id" => $imgID]);
                }
            }
            model("AssurancesModel")->db->transCommit();
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
            'data'    => $input,
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

        // try {
        $response = [
            'status'  => 'ok',
            'message' => "Services de l'assurance.",
            'data'    => $assurance->services,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'status'  => 'no',
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
                'status'  => 'ok',
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
     * Retrieve reductions of identified assurance
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getAssurReductions($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();

        // try {
        $response = [
            'status'  => 'ok',
            'message' => "Reductions de l'assurance.",
            'data'    => $assurance->reductions,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'status'  => 'no',
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
                'status'  => 'ok',
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
            'status'  => 'ok',
            'message' => $data ? "Questionnaire de l'assurance." : "Assurance ou questionnaire introuvable.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
        // } catch (\Throwable $th) {
        //     $response = [
        //         'status'  => 'no',
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
                'status'  => 'ok',
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

        $response = [
            'status'  => 'ok',
            'message' => "Options de paiement de l'assurance.",
            'data'    => $assurance->payOptions,
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
                'status'  => 'ok',
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

        $response = [
            'status'  => 'ok',
            'message' => "Documentation fournie par l'assurance.",
            'data'    => $assurance->documentation,
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
                'status'  => 'ok',
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

        $response = [
            'status'  => 'ok',
            'message' => "Documentation fournie par l'assurance.",
            'data'    => $assurance->images,
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
            'status'  => 'ok',
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
            'status'  => 'ok',
            'message' => "Image par défaut mise à jour.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }
}
