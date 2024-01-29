<?php

namespace  Modules\Assurances\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Assurances\Entities\QuestionAnswersEntity;
use Modules\Assurances\Entities\QuestionsEntity;
use Modules\Assurances\Entities\SouscriptionsEntity;

class SouscriptionsController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['text'];

    /**
     * Retrieve all subscriptions records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $subscriptions = model("SouscriptionsModel")->findAll();
        $response = [
            'status' => 'ok',
            'message' => count($subscriptions) ? 'Souscriptions disponibles.' : 'Aucune souscription disponible.',
            'data' => $subscriptions ?? [],
        ];
        return $this->sendResponse($response);
    }

    /** @todo Ajouter les infos de paiement à récupérer dans les transactions
     * Retrieve the details of a subscription
     *
     * @param  int $id - the specified subscription Identifier
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        $identifier = $this->getIdentifier($id, 'id');
        // try {
        $data = model("SouscriptionsModel")->where($identifier['name'], $identifier['value'])->first();
        $data?->beneficiaires;
        $data?->documents;
        $data?->questionAnswers;
        // } catch (\Throwable $th) {
        //     $response = [
        //         'status' => 'no',
        //         'message' => 'Souscription introuvable.',
        //         'data' => [],
        //     ];
        //     return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        // }

        $response = [
            'status' => 'ok',
            'message' => $data ? 'Détails de la souscription.' : 'Souscription introuvable.',
            'data' => $data
        ];
        return $this->sendResponse($response);
    }

    /**
     * Creates a new subscription record in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        /*
            Le cout de la souscription est édité pendant l'ajout de réponses
            Le cout de la souscription est par défaut le prix de l'assurance
            La date de debut de la souscription est ajoutée après le premier paiement
            La date de fin de la souscription est ajoutée après le dernier paiement
        */
        $rules = [
            'cout'         => 'if_exist',
            'souscripteur' => [
                'rules'  => 'if_exist|integer|is_not_unique[utilisateurs.id]',
                'errors' => [
                    'integer' => "L'identifiant du souscripteur n'est pas reconnu",
                    'is_not_unique' => "L'identifiant du souscripteur n'est pas reconnu",
                ]
            ],
            'assurance'    => [
                'rules'  => 'required|integer|is_not_unique[assurances.id]',
                'errors' => [
                    'required' => "L'identifiant de l'assurance est obligatoire",
                    'integer'  => "L'identifiant de l'assurance n'est pas reconnu",
                    'is_not_unique'  => "L'identifiant de l'assurance n'est pas reconnu",
                ]
            ],
            'dateDebutValidite' => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj']
            ],
            'dateFinValidite' => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj']
            ],
            'beneficiaires' => 'if_exist',
            'beneficiaires.*' => [
                'rules'  => 'if_exist|integer|is_not_unique[utilisateurs.id]',
                'errors' => [
                    'integer'       => "L'identifiant du bénéficiaire n'est pas reconnu",
                    'is_not_unique' => "L'identifiant du bénéficiaire n'est pas reconnu"
                ]
            ]
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            if (!isset($input['souscripteur'])) { // le souscripteur par défaut l'utilisateur courant
                $input['souscripteur'] = 1;
            }
            /*
                $input['code'] = random_string('alnum', 10);
                if (!isset($input['cout'])) {  // le cout par défaut est le prix de base de l'assurance à laquelle on souscrit
                    $input['cout'] = (float)model("AssurancesModel")->where("id", $input['assurance'])->findColumn("prix")[0];
                }
                $souscription     = new SouscriptionsEntity($input);
                $souscriptionID   = model("SouscriptionsModel")->insert($souscription);
                $souscription->id = $souscriptionID;
            */
            $souscription = $this->createSubscription($input['souscripteur'], $input['assurance'], $input['cout'] ?? null);
            if (isset($input['beneficiaires'])) {
                foreach ($input['beneficiaires'] as $benef) {
                    model("SouscriptionBeneficiairesModel")->insert(["souscription_id" => $souscriptionID, "beneficiaire_id" => $benef]);
                }
            }
            $souscription->beneficiaires;
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette souscription.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Souscription ajoutée.',
            'data'    => $souscription,
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    // private function createSubscription($subscriberID, $assuranceID, $cost=null): \Modules\Assurances\Entities\SouscriptionsEntity
    private function createSubscription($subscriberID, $assuranceID, $cost = null): SouscriptionsEntity
    {
        $data = [
            'code'         => random_string('alnum', 10),
            'souscripteur' => $subscriberID,
            'assurance'    => $assuranceID,
            'cout'         => $cost,
            // 'etat'         => SouscriptionsEntity::PENDING,    // par défaut l'état sera ainsi PENDING
        ];
        if (!$cost) {  // le cout par défaut est le prix de base de l'assurance à laquelle on souscrit
            $data['cout'] = (float)model("AssurancesModel")->where("id", $assuranceID)->findColumn("prix")[0];
        }
        $souscription     = new SouscriptionsEntity($data);
        $souscription->id = model("SouscriptionsModel")->insert($souscription);

        return $souscription;
    }

    /** @todo Ne donner l'autorisation à ceci qu'à un administrateur
     * Update a subscription, from "posted" properties
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'cout'         => 'if_exist',
            'souscripteur' => [
                'rules'  => 'if_exist|integer|is_not_unique[utilisateurs.id]',
                'errors' => [
                    'integer' => "L'identifiant du souscripteur n'est pas reconnu",
                    'is_not_unique' => "L'identifiant du souscripteur n'est pas reconnu",
                ]
            ],
            'assurance'    => [
                'rules'  => 'if_exist|integer|is_not_unique[assurances.id]',
                'errors' => [
                    'required' => "L'identifiant de l'assurance est obligatoire",
                    'integer'  => "L'identifiant de l'assurance n'est pas reconnu",
                    'is_not_unique'  => "L'identifiant de l'assurance n'est pas reconnu",
                ]
            ],
            'dateDebutValidite' => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj']
            ],
            'dateFinValidite' => [
                'rules'  => 'if_exist|valid_date[Y-m-d]',
                'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj']
            ],
            'beneficiaires' => [
                'rules'  => 'if_exist',
                'errors' => ['required' => "Le nombre de beneficiaires est obligatoire"]
            ],
            'beneficiaires.*' => [
                'rules'  => 'if_exist|integer|is_not_unique[utilisateurs.id]',
                'errors' => [
                    'integer'       => "L'identifiant du bénéficiaire n'est pas reconnu",
                    'is_not_unique' => "L'identifiant du bénéficiaire n'est pas reconnu"
                ]
            ]
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
            $souscription = model("SouscriptionsModel")->find($id);
            $souscription->fill($input);
            model("SouscriptionsModel")->update($id, $souscription);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette souscription.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Souscription mise à jour.',
            'data'    => $souscription,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Delete the designated subscription record in the database
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        try {
            $souscription = model("SouscriptionsModel")->find($id);
            if ($souscription->dateDebutValidite) {
                $message = "impossible de supprimer une souscription déjà activée.";
            } else {
                $message = "Souscription supprimée.";
                model("SouscriptionsModel")->delete($id);
            }
        } catch (\Throwable $th) {
            $response = [
                'statut' => 'no',
                'message' => 'Identification de la souscription impossible.',
                'errors' => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }

        $response = [
            'statut'        => 'ok',
            'message'       => $message,
            'data'          => [],
        ];
        return $this->sendResponse($response);
    }

    public function getQuestionAnswer($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $souscription  = model("SouscriptionsModel")->where($identifier['name'], $identifier['value'])->first();
        $data = $souscription?->questionAnswers;
        $response = [
            'status'  => 'ok',
            'message' => $data ? "Réponses au questionnaire de souscription." : "Souscription ou réponses introuvables.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * save a subscription's question answer
     *
     * @param  int $id subscription identifier
     * @return ResponseInterface The HTTP response.
     */
    public function addQuestionAnswer($id)
    {
        /*
            on recupère la question, l'option choisie et la souscription
            si la souscription à déjà une réponse pour cette question,
                on récupère sa valeur ajoutée et on édite la réponse avec sa nouvelle valeur ajoutée,
            si non,
                on enregistre la nouvelle réponse
            on met à jour le prix de la souscription
        */
        $rules = [
            'question'   => [
                'rules'  => 'required|integer|is_not_unique[questions.id]',
                'errors' => [
                    'required'      => "Impossible d'identifier la question.",
                    'integer'       => "Identifiant de question inapproprié.",
                    'is_not_unique' => "Impossible d'identifier la question.",
                ]
            ],
            'choix'  => [
                'rules'  => 'required',
                'errors' => [
                    'required' => "Impossible de déterminer l'option choisie.",
                ]
            ],
            'choix.label'  => [
                'rules'  => 'if_exist|string',
                'errors' => [
                    'required' => "Impossible de déterminer l'option choisie.",
                ]
            ],
            'choix.idOption'  => [
                'rules'  => 'required|integer|is_not_unique[question_options.id]',
                'errors' => [
                    'required'      => "Impossible de déterminer l'option choisie.",
                    'integer'       => "Impossible de déterminer l'option choisie.",
                    'is_not_unique' => "Impossible de déterminer l'option choisie.",
                ]
            ],
            'choix.prix'  => [
                'rules'  => 'if_exist|numeric',
                'errors' => [
                    'numeric' => "la valeur associée à cette option est inappropriée.",
                ]
            ],
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $questionID = $input['question'];
            $prixChoix  = model("QuestionOptionsModel")->where('id', $input['choix']['idOption'])->findColumn('prix')[0];

            // calculate the added price
            $question   = model("QuestionsModel")->find($questionID);
            $basePrice  = model("SouscriptionsModel")->getBasePrice($id);
            $addedPrice = $this->calculateAddedPrice((float)$prixChoix, (float)$basePrice, $question->tarif_type);

            //verify if there is an answer for this question in this subscription
            $answer = model("SouscriptionQuestionAnswersModel")->answerExist($id, $questionID);

            // Insert or update the answer
            $questionAns = new QuestionAnswersEntity([
                'added_price' => $addedPrice,
                'choix'       => $input['choix'],
                'question_id' => $input['question']
            ]);
            // update the subscription price
            $subscription = model("SouscriptionsModel")->find($id);
            model("SouscriptionsModel")->db->transBegin();
            if ($answer) {
                model("QuestionAnswersModel")->update($answer['id'], $questionAns);
                $subscription->cout = $subscription->cout - $answer['added_price'] + $addedPrice;
            } else {
                $questionAns->id = model("QuestionAnswersModel")->insert($questionAns);
                $subscription->cout = $subscription->cout + $addedPrice;
                model("SouscriptionQuestionAnswersModel")->insert([
                    'souscription_id' => $id,
                    'questionans_id'  => $questionAns->id
                ]);
            }

            model("SouscriptionsModel")->update($id, $subscription);
            model("SouscriptionsModel")->db->transCommit();
        } catch (\Throwable $th) {
            model("SouscriptionsModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter cette réponse.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'réponse ajoutée',
            'data'    => $questionAns,
        ];
        return $this->sendResponse($response);
    }

    private function calculateAddedPrice(float $optionPrice, float $basePrice, string $tarifType): float
    {
        if ($tarifType == QuestionsEntity::$tarifTypes[QuestionsEntity::ADDITION]) {
            return $optionPrice;
        } elseif ($tarifType == QuestionsEntity::$tarifTypes[QuestionsEntity::POURCENTAGE]) {
            return $basePrice * ($optionPrice / 100);
        }
    }

    /**
     * Retrieve the documents of identified subscription
     *
     * @param  int $id - The subscription identifier
     * @return ResponseInterface The HTTP response.
     */
    public function getSouscriptionDocument($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $souscription  = model("SouscriptionsModel")->where($identifier['name'], $identifier['value'])->first();
        $data = $souscription->documents;
        $response = [
            'status'  => 'ok',
            'message' => $data ? "Document(s) de la souscription." : "Aucun document trouvé pour cette souscription.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associate documents to the identified subscription
     *
     * @param  int $id - The subscription identifier
     * @return ResponseInterface The HTTP response.
     */
    public function addSouscriptionDocument($id)
    {
        $rules = [
            'documents'   => [
                'rules'   => 'required',
                'errors'  => ['required' => "l'identification du document est requis"]
            ],
            'documents.*' => [
                'rules'   => 'integer|is_not_unique[documents.id]',
                'errors'  => [
                    'is_not_unique' => "impossible d'identifier le document",
                    'integer' => "impossible d'identifier le document",
                ]
            ],
        ];
        $input = $this->getRequestInput($this->request);

        $model = model("SouscriptionDocumentsModel");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible de joindre ce(s) document(s) à la souscription.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        foreach ($input['documents'] as $idDocument) {
            try {
                $model->insert(["souscription_id" => (int)$id, "document_id" => (int)$idDocument]);
            } catch (\Throwable $e) {
            }
        }
        $response = [
            'status'  => 'ok',
            'message' => "Document(s) de souscription ajouté(s).",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    public function getBeneficiaires($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $souscription  = model("SouscriptionsModel")->where($identifier['name'], $identifier['value'])->first();
        $data = $souscription->beneficiaires;

        $response = [
            'status'  => 'ok',
            'message' => $data ? "Bénéficiaires de la souscription." : "Aucun bénéficiaire trouvé pour cette souscription.",
            'data'    => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Associate beneficiaries to the identified subscription
     *
     * @param  int $id - The subscription identifier
     * @return ResponseInterface The HTTP response.
     */
    public function addBeneficiaires($id)
    {
        $rules = [
            'beneficiaires'   => [
                'rules'       => 'required',
                'errors'      => ['required' => "Impossible d'identifier les bénéficiaires"]
            ],
            'beneficiaires.*' => [
                'rules'       => 'integer|is_not_unique[utilisateurs.id]',
                'errors'      => [
                    'is_not_unique' => "impossible d'identifier le(s) bénéficiaire(s)",
                    'integer' => "impossible d'identifier le(s) beneficiaire(s)",
                ]
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
                'message' => "Impossible de joindre ce(s) bénéficiaire(s) à la souscription.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        foreach ($input['beneficiaires'] as $idBenef) {
            try {
                model("SouscriptionBeneficiairesModel")->insert(["souscription_id" => (int)$id, "beneficiaire_id" => (int)$idBenef]);
            } catch (\Throwable $e) {
                //throw $th;
            }
        }
        $response = [
            'status'  => 'ok',
            'message' => "Bénéficiaire(s) de souscription ajouté(s).",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    public function getSouscriptionInfos($id)  //the show function already did the work
    {
    }

    public function getSouscriptAssurInfo($idAssur)
    {
        /*
            Vérifie si une souscription en cours de creation existe pour ce produit et cet utilisateur
                si oui, renvoie les détails du produit avec l'identifiant de souscription correspondant
                si non, cree une souscription en cours de traitement pour ce user et ce produit puis 
                renvoie les détails du produit, jumelé à l'identifiant de souscription.
        */
        //recuperons l'assurance
        $identifier = $this->getIdentifier($idAssur, 'id');
        $assurance  = model("AssurancesModel")->where($identifier['name'], $identifier['value'])->first();
        $idAssur = $assurance->id;
        if (!$assurance) {
            $response = [
                'status' => 'no',
                'message' => "L'assurance indiquée est inconnue.",
                'data' => [],
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        // Vérifions l'existance d'une souscription en cours de creation
        $currUserID = $this->request->utilisateur->id;    // utilisateur actuellement connecté
        $souscription = model("SouscriptionsModel")
            ->where('souscripteur_id', $currUserID)
            ->where('assurance_id', $idAssur)
            ->where('etat', SouscriptionsEntity::PENDING)
            ->first();
        if ($souscription) {
            $answers = model("SouscriptionQuestionAnswersModel")->join('question_answers', 'souscription_questionans.questionans_id = question_answers.id')
                ->where('souscription_id', $souscription->id)
                ->findAll() ?? [];
            // ->findColumn('questionans_id') ?? [];
        } else {
            $souscription = $this->createSubscription($currUserID, $idAssur);
            $answers = [];
        }
        // return $this->show($souscription->id);   // the rest is not more used but this line should be removed.


        //Questions de l'assurance
        $assurQuestIDs = model("AssuranceQuestionsModel")->where('assurance_id', $idAssur)->findColumn('question_id') ?? [];
        if ($assurQuestIDs) {
            $optionIDs     = model("QuestionsModel")->whereIn('id', $assurQuestIDs)->findColumn('options') ?? [];
            $optionIDs     = array_map(fn ($opt) => json_decode($opt) ?? [], $optionIDs);
            $optionIDs     = array_unique(array_merge(...$optionIDs));
            $subquestIDs   = model("QuestionOptionsModel")->whereIn('id', $optionIDs)->findColumn('subquestions') ?? [];
            $subquestIDs   = array_map(fn ($sub) => json_decode($sub) ?? [], $subquestIDs);

            $subquestIDs   = array_unique(array_merge(...$subquestIDs));

            $questionIDs   = array_merge($assurQuestIDs, $subquestIDs);
            $questionIDs   = array_unique($questionIDs);

            $questions = model("QuestionsModel")->whereIn('id', $questionIDs)->findAll();
            //Nous sommes supposé avoir autant de ($)questions que de ($)questionIDs
            $questions = array_combine($questionIDs, $questions);
            $options   = model("QuestionOptionsModel")->whereIn('id', $optionIDs)->findAll();
            //Nous sommes supposé avoir autant de ($)opions que de ($)optionIDs
            $options   = array_combine($optionIDs, $options);
            $options   = array_map(function ($opt) {
                $opt->selected = false;
                return $opt;
            }, $options);

            /* Maintenant que nous avons toutes les questions et toutes les réponses involved, faisons l'assemblage
            pour obtenir le questionnaire de l'assurance avec les réponses le cas échéant.*/
            // on indique pour les options ccelles qui sont choisies
            foreach ($answers as $answer) {
                $choix    = json_decode($answer["choix"], true) ?? [];
                $optionID = $choix["idOption"];
                $options[$optionID]->selected = true;
            }

            // on associe les options aux questions
            foreach ($questions as $question) {
                $optIDs = $question->options ?? [];
                $opts   = [];
                foreach ($optIDs as $optID) {
                    $opts[] = $options[$optID] ?? [];
                }
                $question->options = $opts;
            }
            $questionnaire = [];
            // on associe les questions aux sousquestions
            foreach ($assurQuestIDs as $questID) {
                $quest   = $questions[$questID];
                $opts = [];
                foreach ($quest->options as $opt) {
                    if ($opt->subquestions) {
                        $subQuestIDs = $opt->subquestions;
                        $opt->subquestions = array_map(fn ($id) => $questions[$id], $subQuestIDs);
                    }
                    $opts[] = $opt;
                }
                $quest->options = $opts;
                $questionnaire[] = $quest;
            }

            /* Nous pouvons enfin associer le questionnaire à l'assurance */
            $assurance->questionnaire = $questionnaire;
        } else {
            $assurance->questionnaire = [];
        }
        $assurance->reductions;
        $assurance->services;
        $assurance->documentation;
        $assurance->payOptions;
        $assurance->questionnaire;
        $assurance->images;
        $assurance->questionnaire;

        $souscription->assurance = $assurance;
        $souscription->beneficiaires;
        $souscription->documents;
        $souscription->questionAnswers;
        $response = [
            'statut' => 'ok',
            // 'message' => "Détails de l'assurance.",
            'message' => "Détails de la souscription.",
            // 'data' => $assurance,
            'data' => $souscription,
        ];
        return $this->sendResponse($response);
    }

    /* Après réflexion, pas vraiment utile
        /**
         * getPaymentOption
         *
         * @param  int $id - The subscription identifier
         * @return ResponseInterface The HTTP response.
         *
        public function getPaymentOption($id)
        {
        }

        /**
         * setPaymentOption
         *
         * @param  int $id - The subscription identifier
         * @return ResponseInterface The HTTP response.
         *
        public function setPaymentOption($id)
        {
        }
    */
}
