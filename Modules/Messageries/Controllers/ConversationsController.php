<?php

namespace Modules\Messageries\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Messageries\Entities\ConversationEntity;

class ConversationsController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des conversations d'un utilisateur
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier) {
            if (!auth()->user()->can('conversations.view')) {
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

        $conversationIDs = model("ConversationMembresModel")->where("membre_id", $utilisateur->id)->findColumn('conversation_id');
        if ($conversationIDs) {
            $conversations = model("ConversationsModel")
                ->whereIn("id", $conversationIDs)
                ->where("etat", ConversationEntity::ACTIF)
                ->findAll();
        }

        $response = [
            'statut' => 'ok',
            'message' => count($conversations) ? 'Conversations trouvées.' : 'Aucune conversation trouvée.',
            'data' => $conversations ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retourne la liste de toutes les conversations enregistrées
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
        if (!auth()->user()->inGroup('administrateur')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        /** @todo penser à spécifier certains champs pour la liste vue que le listing doit être avec le strict nécessaire */
        $data = model("ConversationsModel")->findAll();
        $response = [
            'statut' => 'ok',
            'message' => $data ? count($data) . ' Conversations trouvées.' : 'Aucune conversation trouvée.',
            'data' => $data ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Returne les détails d'une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        $conversation = model("ConversationsModel")->where('id', $id)->first();
        $conversation->membres;
        $response = [
            'statut' => 'ok',
            'message' => $conversation ? 'Détails de la conversation.' : 'Aucune conversation trouvée.',
            'data' => $conversation ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Cree une conversdation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        /* 
            Une converssation est un échange entre deux conversants où plus.
            Lors de la créations d'une conversation il faut fournir les identifiants
            du/des membres, un titre et/ou une image pour le cas d'une conversation de groupe.
        */
        $rules = [
            "membres" => [
                "rules" => 'required',
                "errors"  => [
                    'require' => 'La liste des membres est requise.',
                ],
            ],
            "membres.*" => [
                "rules" => 'if_exist|is_not_unique[utilisateurs.id]',
                "errors"  => [
                    'is_not_unique' => 'Membre inconnu.',
                ],
            ],
            "titre" => 'if_exist',
            'image'           => [
                'rules'  => 'if_exist|uploaded[image]',
                'errors' => ['uploaded' => 'Immage invalide.'],
            ],
        ];

        $input = $this->getRequestInput($this->request);
        $image = $this->request->getFile("image");
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $membList    = $input["membres"];
            $isGroupConv = gettype($membList) == 'integer' || gettype($membList) == 'string' || count($membList) == 1;
            $membres     = [];
            $conversationInfo = [
                "nom"  => $input['titre'] ?? null,
                "type" => $isGroupConv ? ConversationEntity::TYPE_MESSAGE : ConversationEntity::TYPE_Groupe,
            ];
            if ($image) {
                $imgId = saveImage($image, 'uploads/incidents/images/');
                $conversationInfo["image_id"] = $imgId;
            }

            model("ConversationsModel")->db->transBegin();
            $convId    = model("ConversationsModel")->insert($conversationInfo);
            $membres[] = [
                "conversation_id" => $convId,
                "membre_id" => $this->request->utilisateur->id,
                "isAdmin" => true,
            ];
            if (gettype($membList) == 'array') {
                foreach ($membList as $membre) {
                    $membres[] = ["conversation_id" => $convId, "membre_id" => $membre];
                }
            } else {
                $membres[] = ["conversation_id" => $convId, "membre_id" => $membList];
            }

            foreach ($membres as $membre) {
                model("ConversationMembresModel")->insert($membre);
            }
            // model("ConversationMembresModel")->insertBatch($membres);
            model("ConversationsModel")->db->transCommit();
        } catch (\Throwable $th) {
            model("ConversationsModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de créer cette conversation.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Coversation ajoutée.",
            'data'    => ["idConversation" => $convId],
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
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

    /**
     * Ajoute des membres de la conversation identifiée
     *
     * @return ResponseInterface The HTTP response.
     */
    public function addConversationMember(int $convId)
    {
        $rules = [
            "membres" => [
                "rules" => 'required',
                "errors"  => [
                    'require' => 'La liste des membres est requise.',
                ],
            ],
            "membres.*" => [
                "rules" => 'if_exist|is_not_unique[utilisateurs.id]',
                "errors"  => [
                    'is_not_unique' => 'Membre inconnu.',
                ],
            ],
        ];

        $input = $this->getRequestInput($this->request);
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $membList     = $input["membres"];
            $membres     = [];

            // définition de la liste de membres
            if (gettype($membList) == 'array') {
                foreach ($membList as $membre) {
                    $membres[] = ["conversation_id" => $convId, "membre_id" => $membre];
                }
            } else {
                $membres[] = ["conversation_id" => $convId, "membre_id" => $membList];
            }

            // ajout des membres
            $conversation = model("ConversationsModel")->where('id', $convId)->first();
            $isAdmin = model("ConversationMembresModel")->where("conversation_id", $convId)
                ->where("membre_id", $this->request->utilisateur->id)
                ->findColumn("isAdmin")[0] ?? false;
            if ($isAdmin || auth()->user()->inGroup('administrateur')) {
                if ($conversation->type == ConversationEntity::TYPE_Groupe) {
                    model("ConversationsModel")->db->transBegin();
                    foreach ($membres as $membre) {
                        model("ConversationMembresModel")->insert($membre);
                    }
                    model("ConversationsModel")->db->transCommit();
                } else {
                    $response = [
                        'statut'  => 'no',
                        'message' => "Les membres ne sont ajoutés qu'aux conversations de groupe.",
                    ];
                    return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
                }
            } else {
                $response = [
                    'statut'  => 'no',
                    'message' => "Seul un administrateur peut effectuer cette action.",
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
            }
        } catch (\Throwable $th) {
            model("ConversationsModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'ajouter ce(s) membre(s)'.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Membre ajouté à la conversation.",
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retire le membre désigné de la conversation identifiée
     *
     * @return ResponseInterface The HTTP response.
     */
    public function removeConversationMember(int $convId, int $membId)
    {
        $isAdmin = model("ConversationMembresModel")->where("conversation_id", $convId)
            ->where("membre_id", $this->request->utilisateur->id)
            ->findColumn("isAdmin")[0] ?? false;
        if ($isAdmin || auth()->user()->inGroup('administrateur')) {
            model("ConversationMembresModel")->where("conversation_id", $convId)
                ->where("membre_id", $membId)
                ->delete();
        } else {
            $response = [
                'statut'  => 'no',
                'message' => "Seul un administrateur peut effectuer cette action.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }
        $response = [
            'statut'  => 'ok',
            'message' => "Membre retiré de la conversation.",
        ];
        return $this->sendResponse($response);
    }

    public function setConversationAdminMember(int $convId, int $membId)
    {
        $isAdmin = model("ConversationMembresModel")->where("conversation_id", $convId)
            ->where("membre_id", $this->request->utilisateur->id)
            ->findColumn("isAdmin")[0] ?? false;
        if ($isAdmin || auth()->user()->inGroup('administrateur')) {
            model("ConversationMembresModel")->where("conversation_id", $convId)
                ->where("membre_id", $membId)
                ->set("isAdmin", true)
                ->update();
        } else {
            $response = [
                'statut'  => 'no',
                'message' => "Seul un administrateur peut effectuer cette action.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }
        $response = [
            'statut'  => 'ok',
            'message' => "Nouvel administrateur défini.",
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie les membres de la conversation identifiée
     *
     * @return ResponseInterface The HTTP response.
     */
    public function getConversationMembers(int $id)
    {
        $conversation = model("ConversationsModel")->where('id', $id)->first();
        $response = [
            'statut' => 'ok',
            'message' => $conversation ? 'Membres de la conversation.' : 'Aucun membre de conversation trouvée.',
            'data' => $conversation->membres ?? [],
        ];
        return $this->sendResponse($response);
    }

    public function getConversationMessages(int $id)
    {
        $conversation = model("ConversationsModel")->where('id', $id)->first();
        $response = [
            'statut' => 'ok',
            'message' => $conversation ? 'Messages de la conversation.' : 'Aucun message de conversation trouvé.',
            'data' => $conversation->messages ?? [],
        ];
        return $this->sendResponse($response);
    }

    public function addConversationMessage(int $id)
    {
        $rules = [
            "message"   => 'required_without[images,documents]',
            "images"    => 'if_exist|uploaded[images]',
            "documents" => 'if_exist|uploaded[documents]',
        ];

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            $input = $this->getRequestInput($this->request);
            $files     = $this->request->getFiles();
            $images    = $files["images"] ?? [];
            $documents = $files["documents"] ?? [];

            $data = ["images" => [], "documents" => []];
            $msgInfo = [
                "to_conversation_id" => $id,
                "from_user_id" => $this->request->utilisateur->id,
            ];
            if (isset($input['message'])) {
                $msgInfo["msg_text"] = $input['message'];
                $data['message'] = (string)htmlspecialchars($input['message'] ?? ''); //$input['message'];
            }
            $msgId = model("MessagesModel")->insert($msgInfo);

            foreach ($images as $img) {
                $imgInfo = getImageInfo($img, 'uploads/messages/images/');
                $imgID   = model('ImagesModel')->insert($imgInfo);
                model("MessageImagesModel")->insert(["message_id" => $msgId, "image_id" => $imgID]);
                $data['images'][] = ['idImage' => $imgID, 'url' => base_url($imgInfo['uri'])];
            }

            foreach ($documents as $doc) {
                // $title = str_replace(' ', '-', $doc->getClientName());
                $title = $doc->getClientName();
                $docInfo = getDocInfo($title, $doc, 'uploads/messages/documents/');
                $docInfo['titre'] = $title;
                $docID   = model('DocumentsModel')->insert($docInfo);
                model("MessageDocumentsModel")->insert(["message_id" => $msgId, "document_id" => $docID]);
                $data['documents'][] = ['idDocument' => $docID, 'url' => base_url($docInfo['uri']), 'titre' => $title];
            }
        } catch (\Throwable $th) {
            model("ConversationsModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'enregistrer ce message'.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut' => 'ok',
            'message' => 'message envoyé',
            'data'   => $data,
        ];
        return $this->sendResponse($response);
    }
}
