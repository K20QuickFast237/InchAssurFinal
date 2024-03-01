<?php

namespace Modules\Auth\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
// use Config\Services;

// use Auth\Models\ActionModel;
// use Auth\Models\ActionModuleModel;
// use Auth\Models\ConnexionModel;
// use Auth\Models\ProfilModel;
// use Auth\Models\ProfilactionModel;
// use Users\Models\UtilisateurModel;
// use Users\Models\UtilisateurProfilModel;
// use Paiement\Models\PortefeuilleModel;




use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use CodeIgniter\API\ResponseTrait;
// use CodeIgniter\CodeIgniter;
use CodeIgniter\Shield\Entities\User;
use Modules\Utilisateurs\Entities\UtilisateursEntity;
use CodeIgniter\Events\Events;
use Modules\Utilisateurs\Entities\PortefeuillesEntity;
use Modules\Utilisateurs\Entities\ProfilsEntity;

class Auth extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['text', 'Modules\Images\Images', 'sms', 'jwt', 'email'];

    protected const VALIDITE = 3600 * 24;

    public function sendTestMessage()
    {
        // testSendSMS();
        $msg  = "Parametres Stockés dans l'environnement";
        // $msg  = "Merci d'avoir participé à notre test d'envoi de sms encore";
        $dest = ["237676233273"];
        // $dest = ["237653741031", "237676233273"];
        $response = sendSmsMessage($dest, "InchAssur", $msg);

        return $this->sendResponse($response);
    }

    /** @todo penser à attribuer le profil par défaut en fonction de la donnée reçue
     * for registration
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
        // Get the validation rules
        $config = config('Validation');
        $rules  = $config->registration;

        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
            // unset($input['passwordConfirm']);
            $input['nom']      = strtoupper($input['nom']);
            $input['prenom']   = ucfirst($input['prenom']);
            $input["code"]     = random_string('alnum', 10);
            $input['username'] = $input["nom"] . ' ' . $input['prenom'];
            $codeConnect       = strtoupper(random_string("alnum", 6));
            $input['codeconnect'] = $codeConnect;
            // Get the User Provider (UserModel by default)
            $users = auth()->getProvider();

            $user = new User($input);

            model("UtilisateursModel")->db->transBegin();
            $users->save($user);

            // To get the complete user object with ID, we need to get from the database
            $user = $users->findById($users->getInsertID());
            $user->tel1 = $input['tel1'];


            $utilisateur = new UtilisateursEntity($input);
            $utilisateur->user_id = $user->id;

            $profil = model("ProfilsModel")->where("niveau", $input['categorie'])->first();
            $utilisateur->profil_id = $profil->id ?? ProfilsEntity::PARTICULIER_PROFIL_ID;

            $utilisateur->id = model("UtilisateursModel")->insert($utilisateur);
            // Add the profil to the user
            model("UtilisateurProfilsModel")->insert([
                "utilisateur_id" => $utilisateur->id,
                "profil_id"      => $profil->id,
                "attributor"     => $utilisateur->id,
            ]);
            // Add to selected group
            $user->addGroup('particulier', strtolower($profil->titre));

            model("ConnexionsModel")->where("user_id", $user->id)->set("codeconnect", $codeConnect)->update();
            model("UtilisateursModel")->db->transCommit();
            /** @var JWTManager $manager */
            $manager = service('jwtmanager');

            // Generate JWT and return to client
            $jwt = $manager->generateToken($user, ttl: MONTH);
        } catch (\Throwable $th) {
            model("UtilisateursModel")->db->transRollback();
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $response = [
                'statut'  => 'no',
                'message' => "Impossible de creer ce compte.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }
        try {
            Events::trigger('newRegistration', $user, $codeConnect, $jwt);
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'ok',
                'message' => 'Un mail d\'activation à été envoyé dans votre boite mail',
                'token'   => $jwt,
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $response = [
            'status'  => 'ok',
            'message' => 'Un mail d\'activation à été envoyé dans votre boite mail',
            'token'   => $jwt,
        ];
        return $this->sendResponse($response);
    }

    /**
     * codeConfirm(). 
     * 
     * utilisée pour confirmer l'inscription d'un utilisateur à partir du
     * code fourni en post et de l'email contenu dans le token d'authentification
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     */
    public function codeConfirm()
    {
        $input = $this->getRequestInput($this->request);

        if (!isset($input['code'])) {
            $response = [
                'statut' => 'no',
                'message' => 'Code abscent',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
        }

        $token   = $this->request->newToken;
        /** @var JWTManager $manager */
        $JWTmanager = service('jwtmanager');
        try {
            $JWTdata = $JWTmanager->parse($token);
            $userID  = $JWTdata->sub;
            $codeRef = model("ConnexionsModel")->where('user_id', $userID)->findColumn('codeconnect')[0];
        } catch (\Throwable $th) {
            $response = [
                'statut'  => 'no',
                'message' => 'Token Invalide.',
                'errors'  => $th->getMessage(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($codeRef === '000000') {
            $response = [
                'statut' => 'no',
                'message' => 'Ce compte est déjà vérifié.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        }

        $utilisateurID = $this->request->utilisateur->id;
        if ($codeRef == $input['code']) {
            model("ConnexionsModel")->db->transBegin();
            model("ConnexionsModel")->where('user_id', $userID)
                ->set('codeconnect', '000000')
                ->update();

            // Association d'un portefeuille vide à l'utilisateur
            $infoPortefeuille = new PortefeuillesEntity([
                'solde'  => 0,
                'devise' => 'XAF',
                'utilisateur_id' => $utilisateurID,
            ]);
            model("PortefeuillesModel")->insert($infoPortefeuille);
            $this->request->utilisateur->statut = 'Actif';
            model('UtilisateursModel')->save($this->request->utilisateur);

            model("ConnexionsModel")->db->transCommit();
            auth()->user()->activate();
            // $user = auth()->getProvider()->find($userID);
            // $user->activate();
            $response = [
                'statut'  => 'ok',
                'message' => 'confirmation validée',
                'token'   => $JWTmanager->generateToken($user),
            ];
            return $this->sendResponse($response);
        }
    }

    public function signIn()
    {
        // Get the validation rules
        $config = config('Validation');
        $rules  = $config->login;

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : "Connextion impossible.",
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $credentials = $this->getRequestInput($this->request);

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        // Check the credentials
        $result = $authenticator->check($credentials);

        // Credentials mismatch.
        if (!$result->isOK()) {
            // @TODO Record a failed login attempt
            $response = [
                'statut'  => 'no',
                'message' => "Identifiants incorrect.",
                'errors'  => $result->reason(),
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_PRECONDITION_FAILED);
        }

        // Credentials match.
        // @TODO Record a successful login attempt
        $user = $result->extraInfo();
        if (!$user->isActivated()) {
            $response = [
                'statut'  => 'no',
                'message' => "Consultez votre boite Mail et Activez votre compte.",
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_PRECONDITION_FAILED);
        }

        // Récupére les données détaillées de l'utilisateur afin de transmettre dans la réponse
        /* $utilisateur = model("UtilisateursModel")->where('user_id', $user->id)->first();
        $utilisateur->profils;
        $utilisateur->defaultProfil;
        */

        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        // Generate JWT and return to client
        $jwt = $manager->generateToken($user);

        $response = [
            'statut'  => 'ok',
            'message' => 'Connexion réussie',
            // 'data'    => ["profils" => $utilisateur->profils, "profil" => $utilisateur->defaultProfil],
            // 'data'    => $utilisateur,
            'data'    => [],
            'token'  => $jwt,
        ];
        return $this->sendResponse($response);
    }

    /**
     * initiatePwdReset()
     * 
     * Envoie à l'adresse passée en post, un email de redirection vers la page de 
     * réinitialisation de mot de passe
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     */
    public function initiatePwdReset()
    {
        $input = $this->getRequestInput($this->request);
        if (!isset($input['email'])) {
            $response = [
                'statut' => 'no',
                'message' => 'Saisissez votre email',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_RESET_CONTENT);
        }


        $utilisateur = model("UtilisateursModel")->where('email', $input['email'])->first();
        $user        = auth()->getProvider()->where('id', $utilisateur->user_id)->first();
        $user->email = $input['email'];
        if (empty($user)) {
            $response = [
                'statut' => 'no',
                'message' => 'Adresse email inconnue.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $code = model("ConnexionsModel")->where('user_id', $user->id)->findColumn("codeconnect")[0];

        if ($code !== '000000') {
            $response = [
                'statut' => 'no',
                'message' => 'Veuillez d\'abord vérifier votre compte.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        } else {
            $token = $this->sendResetPwdEmail($user);
            $response = [
                'statut'  => 'ok',
                'message' => 'un mail de réinitialisation a été envoyé',
                'token'   => $token,
            ];
            return $this->sendResponse($response);
        }
    }

    /**
     * resetPassword()
     * 
     * Met à jour le mot de passe de l'utilisateur dont l'email est 
     * contenu dans le token à partir des données du formulaire.
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function resetPassword()
    {
        $rules = [
            'password'     => [
                'rules' => 'required|min_length[8]',
                'errors' => ['required' => "Le mot de passe est requis.", "min_length" => "le mot de passe est trop court."],
            ],
            // 'passwordConfirm' => [
            //     'rules' => 'required_with[password]|matches[password]',
            //     'errors' => ["required_with" => 'La confirmation de mot de passe est requise', "matches" => "Le mot de passe et sa confirmation doivent être identiques."],
            // ],
        ];

        $input = $this->getRequestInput($this->request);

        try {
            if (!$this->validate($rules)) {
                $hasError = true;
                throw new \Exception();
            }
        } catch (\Throwable $th) {
            $errorsData = $this->getErrorsData($th, isset($hasError));
            $validationError = $errorsData['code'] == ResponseInterface::HTTP_NOT_ACCEPTABLE;
            $response = [
                'statut'  => 'no',
                'message' => $validationError ? $errorsData['errors'] : 'Mot de passe non conforme.',
                'errors'  => $errorsData['errors'],
            ];
            return $this->sendResponse($response, $errorsData['code']);
        }

        $user = auth()->user();
        $user->password = $input["password"];
        auth()->getProvider()->save($user);

        $response = [
            'statut'  => 'ok',
            'message' => 'Mot de passe modifié.',
        ];
        return $this->sendResponse($response);
    }

    /**
     * resetCodeConfirm()
     * 
     * utilisée pour renvoyer le code de confirmation d'un utilisateur à
     * partir de l'email contenu dans le token d'authentification
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     */
    public function resetCodeConfirm()
    {
        $codeConnect = strtoupper(random_string("alnum", 6));
        $user        = auth()->user();
        $user->email = $this->request->utilisateur->email;
        $code = model("ConnexionsModel")->where('user_id', $user->id)->findColumn("codeconnect")[0];

        if ($code == '000000') {
            $response = [
                'statut' => 'no',
                'message' => 'Ce compte est déjà confirmé.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_FORBIDDEN);
        }
        model("ConnexionsModel")->where('user_id', $user->id)->set("codeconnect", $codeConnect)->update();

        Events::trigger('newRegistration', $user, $codeConnect, $this->request->newToken);

        $response = [
            'statut'  => 'ok',
            'message' => 'un mail d\'acitivation a été envoyé',
        ];
        return $this->sendResponse($response);
    }

    /**
     * firstConnectDashbord()
     * 
     * Verifie qu'il s'agit de la première connexion d'un utilisateur
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     */
    public function firstConnectDashbord()
    {
        $user = auth()->user();
        $code = model("ConnexionsModel")->where('user_id', $user->id)->findColumn("codeconnect")[0];

        if ($code === '000000') {
            $response = [
                'statut'  => 'no',
                'message' => 'Ce compte est déjà vérifié.',
            ];
            return $this->sendResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
        } else {
            model("ConnexionsModel")->where('user_id', $user->id)->set('codeconnect', '000000')->update();

            $response = [
                'statut'  => 'ok',
                'message' => 'confirmation validée',
                'data'    => $this->request->utilisateur->profils,
            ];
            return $this->sendResponse($response);
        }
    }

    /**
     * deconnexion()
     * 
     * déconnecte un utilisateur connecté
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     * 
     */
    public function deconnexion()
    {
        // auth()->logout();
        auth('session')->logout();
        $response = [
            'statut'  => 'ok',
            'message' => 'Déconnection Réussie!!'
        ];
        return $this->sendResponse($response);
    }

    /**
     * sendResetPwdEmail ($recipients)
     * 
     * @param recipients string 
     * @var recipients adresse email de destination
     * @return jsonWebToken|false
     */
    private function sendResetPwdEmail(user $recipient)
    {
        // generation du token du lien
        $manager = service('jwtmanager');
        $token   = $manager->generateToken($recipient, ttl: MONTH);

        $email = emailer()->setFrom(setting('Email.fromEmail'), setting('Email.fromName') ?? '');
        $email->setTo($recipient->email);
        $email->setCC(['ibikivan1@gmail.com', 'tonbongkevin@gmail.com']);
        $email->setSubject('Password Reset');
        $email->setMessage(view(
            setting('Auth.views')['pwdReset_email'],
            [
                'date'          => \CodeIgniter\I18n\Time::now()->toDateTimeString(),
                'token'         => $token,
                'front_baseURL' => getenv("FRONTBASEURL"),
            ]
        ));
        $tentative = 0;
        while ($tentative < 3) {
            try {
                $email->send();
                return $token;
            } catch (\Exception $e) {
                log_message('warning', $e->getMessage());
            }
            $tentative++;
        }
        return $token;
    }






    /*
        /**
         * create()
         * 
         * cree un nouvel utilisateur et lui renvoie un code de confirmation
         * pour valider son compte
         * 
         * @return CodeIgniter\HTTP\ResponseInterface
         * 
         * /
        public function createByUser()
        {
            /* On doit:
                - Creer un utilisateur avec les informations minimales
                - En registrer sa connexion
                - Lui attibuer un profil particulier
                - creer son portefeuille
            * /
            /* Après le lien on va:
                - Completer les donnees utilisateur
                - Mettre à jour les informations de connexion avec le mot de passe et le code
                Note après utilisation de cette route, la route isregistered peut permettre de vérifier le lien avant la 
                redirection vers le changement de mot de passe, suivi d'une redirection vers la modification des infos 
                de l'utilisateur.
            * /
            $rules = [
                'nom'     => ['rules' => 'required', 'errors' => ['required' => 'Valeur Inappropriée.']],
                'prenom'  => ['rules' => 'required', 'errors' => ['required' => 'Valeur Inappropriée.']],
                'email'   => [
                    'rules' => 'required|min_length[6]|max_length[50]|valid_email|is_unique[IA_utilisateurs.email]',
                    'errors' => ['valid_email' => 'Email invalide', 'is_unique' => 'Email déjà existant.', 'required' => 'Valeur Inappropriée.']
                ],
                'tel1'    => [
                    'rules' => 'required|is_unique[IA_utilisateurs.tel1]',
                    'errors' => ['is_unique' => 'Téléphone déjà existant.', 'required' => 'Valeur Inappropriée.']
                ],
            ];

            if (!$this->validate($rules)) {
                $response = [
                    'statut' => 'no',
                    'message' => $this->validator->getErrors(),
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
            }

            $input = $this->getRequestInput($this->request);
            $input['email'] = strtolower($input['email']);
            if (isset($input['email']) && isset($input['tel1'])) {
                $modelUser  = new UtilisateurModel();
                $this->db = $modelUser->db;

                //sauvegarde Utilistateur et reccupération de l'email
                $UsersInfos = [
                    'nom'   => $input['nom'],
                    'prenom' => $input['prenom'],
                    'email' => $input['email'],
                    'code'  => $this->generatecodeUser($modelUser),
                    'statut' => UtilisateurModel::INACTIF,
                    'etat'  => UtilisateurModel::OFFLINE,
                    'tel1'  => (int)str_replace(' ', '', $input['tel1'])
                ];

                $nomComplet = $UsersInfos['nom'] . " " . $UsersInfos['prenom'];
                $this->db->transBegin();
                $userID = $modelUser->insert($UsersInfos);
                $UserEmail = $UsersInfos['email'];
                // generation et envoi de mail
                $codeconnect = $this->generateCode(6);
                $token = $this->sendmail($UserEmail, $nomComplet, $codeconnect);

                $UserConnexion = [
                    'tel'           => $UsersInfos['tel1'],
                    'email'         => $UsersInfos['email'],
                    'utilisateur_id' => $userID,
                    'codeconnect'   => $codeconnect

                ];
                $modelConnexion = new ConnexionModel();
                $modelConnexion->save($UserConnexion);

                // Utilisateur profil sauvegarde
                $this->createProfil($userID, 'IA1');
                // Association d'un portefeuille vide à l'utilisateur
                $portefeuilleModel = new PortefeuilleModel();
                $infoPortefeuille = [
                    'solde' => 0,
                    'devise' => 'XAF',
                    'utilisateur_id' => $userID,
                ];
                $portefeuilleModel->insert($infoPortefeuille);
                $this->db->transCommit();

                $this->sendAccountCreatedMail($UserEmail, $nomComplet);
                $response = [
                    'statut'  => 'ok',
                    'message' => 'Compte crée.',
                    'data'    => ['code' => $UsersInfos['code']],
                    'token'   => $this->request->newToken ?? '',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
            }
        }

        /**
         * initiatePwdReset()
         * 
         * Envoie à l'adresse passée en post, un email de redirection vers la page de 
         * réinitialisation de mot de passe
         * 
         * @return \CodeIgniter\HTTP\ResponseInterface
         * 
         * /
        public function initiatePwdReset2()
        {
            helper('jwt');

            $input = $this->getRequestInput($this->request);
            if (!isset($input['email'])) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Saisissez votre email',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_RESET_CONTENT);
                // return json_encode($response);
            }


            $connexion = new ConnexionModel();
            $userConnexion = $connexion->where('email', $input['email'])->first();

            if (empty($userConnexion)) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Adresse email inconnue.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }

            $recipients = $input['email'];
            $code = $userConnexion->codeconnect;

            if ($code !== '000000') {
                $response = [
                    'statut' => 'no',
                    'message' => 'Veuillez d\'abord vérifier votre compte.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_FORBIDDEN);
            } else {
                $token = $this->sendResetPwdEmail($recipients);
                $response = [
                    'statut'  => 'ok',
                    'message' => 'un mail de réinitialisation a été envoyé',
                    'token'   => $token,
                ];
                return $this->getResponse($response);
            }

            $response = [
                'statut' => 'no',
                'message' => 'erreur inconnue',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_SERVICE_UNAVAILABLE);
        }



        /**
         * firstConnectDashbord()
         * 
         * Verifie qu'il s'agit de la première connexion d'un utilisateur
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         * 
         * /
        public function firstConnectDashbord2()
        {
            $connexion     = new ConnexionModel();
            $userConnexion = $connexion->where('email', $this->request->userEmail)->first();

            if (!$userConnexion) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Ce lien est corrompu.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
            }

            if ($userConnexion->codeconnect === '000000') {

                $response = [
                    'statut' => 'no',
                    'message' => 'Ce compte est déjà vérifié.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_BAD_REQUEST);
            } else {
                $connexion->update($userConnexion->id_connexion, ['codeconnect' => '000000']);
                $profils = $this->getUserProfils($this->request->userEmail);

                $response = [
                    'statut' => 'ok',
                    'message' => 'confirmation validée',
                    'data'   => ['profils' => $profils],
                    'token'  => $this->request->newToken,
                ];
                return $this->getResponse($response);
            }

            $response = [
                'statut' => 'no',
                'message' => 'erreur inconnue',
            ];
            return $this->getResponse($response, ResponseInterface::HTTP_SERVICE_UNAVAILABLE);
        }

        /**
         * addModule
         * 
         * Ajoute un module
         *
         * @return \CodeIgniter\HTTP\ResponseInterface
         * /
        public function addModule()
        {
            $rules = [
                'nomModule' => 'required|is_unique[IA_actions.nomModule]',
            ];
            $input = $this->getRequestInput($this->request);
            if (!$this->validateRequest($input, $rules)) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Module déjà existant.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_CONFLICT);
            } else {
                if (isset($input['nomModule'])) {
                    $modelAction = new ActionModel();
                    $ActionInfos = [
                        'nomModule'   => $input['nomModule'],
                        'description' => $input['description'] ?? null,
                        'statut'      => ActionModel::INACTIVE_ACTION,
                    ];
                    $modelAction->save($ActionInfos);
                    // $data['ActionInfos'] = $ActionInfos;
                    // $data['allAction']=json_decode(json_encode($modelAction->findall()), true);
                    $response = [
                        'statut'  => 'ok',
                        'message' => 'Action ajoutée.',
                        'data'    => $ActionInfos,
                        'token'   => $this->request->mewToken,
                    ];
                    return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
                }
            }
        }

        /**
         * activateModule
         *
         * Active un module de la plateforme
         * 
         * @param  int $id_action
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function activateModule($id_action)
        {
            $actions = new ActionModel();
            try {
                // Normalement à mettre dans une transaction
                $actions->set('statut', ActionModel::ACTIVE_ACTION)
                    ->where('id_action', $id_action)
                    ->update();

                $response = [
                    'statut' => 'ok',
                    'message' => 'Module Activé.',
                ];
                return $this->getResponse($response);
            } catch (\Throwable $th) {
                $response = [
                    'statut' => 'no',
                    'message' => 'une erreur inconnue.',
                    'errors' => $th->getMessage(),
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        /**
         * desactivateModule
         *
         * Desactive un module de la plateforme
         * 
         * @param  int $id_action
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function desactivateModule($id_action)
        {
            $actions = new ActionModel();
            try {
                // Normalement à mettre dans une transaction
                $actions->set('statut', ActionModel::INACTIVE_ACTION)
                    ->where('id_action', $id_action)
                    ->update();

                $response = [
                    'statut' => 'ok',
                    'message' => 'Module Désactivé.',
                ];
                return $this->getResponse($response);
            } catch (\Throwable $th) {
                $response = [
                    'statut' => 'no',
                    'message' => 'une erreur inconnue.',
                    'errors' => $th->getMessage(),
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
        }

        /**
         * allModules
         * 
         * Renvoie la liste des modules.
         * 
         * @return CodeIgniter\HTTP\ResponseInterface
         * 
         * @OA\Schema(
         *  schema="ModuleDataResponse",
         *  description="Données des actions d'un module",
         *  type="object",
         *  title="Action",
         *  @OA\Property(property="id_module", type="integer"),
         *  @OA\Property(property="nomModule", type="string", example="un nom d'un module"),
         *  @OA\Property(property="description", type="string", example="une brêve description"),
         *  @OA\Property(property="statut", type="integer"),
         * )
         * /
        public function allModules()
        {
            $action = new ActionModel();
            $data = $action->findall();
            $data = array_map(function ($element) {
                $id = $element['id_action'];
                unset($element['id_action']);
                return array_merge(['id_module' => $id], $element);
            }, $data);
            //    if (is_null($data)) { // this case is for a not value in database
            //         $response = [
            //             'statut'        => 'no',
            //             'message'       => 'Aucune Action trouvée!!',
            //         ];
            //         return $this->getResponse($response, ResponseInterface::HTTP_ACCEPTED);
            //     }else{
            $response = [
                'statut'  => 'ok',
                'message' => count($data) ? count($data) . ' Module(s) trouvé(s)' : 'Aucun Module trouvé!!',
                'data'    => $data,
                // 'token'   => $this->request->newToken,
            ];
            return $this->getResponse($response);
            // }

        }

        /**
         * getModuleActions
         *
         * retourne toutes les actions du module dont l'identifiant est spécifié.
         * 
         * @param  int $module
         * @return CodeIgniter\HTTP\ResponseInterface
         * 
         * @OA\Schema(
         *  schema="ActionModuleDataResponse",
         *  description="Données des actions d'un module",
         *  type="object",
         *  title="Action",
         *  @OA\Property(property="id", type="integer"),
         *  @OA\Property(property="action", type="string", example="un nom d'action"),
         *  @OA\Property(property="description", type="string", example="une brêve description"),
         * )
         * 
         * /
        public function getModuleActions(int $module)
        {
            $actionModule = new ActionModuleModel;

            $data = $actionModule->AllSousaction($module);

            if ($data) {
                $message = count($data) . ' Action(s) trouvée(s).';
            }
            $response = [
                'statut'  => 'ok',
                'message' => $message ?? 'Aucune action pour ce module!!',
                'data'    => $data ?? []
            ];
            return $this->getResponse($response);
        }

        /**
         * updateModule
         * 
         * Met à jour un module en fonction des données recues en post.
         *
         * @param  int $id_action
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function updateModule(int $id_action)
        {
            $input = $this->getRequestInput($this->request);

            if (isset($input['nomModule'])) {
                $data['nomModule'] = $input['nomModule'];
            }
            if (isset($input['description'])) {
                $data['description'] = $input['description'];
            }

            $Action = new ActionModel();
            $Action->update($id_action, $data);
            // $data['allAction']=json_decode(json_encode($Action->findall()), true);
            $response = [
                'statut'  => 'ok',
                'message' => 'Action Modifée.',
                'data'    => $data,
                'token'   => $this->request->newToken,
            ];
            return $this->getResponse($response);
        }

        /**
         * addActionModule
         *
         * Ajoute une action au module dont l'identifiant est spécifié en paramètre.
         * 
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function addActionModule(int $actionID)
        {
            $rules = [
                'nomsousaction ' => 'required|is_unique[IA_actionmodules.nomsousaction]',
            ];
            $input = $this->getRequestInput($this->request);
            if (!$this->validateRequest($input, $rules)) {
                $response = [
                    'statut' => 'no',
                    'message' => 'Action déja existante pour ce module.',
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_CONFLICT);
            } else {
                if (isset($input['nomsousaction'])) {
                    $ModuleAction = new ActionModuleModel();
                    $ActionModuleInfos = [
                        'nomsousaction' => (string)htmlspecialchars($input['nomsousaction']),
                        'description'   => (string)htmlspecialchars($input['description']),
                        'action_id'     => $actionID,
                        'statut'        => ActionModel::ACTIVE_ACTION,
                    ];
                    $ModuleAction->save($ActionModuleInfos);
                    $data['ActionModuleInfos'] = $ActionModuleInfos;
                    $data['allActionModule'] = json_decode(json_encode($ModuleAction->findall()), true);
                    $response = [
                        'statut'  => 'ok',
                        'message' => 'Action Ajoutée au module.',
                        'data'    => $data,
                        'token'   => $this->request->newToken,
                    ];
                    return $this->getResponse($response, ResponseInterface::HTTP_CREATED);
                }
            }
        }

        /**
         * updateActionModule
         * 
         * Met à jour l'action dont l'identifiant est passé en paramètre.
         *
         * @param  int $id_actionmodule
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function updateActionModule(int $id_actionmodule)
        {
            $input = $this->getRequestInput($this->request);
            $Actionmodule = new ActionModuleModel();
            if (isset($input['nomsousaction'])) {
                $data['nomsousaction'] = htmlspecialchars($input['nomsousaction']);
            }
            if (isset($input['description'])) {
                $data['description'] = htmlspecialchars($input['description']);
            }

            $Actionmodule->update($id_actionmodule, $data);
            // $data['allAction']=json_decode(json_encode($Actionmodule->findall()), true);
            $response = [
                'statut'  => 'ok',
                'message' => 'Action Modifée.',
                'data'    => $data,
                // 'token'   => $this->request->newToken,
            ];
            return $this->getResponse($response);
        }

        /**
         * addModuleByProfileUser
         * 
         * Ajoute des actions à un profil même si ce profil avait déja au préalable
         * des actions définies.
         * 
         * @return CodeIgniter\HTTP\ResponseInterface
         * /
        public function addModuleByProfileUser(int $profilID)
        {
            /*
                -recupere líd du profil à partir du champ profil contenant  le niveau (IA...)
                -recupere en bd tous les action_id de ce profil
                -parcoure la data recu et genere deux tableaux (to update & to add)
                    - to update ceux dont l'id action est dans la liste recupérée en bd    et les autres à to add 
                -faire le insert et le update dans le cas de tableaux non vides
            //* /
            $profilActionModel = new ProfilactionModel();
            $input = $this->getRequestInput($this->request);

            try {
                $currentActions = $profilActionModel->where('profil_id', $profilID)->findColumn('action_id');
                $data   = $input['data'];
                for ($i = 0; $i < count($input['data']); $i++) {
                    $actionID = (int)$data[$i]->id_action;
                    $actions  = $data[$i]['action'];
                    for ($i = 0; $i < count($actions); $i++) {
                        $listeAction[] = $actions[$i]->id;
                    }
                    if (in_array($actionID, $currentActions)) {
                        $profilActionModel->where('action_id', $actionID)
                            ->where('profil_id', $profilID)
                            ->set('liste_action', $listeAction)
                            ->update();
                    } else {
                        $insert = [
                            'action_id'    => $actionID,
                            'profil_id'    => $profilID,
                            'liste_action' => $listeAction,
                            'status'       => 1,
                        ];
                        $profilActionModel->insert($insert);
                    }
                }
            } catch (\Exception $e) {
                $response = [
                    'statut' => 'no',
                    'message' => 'une erreur empêche le traitement de la requette',
                    'errors' => $e->getMessage(),
                ];
                return $this->getResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
            }
            $response = [
                'statut' => 'ok',
                'message' => 'Actions Ajoutées',
                'token'  => $this->request->newToken,
            ];
            return $this->getResponse($response);
        }




        // Fonctions Utilitaires 
        /**
         * sendmail($recipients, $nomComplet, $codeconnect)
         * 
         * À la création de compte,
         * Envoie un email contenant le code de validation de compte.
         * 
         * @param string $recipients Email de l'utilisateur.
         * @param string $nomComplet Nom de l'utilisateur.
         * @param string $codeconnect code de validation à 6 caractères alphanumeriques.
         * @return jsonWebToken|false
         * /
        private function sendmail($recipients, $nomComplet, $codeconnect)
        {
            $email = Services::email();
            $front_baseURL = Services::getforntBaseUrl();

            // generation du token du lien
            helper('jwt');
            $token = makePersonalisedToken(['email' => $recipients], 3600 * 24 * 90);
            // $email->setSMTPHost(['nsangouassanzidan@gmail.com','tonbongkevin@gmail.com']); 

            $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
            $email->setTo($recipients);
            $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
            $email->setSubject('Validation de Compte');
            $email->setMessage("<h2>Hello " . $nomComplet . " Bienvenue sur IncH Assurance.</h2>
                                <br> Code d'acces : " . $codeconnect . " <br>
                                <a href='" . $front_baseURL . "verifcode?token=" . $token . "&code=" . $codeconnect . "'>Activer votre compte maintenant!!</a>");
            // <a href='".$front_baseURL."auth/verifcode?code=".$codeconnect."&token=".$token."'>Activer votre compte maintenant!!</a>");
            $tentative = 0;
            while ($tentative < 3) {
                try {
                    $email->send();
                    return $token;
                } catch (\Exception $e) {
                    log_message('warnig', $e->getMessage());
                }
                $tentative++;
            }
            return false;
        }

        /**
         * sendAccountCreatedmail($recipients, $nomComplet, $codeconnect)
         * 
         * À la création de compte, par un autre utilisateur
         * Envoie un email contenant un lien pour définir son mot de passe et compléter les informations de son compte.
         * 
         * @param string $recipients Email de l'utilisateur.
         * @param string $nomComplet Nom de l'utilisateur.
         * @return jsonWebToken|false
         * /
        private function sendAccountCreatedMail($recipient, $nomComplet)
        {
            $email = Services::email();
            $front_baseURL = Services::getforntBaseUrl();

            // generation du token du lien
            helper('jwt');
            $token = makePersonalisedToken(['email' => $recipient], 3600 * 24 * 90);
            // $email->setSMTPHost(['nsangouassanzidan@gmail.com','tonbongkevin@gmail.com']); 

            $email->setFrom('nanguedevops@gmail.com', 'IncH Assurance');
            $email->setTo($recipient);
            $email->setCC(['nsangouassanzidan@gmail.com', 'tonbongkevin@gmail.com']);
            $email->setSubject('Validation de Compte');
            $email->setMessage("<h2>Hello " . $nomComplet . " Bienvenue sur IncH Assurance.</h2>
                                <br>Veuillez suivre ce lien afin de finaliser la création de votre compte.<br>
                                <a href='" . $front_baseURL . "/maison/" . $token . "'>Je finalise la création de mon Compte.</a>");
            $tentative = 0;
            while ($tentative < 3) {
                try {
                    $email->send();
                    return $token;
                } catch (\Exception $e) {
                    log_message('warnig', $e->getMessage());
                }
                $tentative++;
            }
            return false;
        }

        /**
         * public function generatecodeUser()
         * 
         * Génère un code unique pour chaque utilisateur crée
         *
         * @return string code
         * /
        private function generatecodeUser($userModel)
        {
            $codePrefixe = Services::getDBPrefix() ?? "";
            $codegenerate = $codePrefixe . $this->generateCode(8);
            $verif_exist = $userModel->where('code', $codegenerate)->first();
            if ($verif_exist === null) {
                return $codegenerate;
            } else {
                $this->generatecodeUser($userModel);
            }
        }

        /**
         * generateCode()
         * 
         * genere un code alphanumérique aléatoire dont la longeur est spécifié en paramètre
         * 
         * @param length_of_string int 
         * @return string code génére
         * /
        private function generateCode($length_of_string)
        {
            // String of acepted alphanumeric character
            $str_result = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            // Shuffle the $str_result and returns substring of specified length
            return substr(str_shuffle($str_result), 0, $length_of_string);
        }

        /**
         * addProfil(int $id, string $level)
         *
         * Vérifie qu'un profil appartienne au client spécifié par son iddentifiant
         * si non l'ajoute et retourne true
         * si oui retourne false
         *
         * @param  int $id
         * @param  string $level
         * @param  int $defaultProfil
         * @return int
         * /  //15 IA1    
        private function createProfil(int $id, string $level): int
        {
            $profil = new ProfilModel();
            $currentProfil = $profil->asArray()->where('niveau', $level)->first();

            if ($currentProfil === null) {
                $currentProfil = $profil->asArray()->first();
            }

            $userProfilModel = new UtilisateurProfilModel();
            $result = $userProfilModel->insert(['utilisateur_id' => $id, 'profil_id' => $currentProfil['id_profil'], 'defaultProfil' => 1]);

            return $result;
        }

        /**
         * getUserProfils
         *
         * retourne le tableau associatif titre=>niveau des différents profil de l'utilisateur
         * dont l'email est passé en paramètre
         * 
         * @param  string $email
         * @param  object $UserProfil le model utilisateurprofil
         * @param  object $Profil le model profil
         * @param  object $User le model utilisateur
         * @return array
         * /
        private function getUserProfils(string $email): array
        {
            $UserProfil = new UtilisateurProfilModel();
            $Profil = new ProfilModel();
            $User   = new UtilisateurModel();

            $idUser  = $User->getIdByEmail($email);
            $profils = $UserProfil->getUserProfilsId($idUser);
            $profils = $Profil->getProfilValues($profils);
            /* new Added block in observation
            for ($i=0; $i < count($profils); $i++) { 
                $temp = $profils[$i];
                $key  = array_keys($temp)[0];
                $profils[$i] = [];
                $profils[$i]['id'] = $temp[$key];
                $profils[$i]['value'] = $key;
            }
            //* /
            return $profils;
        }

        /**
         * getTokenUserProfil
         *
         * retourne le niveau du profil utilisateur pour le token en fonction de son email
         * 
         * @param  string $email
         * @return string
         * /
        private function getTokenUserProfil(string $email): string
        {
            $UserProfil = new UtilisateurProfilModel();
            $Profil = new ProfilModel();
            $User  = new UtilisateurModel();

            $idUser = $User->getIdByEmail($email);
            $defaultProfilId = $UserProfil->getDefaultProfilByid($idUser);
            $defaultProfil = $Profil->asArray()->where(['id_profil' => $defaultProfilId])->first();

            return $defaultProfil['niveau'];
        }

        private function hashpass(string $password): string
        {
            return hash("sha256", sha1($password));
        }

        public static function hashPassword(string $password)
        {
            return hash("sha256", sha1($password));
        }
        public function testDecodage(string $password)
        {
            return $this->getResponse(['password' => $password, 'hash' => $this->hashpass($password)]);
        }


        /**
         * getActionsProfil
         *
         * recupere les actions et sous actions d'un profil utilisateur
         * 
         * @param  int $idProfil l'identifiant du profil dont on recher cles actions
         * @return array
         * /
        static function getActionsProfil(int $idProfil): array
        {
            $actionModel  = new ActionModel();
            $profilModel  = new ProfilactionModel();
            $actionModule = new ActionModuleModel();
            // on recupere toutes les actions associées au profil
            $actions = $profilModel->asArray()
                ->where('profil_id', $idProfil)
                ->findAll();

            $allElements = [];
            $element = [];
            // on transforme les ids en valeurs 
            for ($i = 0; $i < count($actions); $i++) {

                $moduleID = $actions[$i]['action_id'];
                $moduleNom = $actionModel->where('id_action', $moduleID)
                    ->findColumn('nomModule');
                // print_r($actionNom); 
                $element['module'] = $moduleNom[0];

                $allActionVals = [];
                $allActions = json_decode(($actions[$i]['liste_action']));

                for ($j = 0; $j < count($allActions); $j++) {
                    $actionID = (int)$allActions[$j];

                    $actionName = $actionModule->where('id_actionmodule', $actionID)
                        ->findColumn('nomsousaction');
                    if ($actionName) {
                        $allActionVals[] = ['id' => $actionID, 'value' => $actionName[0]];
                    }
                }
                $element['total'] = count($allActionVals);
                $element['action'] = $allActionVals;

                $allElements[] = $element;
            }
            return $allElements;
        }
    */
}
