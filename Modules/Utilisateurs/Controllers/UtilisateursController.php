<?php

namespace  Modules\Utilisateurs\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Utilisateurs\Entities\ProfilsEntity;
use Modules\Utilisateurs\Entities\UtilisateursEntity;
use CodeIgniter\Events\Events;

class UtilisateursController extends ResourceController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['text', 'Modules\Images\Images'];

    /**
     * Retrieve all Users records in the database.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index()
    {
        $response = [
            'status' => 'ok',
            'message' => 'Utilisateurs disponibles.',
            // 'data' => model("CategorieProduitsModel")->select("nom, description, image_id, id")->findAll(),
        ];
        return $this->sendResponse($response);
    }

    /**
     * Add a member in the connected User members list.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function addMember($identifier = null)
    {
        $user = auth()->user();
        if ($identifier) {
            if (!$user->can('users.addUserMember')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($identifier);
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }

        $rules = [
            "nom"            => "required|string",
            "prenom"         => "required|string",
            "dateNaissance"  => "required|valid_date[Y-m-d]",
            "sexe"           => "if_exist|string",
            "profession"     => "if_exist|string",
            "email"          => "if_exist|valid_email|is_unique[utilisateurs.email]",
            "tel1"           => "if_exist|integer|min_length[6]",
            "tel2"           => "if_exist|integer|min_length[6]",
            "photo_profil"   => "if_exist",                         // file not needed
            "ville"          => "if_exist|string",
            "etatCivil"      => "if_exist|string",
            "nbr_enfant"     => "if_exist|integer",
            "specialisation" => "if_exist|string",
        ];

        $input = $this->getRequestInput($this->request);
        $img   = $this->request->getFile('photo_profil') ?? null;

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'ajouter ce membre.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        if ($img) {
            $input['photoProfil'] = getInfoImage($img, 'uploads/utilisateurs/images/');
        }
        $existed = model('UtilisateursModel')
            ->select("id, code, nom, prenom, date_naissance, email, photo_profil, ville")
            ->where('nom', $input['nom'])
            ->where('prenom', $input['prenom'])
            ->where('profil_id', ProfilsEntity::MEMBRE_PROFIL_ID)
            ->where('date_naissance', $input['dateNaissance'])
            ->first();
        if ($existed) {
            $userID = $existed->id;
        } else {
            $input['code']      = random_string('alnum', 10);
            $input['statut']    = 'Inactif';
            $input['profil_id'] = ProfilsEntity::MEMBRE_PROFIL_ID;
            $userID = model("UtilisateursModel")->insert(new UtilisateursEntity($input));
        }
        $input['id'] = model("UtilisateurMembresModel")->insert([
            "utilisateur_id" => $utilisateur->id,  // Connected user Id
            "membre_id"      => $userID,
        ]);

        $response = [
            'statut'  => 'ok',
            'message' => 'Membre Ajouté.',
            'data'    => $input,
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_CREATED);
    }

    /**
     * Retrieve all members of connected User.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function getMember($identifier = null)
    {
        $user = auth()->user();
        if ($identifier) {
            if (!$user->can('users.getUserMembers')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($identifier);
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }

        $members = array_map(fn ($user) => array_filter($user->toArray()), $utilisateur->membres);
        $response = [
            'statut'  => 'ok',
            'message' => $members ? 'Liste des membres.' : 'Aucun membre trouvé pour cet utilisateur.',
            'data'    => $members,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Retrieve all user's data needed for dashboard
     *
     * @return ResponseInterface The HTTP response.
     */
    public function dashboardInfos()
    {
        $utilisateur = $this->request->utilisateur;
        $response = [
            'statut'  => 'ok',
            'message' => 'Infos Dashboard.',
            'data'    => $utilisateur,
        ];
        return $this->sendResponse($response);
    }

    public function setDefaultProfil($identifier = null)
    {
        if ($identifier) {
            if (!auth()->user()->can('pockets.getUserPocket')) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action non authorisée pour ce profil.',
                ];
                return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }
            $identifier = $this->getIdentifier($identifier);
            $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        } else {
            $utilisateur = $this->request->utilisateur;
        }

        $rules = [
            'profil'  => [
                'rules'  => 'required|string|is_not_unique[profils.niveau]',
                'errors' => [
                    'required'      => 'Profil non identifiée.',
                    'string'        => 'Profil non identifiable.',
                    'is_not_unique' => 'Profil non reconnue.'
                ],
            ],
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible de définir ce profil par défaut.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $currProfils = model("UtilisateurProfilsModel")->where('utilisateur_id', $utilisateur->id)->findColumn("profil_id");
        $profil = model("ProfilsModel")->where("niveau", $input['profil'])->first();
        $condition = array_search($profil->id, $currProfils);
        if ($profil && !$condition) {
        }
        model("UtilisateursModel")->update($utilisateur->id, ["profil_id" => $profil->id]);

        $response = [
            'statut'  => 'ok',
            'message' => 'Profil par défaut modifié.',
            'data'    => $input,
        ];
        return $this->sendResponse($response);
    }

    /**
     * Attribute a profile to the identified User.
     *
     * @return ResponseInterface The HTTP response.
     */
    public function addprofil($identifier)
    {
        $identifier = $this->getIdentifier($identifier);
        $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
        if (!auth()->user()->can('users.addUserProfil')) {
            $response = [
                'statut'  => 'no',
                'message' => 'Action non authorisée pour ce profil.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        } elseif (!$utilisateur) {
            $response = [
                'statut'  => 'no',
                'message' => 'Utilisateur Inconnu.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $rules = [
            'profil'  => [
                'rules'  => 'required|string|is_not_unique[profils.niveau]',
                'errors' => [
                    'required'      => 'Profil non identifiée.',
                    'string'        => 'Profil non identifiable.',
                    'is_not_unique' => 'Profil non reconnue.'
                ],
            ],
        ];
        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception('');
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible d'attribuer ce Profil.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        $currProfils = model("UtilisateurProfilsModel")->where('utilisateur_id', $utilisateur->id)->findColumn("profil_id");
        $profil = model("ProfilsModel")->where("niveau", $input['profil'])->first();
        $condition = array_search($profil->id, $currProfils);
        if ($profil && !$condition) {
            $input['id'] = model("UtilisateurProfilsModel")->insert([
                "utilisateur_id" => $utilisateur->id,  // Connected user Id
                "profil_id"      => $profil->id,
                "attributor"     => $this->request->utilisateur->id,
            ]);
            try {
                Events::trigger('profilAttributed', $utilisateur, $profil,);
            } catch (\Throwable $th) {
            }
        }

        $response = [
            'statut'  => 'ok',
            'message' => 'Profil Attribué.',
            'data'    => ["id" => $profil->niveau, "value" => $profil->titre],
        ];
        return $this->sendResponse($response);
    }

    public function test()
    {
        $particulier = model("ParticuliersModel")->where("user_id", auth()->user()->id)->first();
        $assureur    = model("AssureursModel")->where("user_id", auth()->user()->id)->first();
        $admin       = model("AdministrateursModel")->where("user_id", auth()->user()->id)->first();

        echo "Assurueur: ";
        print_r($assureur->toArray());
        echo "\nAdmin: ";
        print_r($admin->toArray());
        echo "\nParticulier: ";
        print_r($particulier->toArray());
        exit;
    }
}
