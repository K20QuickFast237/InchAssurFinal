<?php

namespace  Modules\Assurances\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Assurances\Entities\QuestionOptionsEntity;
use Modules\Assurances\Entities\QuestionsEntity;

class QuestionsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;


    /**
     * Retrieve all questions of product's questionnaries.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $list      = model("QuestionsModel")->findAll();
        $optionIDs = array_reduce($list, fn ($curr, $elmt) => array_merge($curr, $elmt->options), []);
        $optionIDs = array_unique($optionIDs);

        $options = model("QuestionOptionsModel")->whereIn("id", $optionIDs)->findAll();
        foreach ($list as $question) {
            $result = array_map(function ($optID) use ($options) {
                $result = array_filter($options, function ($option) use ($optID) {
                    return $option->idOption == $optID;
                });
                return array_values($result)[0];
            }, $question->options);
            $question->options = $result;
        }

        $response = [
            'status' => 'ok',
            'message' => 'Questions disponibles.',
            'data' => $list,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the details of a question
     *
     * @param  int $id - the specified question Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        /*
            Récupérer les infos de la question,
            Vérifier ses options:
                - si sous questions présentes, récupérer les sousquestions 
                    et relancer l'extraction de sousquestions sur chacune d'elles
            Retourner le résultat
        */
        // $details = model("QuestionsModel")->where('id_question', $id)->first();
        $details = model("QuestionsModel")->getBulkQuestionDetails([$id]);
        $response = [
            'status' => 'ok',
            'message' => 'Détails de la question.',
            // 'data' => array_merge($details->toArray(), ['options' => $details->optionsDetails]),
            'data' => $details[0] ?? throw new \Exception("Question introuvable"),
        ];
        return $this->sendResponse($response);
        // try {
        // } catch (\Throwable $th) {
        //     $response = [
        //         'status' => 'no',
        //         'message' => 'Question introuvable.',
        //         'data' => [],
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        // }
    }

    /** @todo gérer le cas d'une option de type fichier
     * Creates a new questionary record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        /*
            input form format
            form = [
                {"question" : "tranche d'age",                      //name
                "fieldType": "select",                              //type
                "tarifType": "Valeur",    // "pourcentage",
                "isRequired": 1,        
                "options"  : [
                                {
                                    "label": "moins de 40 ans",
                                    "prix" : 15000,
                                    "format": "valeur en cas de fichier ('image'/'audio'/'document'/'video')"
                                    "subquestions": un tableau d'identifiants de questions
                                },
                                {
                                    "label": "plus de 40 ans",
                                    "prix" : 25000,
                                    "format": "valeur en cas de fichier ('image'/'audio'/'document'/'video')"
                                    "subquestions": un tableau d'identifiants de questions
                                },
                            ]
            ]
        */
        $rules = [
            'libelle'   => [
                'rules'  => 'required|is_unique[questions.libelle]',
                'errors' => [
                    'required'  => "La valeur de {field} est requise.",
                    'is_unique' => "Une {field} identique existe déjà."
                ]
            ],
            'fieldType'  => [
                'rules'  => 'required|alpha_numeric_punct',
                'errors' => [
                    'required' => "La valeur de {field} est requise.",
                    'alpha_numeric_punct' => "La valeur de {field} ne doit pas contenir de caractères spéciaux.",
                ]
            ],
            'tarifType'  => [
                'rules'  => 'required|alpha_numeric_punct',
                'errors' => [
                    'required' => "La valeur de {field} est requise.",
                    'alpha_numeric_punct' => "La valeur de {field} ne doit pas contenir de caractères spéciaux.",
                ]
            ],
            'isRequired' => [
                'rules'  => 'required',
                'errors' => ['required' => "La valeur de {field} est requise.",]
            ],
            'description' => 'if_exist',
            "options.*.prix" => [
                'rules'  => 'required|numeric',
                'errors' => [
                    'numeric'  => 'Valeur de {field} inappropriée.'
                ]
            ],
            "options.*.label" => [
                'rules'  => 'if_exist',
            ],
            "options.*.format" => [
                'rules'  => 'if_exist',
            ],
            "options.*.subquestions" => [
                'rules'  => 'if_exist',
            ],
        ];
        $input = $this->getRequestInput($this->request);
        /*
            Enregistrer les options de la question:
                - Si les sousquestions existent, vérifier que cest éléments existent en BD et les enregistrer.
            Regrouper les identifiants des options enregistrées dans un tableau d'options.
            Ajouter l'auteur.
            Enregistrer la question avec le champ options contenant le tableau précédent.
        */
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }

            $subquestionIDs = array_reduce($input['options'], fn ($curr, $elmt) => array_merge($curr, $elmt['subquestions'] ?? []), []);
            $subquestionIDs = array_unique($subquestionIDs);

            $foundedquestIDs = model("QuestionsModel")->whereIn('id', $subquestionIDs)->findColumn('id');

            // Si les sousquestions existent, vérifier que cest éléments existent en BD et les enregistrer.
            if (count($foundedquestIDs) != count($subquestionIDs)) {
                $notFoundedQuestions = array_reduce($subquestionIDs, function ($curr, $elmt) use ($foundedquestIDs) {
                    if (array_search($elmt, $foundedquestIDs) === false) {
                        return $curr . " $elmt,";
                    }
                }, "");

                strlen($notFoundedQuestions) === 3 ? throw new \Exception("La question$notFoundedQuestions est introuvable.", 1) : null;
                strlen($notFoundedQuestions) > 3 ? throw new \Exception("Les questions$notFoundedQuestions sont introuvables.", 1) : null;
            }
            $optionIDs = [];
            $result = [];
            // Enregistrer les options de la question:
            foreach ($input['options'] as $option) {
                $id = model("QuestionOptionsModel")->insert(
                    new QuestionOptionsEntity(array_filter($option, fn ($elmt) => $elmt !== null))
                );
                $optionIDs[] = $id;
                $result['options'][] = array_merge($option, ['idOption' => $id]);
            }
            // Regrouper les identifiants des options enregistrées dans un tableau d'options.
            $input['options'] = $optionIDs;

            /** @todo Ajouter l'auteur.*/
            $input['auteur_id'] = 1;

            // Enregistrer la question avec le champ options contenant le tableau précédent.
            $input['idQuestion'] = model("QuestionsModel")->insert(new QuestionsEntity($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'enregistrer la question.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut' => 'ok',
            'message' => "Question enregistrée.",
            'data' => array_merge($input, $result),
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Update a question, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'libelle'    => 'if_exist',
            'fieldType'  => [
                'rules'  => 'if_exist|alpha_numeric_punct',
                'errors' => ['alpha_numeric_punct' => "La valeur de {field} ne doit pas contenir de caractères spéciaux.",]
            ],
            'tarifType'  => [
                'rules'  => 'if_exist|alpha_numeric_punct',
                'errors' => ['alpha_numeric_punct' => "La valeur de {field} ne doit pas contenir de caractères spéciaux.",]
            ],
            'isRequired' => 'if_exist',
            'description' => 'if_exist',
            "options.*.prix" => [
                'rules'  => 'if_exist|numeric',
                'errors' => ['numeric'  => 'Valeur de {field} inappropriée.']
            ],
            "options.*.label"  => 'if_exist',
            "options.*.format" => 'if_exist',
            "options.*.subquestions" => 'if_exist',
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }

            if (isset($input['options'])) {
                $options = $input['options'];
                foreach ($options as $option) {
                    if (isset($option['subquestions']) && is_array($subQuest = $option['subquestions'])) {
                        throw new \Exception("Les sousquestions d'une options ne peuvent être qu'une liste d'identifiants de questions.");
                    }
                    foreach ($subQuest as $idQuest) {
                        if (!is_int($idQuest)) {
                            throw new \Exception("Les sousquestions d'une options ne peuvent être qu'une liste d'identifiants de questions.");
                        }
                    }
                }
            }

            model("QuestionsModel")->update($id, new QuestionsEntity($input));
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette question.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Question mise à jour.',
            'data'          => $input,
        ];
        return $this->sendResponse($response);
    }

    /** @todo penser à faire gérer ceci par les utilisateurs ayant ce droit uniquement.
     * Delete the question record in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            $options = model("QuestionsModel")->find($id)->options;
            model("QuestionOptionsModel")->delete($options);
            model("QuestionsModel")->delete($id);
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Identifiant de Question inexistant.',
                'errors' => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => 'Question Supprimée.',
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieves the available tarif types.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function getTarifTypes()
    {
        $questionsEntity = new QuestionsEntity();
        $response = [
            'status' => 'ok',
            'message' => 'Types de tarifications disponibles.',
            'data' => $questionsEntity->tarifTypeList,
        ];
        return $this->sendResponse($response);
    }
}
