<?php

namespace Modules\Messageries\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Messageries\Entities\MessageEntity;

class MessagesController extends BaseController
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
    }

    /**
     * Retourne la liste de toutes les conversations enregistrées
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
    }

    /**
     * Returne les détails d'une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
    }

    /**
     * Cree une conversdation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
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

    public function sendAppMessage(array $message)
    {
        /* Attend en post un nouveau message Qu'il va enregistrer et retourner les url des fichiers-joints au cas échéant
            Tous les fichiers doivent être contenu dans un input dans un input dont le nom est file<i> où i est un compteur 
            servant à les différentier.
            format attendu : 
            $input = [
                'destination'= identifiant de la conversation
                'message'    = corps(texte) du message
            ] plus les fichiers si ajoutés.
            Doit retourner un tableau de liens(avec les mêmes clés fournies) vers les fichiers ajoutés
        */

        $msgModel    = new MessageModel();
        $docMsgModel = new DocumentMessageModel();
        $input = $this->getRequestInput($this->request);
        $files = $this->request->getFiles();
        $result = [];
        $rules = [
            "message"    => [
                "rules"  => 'required',
                "errors" => [
                    'require' => 'Le message est requis.',
                    // 'numeric' => 'Le type de l\'emetteur est non valide.'
                ],
            ],
            "destination" => [
                "rules"   => 'required|alpha_numeric',
                "errors"  => [
                    'require'       => 'La destination est requise.',
                    'alpha_numeric' => 'Le type de la destination est non valide.',
                ],
            ],
        ];
        if (!$this->validate($rules)) {
            $response = [
                'status'  => 'no',
                'message' => $this->validator->getErrors(),
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        $conversationId = $input['destination'];
        $messageData = [
            'from_user_id' => UtilisateurModel::staticGetIdByEmail($this->request->userEmail),
            'to_conversation_id' => $conversationId,
            'msg_text'    => (string)htmlspecialchars($input['message'] ?? ''),
            'dateCreation' => date('Y-m-d H:i:s'),
            'etat'        => 1,
            'statut'      => 1,
        ];
        $msgId = $msgModel->insert($messageData);
        $result['message'] = $messageData['msg_text'];
        $result['statut']  = 'Non Lu';
        $result['date']    = $messageData['dateCreation'];

        if (isset($files)) {
            foreach ($files as $key => $file) {
                $type = $file->getClientMimeType();
                $subType = explode('/', $type)[0];
                if ($file->isValid() && !$file->hasMoved()) {
                    $folder = strtolower(TypeConversationModel::getNameById($conversationId));
                    $path   = "conversations/" . $folder . "/" . $subType . "s/" . date('Y-m-d');
                    if ($subType == 'image') {
                        $savedFile = $this->saveImage($file, $key, 250, $path);
                    } elseif ($subType == 'audio') {
                        $savedFile = $this->saveAudio($file, $key, 250, $path);
                    } elseif ($subType == 'application') {
                        $savedFile = $this->saveTextFile($file, $key, 50, $path);
                    } elseif ($subType == 'video') {
                        $savedFile = $this->saveVideo($file, $key, 1000, $path);
                    }
                    if ($savedFile['status'] === 'ok') {
                        $uri = $savedFile['uri'];
                        $result['fichiers'][$key] = ['url' => base_url($uri), 'type' => $type];
                        $docMsgModel->insert([
                            // 'nom' => $subType.'_incident'.$input['incidentId'], 
                            'nom' => $subType . '_incident' . date('Y-m-dH:i'),
                            'type' => $type,
                            'uri' => $uri,
                            'message_id' => $msgId
                        ]);
                    } else {
                        $result['fichiers'] = [];
                        $result[$key]['errors'] = $savedFile['errors'];
                    }
                }
            }
        }

        // transfère le message au besoin vers la plateforme appropriée
        $this->transfertToChannel($messageData, $conversationId);

        $response = [
            'statut' => 'ok',
            'message' => 'message envoyé',
            'data'   => $result,
        ];
        return $this->getResponse($response);
    }
}
