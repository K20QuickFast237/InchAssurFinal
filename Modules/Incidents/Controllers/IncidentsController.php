<?php

namespace Modules\Incidents\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Incidents\Entities\IncidentsEntity;
use Modules\Messageries\Entities\ConversationEntity;
use CodeIgniter\Database\Exceptions\DataException;

class IncidentsController extends BaseController
{
    use ResponseTrait;
    use ControllerUtilsTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Renvoie tous les incidents actifs de l'utilisateur identifié.
     *
     * @param  mixed $identifier identifiant ou code de l'utilisateur
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
        if ($identifier) {
            if (!auth()->user()->inGroup('administrateur')) {
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
        $incidents = model("IncidentsModel")->where("auteur_id", $utilisateur->id)
            ->where("etat", IncidentsEntity::ACTIF)
            ->findAll();

        $response = [
            'status' => 'ok',
            'message' => count($incidents) ? 'Incidents déclarés.' : 'Aucun incident déclaré.',
            'data' => $sinistres ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie toutes les déclarations d'incidents.
     * pour les administrateurs seulement.
     *
     * @param  boolean $active specifie si seuls les déclarations actives doivent être renvoyées
     * @return ResponseInterface The HTTP response.
     */
    public function getAllIncidents($active = false)
    {
        if (!auth()->user()->can('incidents.viewAll')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        if ($active) {
            $incidents = model("IncidentsModel")
                ->where("etat", IncidentsEntity::ACTIF)
                ->findAll();
        } else {
            $incidents = model("IncidentsModel")->findAll();
        }

        $response = [
            'status' => 'ok',
            'message' => count($incidents) ? 'Incidents trouvés.' : 'Aucun Incident trouvé.',
            'data' => $incidents ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie les détails d'une déclaration de incident
     *
     * @param  mixed $id identifiant où code de la déclaration d'incident
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $incident   = model("IncidentsModel")->where($identifier['name'], $identifier['value'])->first();
        $incident->images;
        $response   = [
            'statut'  => 'ok',
            'message' => $incident ? "Détails de l'incident." : "Impossible de trouver les détails de cette déclaration d'incident.",
            'data'    => $incident ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une déclaration d'incident
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create($declarant = null)
    {
        /*
            L'incident est un disfonctionnements ou bugs rencontrés sur la plateforme et
            ayant eu des répercussions sur le compte utilisateur.
            Déclarer un incident reviens à faire une descriptoin des circonstances
            et au besoin y joindre des images (captures d'écran) justificatives.
            Aussi, cette déclaration ouvre une conversation.
        */
        $rules = [
            'sujet'          => ['rules' => 'required', 'errors' => ['required' => 'Le sujet est obligatoire']],
            'description'    => ['rules' => 'required', 'errors' => ['required' => 'La description est obligatoire']],
            'idTypeIncident' => [
                'rules'   => 'required|is_not_unique[incident_types.id]',
                'errors'  => [
                    'required' => 'Le type est obligatoire.',
                    'is_not_unique' => "Le type spécifié n'est pas reconnu.",
                ]
            ],
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception("Veuillez corriger les erreurs suivantes: " . $this->validator->getErrors());
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de déclarer ce sinistre.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        if ($declarant) {
            if (!auth()->user()->inGroup('administrateur')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($declarant, 'id');
            $declarant  = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $declarant  = $this->request->utilisateur;
        }

        $input = $this->getRequestInput($this->request);
        // Ouverture de la conversation ou réutilisation
        $codeIncident = $this->generateCodeIncident();
        $conversationInfo = [
            "nom"     => "Reclamations-$codeIncident",
            "description" => "Échanges pour la résolution de l'incident $codeIncident",
            "type" => ConversationEntity::TYPE_INCIDENT,
        ];
        model("IncidentsModel")->db->transBegin();
        $convId = model("ConversationsModel")->insert($conversationInfo);
        model("ConversationMembresModel")->insert(["conversation_id" => $convId, "membre_id" => $declarant->id, "isAdmin" => false]);

        $incidentInfo = [
            "code"  => $codeIncident,
            "titre" => $input['sujet'],
            "description" => $input['description'],
            "auteur_id" => $declarant->id,
            "type_id" => (int)$input["idTypeIncident"],
            "conversation_id" => $convId,
        ];
        $incidentId = model("IncidentsModel")->insert($incidentInfo);
        model("IncidentsModel")->db->transCommit();

        // Enregistrer les fichiers joints au cas échéant
        // $images    = $this->request->getFiles("images");
        // $documents = $this->request->getFiles("documents");
        $files     = $this->request->getFiles();
        $images    = $files["images"] ?? [];
        foreach ($images as $img) {
            $imgID = saveImage($img, 'uploads/incidents/images/');
            model("IncidentImagesModel")->insert(["incident_id" => $incidentId, "image_id" => $imgID]);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Déclaration Enregistrée.',
            'data'    => ["idIncident" => $incidentId],
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    private function generateCodeIncident()
    {
        $maxId = model("IncidentsModel")->orderBy("id", "DESC")->findColumn("id")[0] ?? 0;
        $num   = str_pad($maxId + 1, 5, "0", STR_PAD_LEFT);
        return "I" . substr(date('Y'), 2) . $num;
    }

    /**
     * Met à jour la déclaration de sinistre identifiée
     *
     * @param  mixed $id identifiant où code de la déclaration de sinistre
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
        $rules = [
            'sujet'          => 'if_exist',
            'description'    => 'if_exist',
            'idTypeIncident' => [
                'rules'   => 'if_exist|is_not_unique[incident_types.id]',
                'errors'  => ['is_not_unique' => "Le type spécifié n'est pas reconnu.",]
            ],
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception("Veuillez corriger les erreurs suivantes: " . $this->validator->getErrors());
            }
        } catch (DataException $de) {
            $response = [
                'statut'  => 'ok',
                'message' => "Aucune modification apportée.",
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour cette déclaration d'incident.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $input = $this->getRequestInput($this->request);

        // Ouverture de la conversation ou réutilisation
        $incidentInfo = [
            "titre" => $input['sujet'] ?? null,
            "description" => $input['description'] ?? null,
            "type_id" => (int)$input["idTypeIncident"] ?? null,
        ];
        model("IncidentsModel")->update($id, array_filter($incidentInfo));

        $response = [
            'statut'  => 'ok',
            'message' => "Déclaration d'incident mise à jour.",
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprimme la déclaration d'incident identifiée
     *
     * @param  mixed $id identifiant où code de la déclaration de sinistre
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        $incident = model("IncidentsModel")->find($id);
        model("IncidentsModel")->delete($id);
        model("IncidentImagesModel")->where('incident_id', $id)->delete();
        $convName = 'Reclamations-' . $incident->code;
        model("ConversationsModel")->where('nom', $convName)->delete();

        $response = [
            'statut'  => 'ok',
            'message' => "Déclaration d'incident Supprimée.",
        ];
        return $this->sendResponse($response);
    }

    public function setIncidentImages($id)
    {
        $rules = [
            'url' => "if_exist",
            'images' => [
                'rules'  => 'if_exist|uploaded[images]',
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
            if ($images) {
                foreach ($images['images'] as $img) {
                    $imgID = saveImage($img, 'uploads/incidents/images/');
                    model("IncidentImagesModel")->insert(["incident_id" => $id, "image_id" => $imgID]);
                }
            }
            if (isset($input['url'])) {
                $imgID = model("ImagesModel")->insert(["uri" => $input["url"], "isLink" => true]);
                model("IncidentImagesModel")->insert(["incident_id" => $id, "image_id" => $imgID]);
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Impossible d'associer cette(ces) image(s) à la déclaration de sinstre.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => "Image ajoutée à la déclaration d'incident.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the Images of the identified incident declaration
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getIncidentImages($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $incident   = model("IncidentsModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $incident->images;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Images de la déclaration d'incident." : "Aucune image pour cette déclaration d'incident.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    public function delIncidentImage($id, $imgId)
    {
        model("IncidentImagesModel")->where('incident_id', $id)->where("image_id", $imgId)->delete();

        $response = [
            'statut'  => 'ok',
            'message' => "Image de déclaration d'incident Supprimée.",
        ];
        return $this->sendResponse($response);
    }

    // /**
    //  * Retrieve the Documents provided for the identified sinistre declaration
    //  *
    //  * @param  mixed $id
    //  * @return ResponseInterface The HTTP response.
    //  */
    // public function getSinistreDocuments($id)
    // {
    //     $identifier = $this->getIdentifier($id, 'id');
    //     $sinistre   = model("SinistresModel")->where($identifier['name'], $identifier['value'])->first();
    //     $data       = $sinistre->documents;

    //     $response = [
    //         'statut'  => 'ok',
    //         'message' => $data ? "Document(s) joint(s) à la déclaration de sinistre." : "Aucun document join à cette déclaration de sinistre.",
    //         'data'    => $data,
    //     ];
    //     return $this->sendResponse($response);
    // }

    // public function setSinistreDocument($id)
    // {
    //     $rules = [
    //         'titre'    => ['rules' => 'required', 'errors' => ['required' => "Un titre est nécessaire pour enregistrer le document."]],
    //         'url'      => 'if_exist',
    //         'document' => 'if_exist|uploaded[document]',
    //     ];
    //     $input    = $this->getRequestInput($this->request);
    //     $document = $this->request->getFile('document');

    //     $model = model("SinistreDocumentsModel");
    //     try {
    //         if (!$this->validate($rules)) {
    //             $hasError = true;
    //             throw new \Exception();
    //         }
    //         $model->db->transBegin();
    //         if (isset($input['url'])) {
    //             $docID = model("DocumentsModel")->insert([
    //                 "titre" => $input['titre'],
    //                 "uri"   => $input['url'],
    //                 "isLink" => true,
    //             ]);
    //         } elseif ($document) {
    //             $docID = saveDocument($input['titre'], $document, 'uploads/Sinistres/documents/');
    //         }
    //         $model->insert(["sinistre_id" => (int)$id, "document_id" => $docID]);

    //         $model->db->transCommit();
    //         $response = [
    //             'statut'  => 'ok',
    //             'message' => "Document(s) associé(s) au sinistre.",
    //             'data'    => [],
    //         ];
    //         return $this->sendResponse($response);
    //     } catch (\Throwable $th) {
    //         $model->db->transRollback();
    //         $errorsData = $this->getErrorsData($th, isset($hasError));
    //         $response = [
    //             'statut'  => 'no',
    //             'message' => "Impossible d'associer ce(s) document(s) à la déclaratrion de sinistre.",
    //             'errors'  => $errorsData['errors'],
    //         ];
    //         return $this->sendResponse($response, $errorsData['code']);
    //     }
    // }

    // public function delSinistreDocument($id, $docId)
    // {
    //     model("SinistreDocumentsModel")->where('sinistre_id', $id)->where("document_id", $docId)->delete();

    //     $response = [
    //         'statut'  => 'ok',
    //         'message' => 'Document de sinistre Supprimée.',
    //     ];
    //     return $this->sendResponse($response);
    // }
}
