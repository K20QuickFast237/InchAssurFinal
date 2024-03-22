<?php

namespace Modules\Assurances\Controllers;

use App\Controllers\BaseController;
use App\Traits\ControllerUtilsTrait;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Traits\ErrorsDataTrait;
use Modules\Assurances\Entities\SinistresEntity;
use Modules\Messageries\Entities\ConversationEntity;
use CodeIgniter\Database\Exceptions\DataException;

class SinistresController extends BaseController
{
    use ResponseTrait;
    use ControllerUtilsTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Renvoie tous les sinistres actifs de l'utilisateur identifié.
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
        $sinistres = model("SinistresModel")->where("auteur_id", $utilisateur->id)
            ->where("etat", SinistresEntity::ACTIF)
            ->findAll();

        $sinistres = array_map(function ($s) {
            $s->images;
            $s->documents;
            return $s;
        }, $sinistres);

        $response = [
            'status' => 'ok',
            'message' => count($sinistres) ? 'Sinistres déclarés.' : 'Aucun sinistre déclaré.',
            'data' => $sinistres ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie toutes les déclarations de sinistres.
     * pour les administrateurs seulement.
     *
     * @param  boolean $active specifie si seuls les déclarations actives doivent être renvoyées
     * @return ResponseInterface The HTTP response.
     */
    public function getAllSinistres($active = false)
    {
        if (!auth()->user()->can('sinistres.viewAll')) {
            $response = [
                'statut' => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }
        if ($active) {
            $sinistres = model("SinistresModel")
                ->where("etat", SinistresEntity::ACTIF)
                ->findAll();
        } else {
            $sinistres = model("SinistresModel")->findAll();
        }

        $sinistres = array_map(function ($s) {
            $s->images;
            $s->documents;
            return $s;
        }, $sinistres);

        $response = [
            'status' => 'ok',
            'message' => count($sinistres) ? 'Sinistres trouvés.' : 'Aucun sinistre trouvé.',
            'data' => $sinistres ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie tous les types de déclarations de sinistres actifs.
     *
     * @param  boolean $active specifie si seuls les types de déclarations actives doivent être renvoyées
     * @return ResponseInterface The HTTP response.
     */
    public function getActiveTypeSinistres($active = true)
    {
        if ($active) {
            $typeSinistres = model("SinistreTypesModel")
                ->select("id as idTypeSinistre, nom, description")
                ->where("statut", SinistresEntity::ACTIF)
                ->findAll();
        } else {
            $typeSinistres = model("SinistreTypesModel")->select("id as idTypeSinistre, nom, description")->findAll();
        }

        $typeSinistres = array_map(function ($s) {
            $s["idTypeSinistre"] = (int)$s["idTypeSinistre"];
            return $s;
        }, $typeSinistres);

        $response = [
            'status' => 'ok',
            'message' => count($typeSinistres) ? 'Type(s) de sinistre(s) trouvé(s).' : 'Aucun type de sinistre trouvé.',
            'data' => $typeSinistres ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Renvoie les détails d'une déclaration de sinistre
     *
     * @param  mixed $id identifiant où code de la déclaration de sinistre
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $sinistre   = model("SinistresModel")->where($identifier['name'], $identifier['value'])->first();
        $sinistre->images;
        $sinistre->documents;
        $response   = [
            'statut'  => 'ok',
            'message' => $sinistre ? 'Détails du sinistre.' : "Impossible de trouver les détails de ce sinistre.",
            'data'    => $sinistre ?? [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Ajoute une déclaration de sinistre
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create($declarant = null)
    {
        /*
            Le sinistre est un incicdent malheureux couvert par l'assurance.
            Déclarer un sinistre reviens à faire une descriptoin des faits
            et au besoin y joindre des documents et images justificatifs.
            Aussi, cette déclaration ouvre une conversation.
        */
        $rules = [
            'sujet'          => ['rules' => 'required', 'errors' => ['required' => 'Le sujet est obligatoire']],
            'description'    => ['rules' => 'required', 'errors' => ['required' => 'La description est obligatoire']],
            'idTypeSinistre' => [
                'rules'   => 'required|is_not_unique[sinistre_types.id]',
                'errors'  => [
                    'required' => 'Le type est obligatoire.',
                    'is_not_unique' => "Le type spécifié n'est pas reconnu.",
                ]
            ],
            'idSouscription' => [
                'rules'      => 'required|is_not_unique[souscriptions.id]',
                'errors'     => [
                    'integer' => "La souscription spécifiée n'est pas reconnue.",
                    'is_not_unique' => "La souscription spécifiée n'est pas reconnue.",
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
        $codeSinistre = $this->generateCodeSinistre();
        $conversationInfo = [
            "nom"     => "Reclamations-$codeSinistre",
            "description" => "Échanges pour la résolution du sinistre $codeSinistre",
            "type" => ConversationEntity::TYPE_SINISTRE,
        ];
        model("SinistresModel")->db->transBegin();
        $convId = model("ConversationsModel")->insert($conversationInfo);
        model("ConversationMembresModel")->insert([
            "conversation_id" => $convId,
            "membre_id" => $declarant->id,
            "isAdmin" => false
        ]);

        $sinistreInfo = [
            "code"  => $codeSinistre,
            "titre" => $input['sujet'],
            "description" => $input['description'],
            "auteur_id" => $declarant->id,
            "type_id" => (int)$input["idTypeSinistre"],
            "souscription_id" => $input["idSouscription"],
            "conversation_id" => $convId,
        ];
        $sinistreId = model("SinistresModel")->insert($sinistreInfo);
        model("SinistresModel")->db->transCommit();

        // Enregistrer les fichiers joints au cas échéant
        // $images    = $this->request->getFiles("images");
        // $documents = $this->request->getFiles("documents");
        $files     = $this->request->getFiles();
        $images    = $files["images"] ?? [];
        $documents = $files["documents"] ?? [];
        // print_r($images);
        // print_r($documents);
        foreach ($images as $img) {
            $imgID = saveImage($img, 'uploads/sinistres/images/');
            // echo "\nImage: $imgID";
            model("SinistreImagesModel")->insert(["sinistre_id" => $sinistreId, "image_id" => $imgID]);
        }

        foreach ($documents as $doc) {
            $title = $doc->getClientName();
            $docID = saveDocument($title, $doc, 'uploads/sinistres/documents/');
            // echo "\nDocument: $docID";
            model("SinistreDocumentsModel")->insert(["sinistre_id" => $sinistreId, "document_id" => $docID]);
        }
        // exit;
        $response = [
            'statut'  => 'ok',
            'message' => 'Déclaration Enregistrée.',
            'data'    => ["idSinistre" => $sinistreId],
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    private function generateCodeSinistre()
    {
        $maxId = model("SinistresModel")->orderBy("id", "DESC")->findColumn("id")[0] ?? 0;
        $num   = str_pad($maxId + 1, 5, "0", STR_PAD_LEFT);
        return "S" . substr(date('Y'), 2) . $num;
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
            'idTypeSinistre' => [
                'rules'   => 'if_exist|is_not_unique[sinistre_types.id]',
                'errors'  => ['is_not_unique' => "Le type spécifié n'est pas reconnu.",]
            ],
        ];
        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception("Veuillez corriger les erreurs suivantes: " . $this->validator->getErrors());
            }

            $input = $this->getRequestInput($this->request);

            // Ouverture de la conversation ou réutilisation
            $sinistreInfo = [
                "titre" => $input['sujet'] ?? null,
                "description" => $input['description'] ?? null,
                "type_id" => (int)$input["idTypeSinistre"] ?? null,
            ];
            model("SinistresModel")->update($id, array_filter($sinistreInfo));
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
                'message' => $validationError ? $errorsData['errors'] : "Impossible de mettre à jour ce sinistre.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Déclaration de sinistre Mise à jour.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Supprimme la déclaration de sinistre identifiée
     *
     * @param  mixed $id identifiant où code de la déclaration de sinistre
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
        $sinistre = model("SinistresModel")->find($id);
        model("SinistresModel")->delete($id);
        model("SinistreImagesModel")->where('sinistre_id', $id)->delete();
        model("SinistreDocumentsModel")->where('sinistre_id', $id)->delete();
        $convName = 'Reclamations-' . $sinistre->code;
        model("ConversationsModel")->where('nom', $convName)->delete();

        $response = [
            'statut'  => 'ok',
            'message' => 'Déclaration de sinistre Supprimée.',
        ];
        return $this->sendResponse($response);
    }

    public function setSinistreImages($id)
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
                    $imgID = saveImage($img, 'uploads/sinistres/images/');
                    model("SinistreImagesModel")->insert(["sinistre_id" => $id, "image_id" => $imgID]);
                }
            }
            if (isset($input['url'])) {
                $imgID = model("ImagesModel")->insert(["uri" => $input["url"], "isLink" => true]);
                model("SinistreImagesModel")->insert(["sinistre_id" => $id, "image_id" => $imgID]);
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
            'message' => "Image ajoutée à la déclaration de sinistre.",
            'data'    => [],
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the Images of the identified sinstre declaration
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getSinistreImages($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $sinistre  = model("SinistresModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $sinistre->images;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Images de la déclaration de sinistre." : "Aucune image pour cette déclaration de sinistre.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    public function delSinistreImage($id, $imgId)
    {
        model("SinistreImagesModel")->where('sinistre_id', $id)->where("image_id", $imgId)->delete();

        $response = [
            'statut'  => 'ok',
            'message' => 'Image de sinistre Supprimée.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve the Documents provided for the identified sinistre declaration
     *
     * @param  mixed $id
     * @return ResponseInterface The HTTP response.
     */
    public function getSinistreDocuments($id)
    {
        $identifier = $this->getIdentifier($id, 'id');
        $sinistre   = model("SinistresModel")->where($identifier['name'], $identifier['value'])->first();
        $data       = $sinistre->documents;

        $response = [
            'statut'  => 'ok',
            'message' => $data ? "Document(s) joint(s) à la déclaration de sinistre." : "Aucun document join à cette déclaration de sinistre.",
            'data'    => $data,
        ];
        return $this->sendResponse($response);
    }

    public function setSinistreDocument($id)
    {
        $rules = [
            'titre'    => ['rules' => 'required', 'errors' => ['required' => "Un titre est nécessaire pour enregistrer le document."]],
            'url'      => 'if_exist',
            'document' => 'if_exist|uploaded[document]',
        ];
        $input    = $this->getRequestInput($this->request);
        $document = $this->request->getFile('document');

        $model = model("SinistreDocumentsModel");
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
                $docID = saveDocument($input['titre'], $document, 'uploads/Sinistres/documents/');
            }
            $model->insert(["sinistre_id" => (int)$id, "document_id" => $docID]);

            $model->db->transCommit();
            $response = [
                'statut'  => 'ok',
                'message' => "Document(s) associé(s) au sinistre.",
                'data'    => [],
            ];
            return $this->sendResponse($response);
        } catch (\Throwable $th) {
            $model->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'associer ce(s) document(s) à la déclaratrion de sinistre.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
    }

    public function delSinistreDocument($id, $docId)
    {
        model("SinistreDocumentsModel")->where('sinistre_id', $id)->where("document_id", $docId)->delete();

        $response = [
            'statut'  => 'ok',
            'message' => 'Document de sinistre Supprimée.',
        ];
        return $this->sendResponse($response);
    }
}
