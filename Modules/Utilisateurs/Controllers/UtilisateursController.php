<?php

namespace  Modules\Utilisateurs\Controllers;

use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Utilisateurs\Entities\UtilisateursEntity;

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
    public function addMember()
    {
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
        $existed = model('UtilisateursModel')->select("code, nom, prenom, date_naissance, email, photo_profil, ville")
            ->where('nom', $input['nom'])
            ->where('prenom', $input['prenom'])
            ->where('date_naissance', $input['dateNaissance'])
            ->first();
        if ($existed) {
            $response = [
                'statut'  => 'no',
                'message' => "Un membre correspondant à ces données existe déja.",
                'data'    => array_merge(
                    ['email' => null, 'photoProfil' => null, 'ville' => null],
                    array_filter($existed->toArray())
                ),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_NOT_ACCEPTABLE);
        }
        $input['code']   = random_string('alnum', 10);
        $input['statut'] = 'Inactif';
        $userID = model("UtilisateursModel")->insert(new UtilisateursEntity($input));
        $input['id'] = model("UtilisateurMembresModel")->insert([
            "utilisateur_id" => 1,                   // Connected user Id
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
    public function getMember()
    {
        $connecteduserID = 1;
        $memberIDs = model("UtilisateurMembresModel")->where("utilisateur_id", $connecteduserID)->findColumn('membre_id');
        // $members   = model('UtilisateursModel')->asArray()->select("id, code, nom, prenom, email, photo_profil")
        $members   = model('UtilisateursModel')->select("id, code, nom, prenom, email, photo_profil")
            ->whereIn("id", $memberIDs)
            ->findAll();


        //  array_merge(
        //             ['email' => null, 'photoProfil' => null, 'ville' => null],
        //             array_filter($existed->toArray())
        //         )

        $response = [
            'statut'  => 'ok',
            'message' => 'Liste des membres.',
            'data'    => $members,
        ];
        return $this->sendResponse($response);
    }
}
