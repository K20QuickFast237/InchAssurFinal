<?php

namespace Modules\Auth\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
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
            'statut'  => 'ok',
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

        $token = $this->request->newToken;
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
                'token'   => $JWTmanager->generateToken(auth()->user()),
                'token'   => $JWTmanager->generateToken(auth()->user()),
            ];
            return $this->sendResponse($response);
        }

        $response = [
            'statut'  => 'no',
            'message' => 'Code de validation incorrect',
        ];
        return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
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
}
