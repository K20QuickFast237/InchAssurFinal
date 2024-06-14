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
use CodeIgniter\Database\Exceptions\DataException;

class UtilisateursController extends ResourceController
{
  use ControllerUtilsTrait;
  use ResponseTrait;
  use ErrorsDataTrait;

  protected $helpers = ['text', 'Modules\Images\Images'];

  /**
   * Retrieve all Users records in the database.
   * Only users with admin rights can do this
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
    unset($utilisateur->user_id);
    $utilisateur->profils;
    $utilisateur->defaultProfil;
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

  public function getSouscriptions($identifier = null)
  {
    $user = auth()->user();
    if ($identifier) {
      if (!$user->can('users.getSubscriptions')) {
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

    $souscriptions = $utilisateurs->souscriptions;
    $response = [
      'statut'  => 'ok',
      'message' => $souscriptions ? 'Souscriptions trouvées.' : 'Aucune souscription trouvée pour cet utilisateur.',
      'data'    => $souscriptions,
    ];
    return $this->sendResponse($response);
  }

  public function test()
  {   /*
        echo base_url('paiements/notify');
        exit;
        $particulier = model("ParticuliersModel")->where("user_id", auth()->user()->id)->first();
        $assureur    = model("AssureursModel")->where("user_id", auth()->user()->id)->first();
        $admin       = model("AdministrateursModel")->where("user_id", auth()->user()->id)->first();

        echo "Assurueur: ";
        print_r($assureur->toArray());
        echo "\nAdmin: ";
        print_r($admin->toArray());
        echo "\nParticulier: ";
        print_r($particulier->toArray());
        exit;*/

    /*$jsonString = <<<'EOD'
        [
            {
              "titre": "Infirmier/infirmière",
              "description": "Fournit des soins médicaux de base aux patients, surveille leur état de santé et administre des médicaments sous la supervision d'un médecin."
            },
            {
              "titre": "Développeur/développeuse web",
              "description": "Conçoit, développe et maintient des sites web en utilisant des langages de programmation, des frameworks et des outils de développement."
            },
            {
              "titre": "Enseignant/enseignante",
              "description": "Éduque et enseigne aux élèves dans une variété de sujets, prépare des plans de cours, évalue les progrès des élèves et les aide à atteindre leurs objectifs éducatifs."
            },
            {
              "titre": "Avocat/avocate",
              "description": "Représente et conseille les clients dans des affaires juridiques, prépare des documents juridiques, plaide devant les tribunaux et négocie des accords."
            },
            {
              "titre": "Ingénieur/ingénieure en informatique",
              "description": "Conçoit, développe et teste des logiciels, des systèmes informatiques et des réseaux pour répondre aux besoins spécifiques des entreprises ou des organisations."
            },
            {
              "titre": "Médecin",
              "description": "Diagnostique et traite les maladies et les blessures, prescrit des médicaments, conseille sur les questions de santé et supervise les soins des patients."
            },
            {
              "titre": "Comptable",
              "description": "Gère les finances et les comptes d'une entreprise ou d'une organisation, prépare des états financiers, effectue des audits et fournit des conseils fiscaux."
            },
            {
              "titre": "Architecte",
              "description": "Conçoit des bâtiments et des structures en tenant compte à la fois des aspects esthétiques et fonctionnels, prépare des plans et supervise leur construction."
            },
            {
              "titre": "Journaliste",
              "description": "Recherche, rédige et présente des informations sur des événements actuels, des problèmes et des tendances pour les médias imprimés, électroniques ou en ligne."
            },
            {
              "titre": "Psychologue",
              "description": "Étudie le comportement humain, évalue et traite les problèmes émotionnels, mentaux et comportementaux, et fournit un soutien thérapeutique aux individus et aux groupes."
            },
            {
              "titre": "Policier/policière",
              "description": "Maintient l'ordre public, enquête sur les crimes, patrouille les quartiers et arrête les suspects en utilisant les lois et les procédures applicables."
            },
            {
              "titre": "Chef/cuisinier",
              "description": "Planifie et prépare des repas dans les restaurants, les hôtels ou d'autres établissements alimentaires, supervise le personnel de cuisine et assure la qualité des plats servis."
            },
            {
              "titre": "Électricien/électricienne",
              "description": "Installe, entretient et répare les systèmes électriques dans les bâtiments, les usines et d'autres installations, en veillant à leur bon fonctionnement et à leur sécurité."
            },
            {
              "titre": "Designer graphique",
              "description": "Crée des visuels attrayants et des éléments graphiques pour les entreprises, les marques et les projets, en utilisant des logiciels de conception et en suivant les tendances actuelles."
            },
            {
              "titre": "Secrétaire/administrateur",
              "description": "Assiste les gestionnaires et les professionnels en effectuant des tâches administratives telles que la gestion des appels téléphoniques, la planification des réunions et la tenue des dossiers."
            },
            {
              "titre": "Technicien/technicienne de laboratoire",
              "description": "Effectue des tests et des analyses sur des échantillons biologiques, chimiques ou physiques, en utilisant des équipements de laboratoire et en interprétant les résultats pour la recherche ou le diagnostic."
            },
            {
              "titre": "Traducteur/traductrice",
              "description": "Convertit des textes écrits d'une langue à une autre tout en préservant leur sens, leur style et leur contexte culturel, pour faciliter la communication entre les personnes de différentes langues."
            },
            {
              "titre": "Développeur/développeuse de jeux vidéo",
              "description": "Conçoit, programme et teste des jeux vidéo pour diverses plateformes, en travaillant en étroite collaboration avec des artistes, des concepteurs et d'autres membres de l'équipe de développement."
            },
            {
              "titre": "Agent immobilier/agent immobilier",
              "description": "Facilite l'achat, la vente ou la location de biens immobiliers en aidant les clients à trouver des propriétés, à négocier des contrats et à finaliser des transactions immobilières."
            },
            {
              "titre": "Entrepreneur/entrepreneuse",
              "description": "Lance et gère une entreprise ou un projet commercial, en prenant des décisions stratégiques, en mobilisant des ressources et en assurant la croissance et la rentabilité de l'entreprise."
            },
            {
              "titre": "Chirurgien/chirurgienne",
              "description": "Effectue des opérations chirurgicales pour traiter les maladies, les blessures ou les anomalies physiques, en utilisant des techniques chirurgicales avancées et en assurant le suivi post-opératoire des patients."
            },
            {
              "titre": "Conseiller/conseillère en orientation",
              "description": "Fournit des conseils professionnels aux individus sur leur carrière, leur éducation et leur développement personnel, en évaluant leurs intérêts, leurs compétences et leurs objectifs."
            },
            {
              "titre": "Artiste peintre",
              "description": "Crée des œuvres d'art visuelles en utilisant différentes techniques de peinture et en exprimant des idées, des émotions ou des concepts à travers des compositions visuelles uniques."
            },
            {
              "titre": "Agent de voyage",
              "description": "Organise et planifie des voyages pour les clients, en réservant des vols, des hôtels, des excursions et d'autres services de voyage, et en fournissant des conseils sur les destinations et les itinéraires."
            },
            {
              "titre": "Animateur/animateure pour enfants",
              "description": "Organise et anime des activités récréatives, éducatives et artistiques pour les enfants dans des centres de loisirs, des camps d'été ou d'autres environnements pour enfants."
            },
            {
              "titre": "Agent de police scientifique",
              "description": "Recueille et analyse des preuves sur les scènes de crime, en utilisant des techniques scientifiques et des technologies avancées pour aider à résoudre des enquêtes criminelles et à identifier les coupables."
            },
            {
              "titre": "Pharmacien/pharmacienne",
              "description": "Prépare et délivre des médicaments sur ordonnance, fournit des conseils sur l'utilisation appropriée des médicaments, surveille les interactions médicamenteuses et fournit des services de santé préventive."
            },
            {
              "titre": "Écrivain/écrivaine",
              "description": "Crée des textes écrits tels que des romans, des articles, des scripts ou des contenus web, en utilisant son imagination, ses recherches et ses compétences en écriture pour captiver les lecteurs."
            },
            {
              "titre": "Artiste de maquillage/maquilleuse",
              "description": "Applique du maquillage professionnel pour des événements spéciaux, des séances photo, des productions cinématographiques ou des défilés de mode, en utilisant des techniques artistiques pour créer des looks uniques."
            },
            {
              "titre": "Développeur/développeuse d'applications mobiles",
              "description": "Conçoit, développe et teste des applications mobiles pour les smartphones et les tablettes, en utilisant des langages de programmation et des outils de développement adaptés aux plateformes mobiles."
            },
            {
              "titre": "Consultant/consultante en gestion",
              "description": "Fournit des conseils stratégiques aux entreprises sur des questions telles que la gestion, la planification stratégique, les opérations, le marketing et la croissance des affaires pour améliorer leur performance globale."
            },
            {
              "titre": "Ingénieur/ingénieure en génie civil",
              "description": "Conçoit, supervise et gère la construction d'infrastructures publiques et privées telles que des routes, des ponts, des bâtiments, des barrages et des systèmes de distribution d'eau."
            },
            {
              "titre": "Photographe",
              "description": "Capture des images visuellement saisissantes en utilisant des appareils photo professionnels, en manipulant la lumière et en choisissant des angles et des compositions pour raconter des histoires ou capturer des moments spéciaux."
            },
            {
              "titre": "Spécialiste en ressources humaines",
              "description": "Recrute, forme, gère et développe le personnel d'une entreprise, en veillant au respect des politiques et des procédures en matière de ressources humaines et à la satisfaction des employés."
            },
            {
              "titre": "Économiste",
              "description": "Analyse les tendances économiques, les politiques gouvernementales et les données financières pour fournir des prévisions, des conseils et des recommandations sur des questions économiques et financières."
            },
            {
              "titre": "Conducteur/conductrice de poids lourd",
              "description": "Conduit des camions lourds pour transporter des marchandises sur de longues distances, en respectant les réglementations routières et en veillant à la sécurité des marchandises et des autres usagers de la route."
            },
            {
              "titre": "Dentiste",
              "description": "Diagnostique et traite les problèmes dentaires et bucco-dentaires, effectue des interventions chirurgicales dentaires et fournit des soins préventifs pour maintenir la santé dentaire des patients."
            },
            {
              "titre": "Musicien/musicienne",
              "description": "Interprète de la musique en jouant d'un instrument, en chantant ou en dirigeant un ensemble musical, en captivant les auditeurs et en transmettant des émotions à travers la musique."
            },
            {
              "titre": "Agent de marketing",
              "description": "Développe et met en œuvre des stratégies de marketing pour promouvoir des produits, des services ou des marques, en utilisant des techniques telles que la publicité, les médias sociaux et les relations publiques."
            },
            {
              "titre": "Coach personnel",
              "description": "Fournit un soutien, des conseils et des encouragements à des individus pour les aider à atteindre leurs objectifs personnels, professionnels ou sportifs, en les aidant à surmonter les obstacles et à maximiser leur potentiel."
            },
            {
              "titre": "Agent de sécurité",
              "description": "Surveille et protège les biens, les installations ou les personnes contre les menaces, les vols, les actes de vandalisme ou les intrusions, en appliquant des protocoles de sécurité et en intervenant en cas d'urgence."
            },
            {
              "titre": "Analyste financier/analyste financière",
              "description": "Analyse les données financières, évalue les risques et les opportunités d'investissement, et fournit des conseils sur les décisions financières telles que les investissements, les acquisitions et la gestion de portefeuille."
            },
            {
              "titre": "Agent immobilier/agent immobilier résidentiel",
              "description": "Facilite la vente, l'achat ou la location de propriétés résidentielles telles que des maisons, des appartements ou des condominiums, en guidant les clients tout au long du processus immobilier."
            },
            {
              "titre": "Thérapeute physique",
              "description": "Évalue et traite les blessures, les douleurs ou les limitations physiques en utilisant des techniques de réadaptation telles que l'exercice thérapeutique, la manipulation manuelle et les modalités physiques pour restaurer la fonction corporelle."
            },
            {
              "titre": "Garde forestier/garde forestière",
              "description": "Surveille, protège et gère les ressources naturelles des forêts, en appliquant des pratiques de conservation, en luttant contre les incendies de forêt et en sensibilisant le public à la préservation de l'environnement."
            },
            {
              "titre": "Réceptionniste",
              "description": "Accueille et oriente les clients, répond aux appels téléphoniques, gère les réservations et les rendez-vous, et fournit un soutien administratif dans les hôtels, les cliniques médicales, les entreprises ou d'autres établissements."
            },
            {
              "titre": "Consultant/consultante en informatique",
              "description": "Fournit des conseils et des solutions informatiques aux entreprises ou aux clients individuels, en analysant leurs besoins informatiques, en proposant des technologies appropriées et en assurant la mise en œuvre et la maintenance des systèmes informatiques."
            },
            {
              "titre": "Conseiller/conseillère financier/financière",
              "description": "Fournit des conseils financiers personnalisés aux particuliers ou aux entreprises, en évaluant leur situation financière, en identifiant leurs objectifs et en proposant des stratégies d'investissement, de planification fiscale et de gestion de patrimoine."
            },
            {
              "titre": "Ingénieur/ingénieure en énergie renouvelable",
              "description": "Conçoit, développe et implémente des solutions d'énergie renouvelable telles que les panneaux solaires, les éoliennes et les systèmes de bioénergie pour réduire l'empreinte carbone et promouvoir la durabilité environnementale."
            },
            {
              "titre": "Bibliothécaire",
              "description": "Gère et organise les collections de livres et de ressources dans les bibliothèques publiques ou privées, fournit des services d'information et de recherche, et encourage la lecture et l'éducation."
            },
            {
              "titre": "Agent de publicité",
              "description": "Conçoit et met en œuvre des campagnes publicitaires pour promouvoir des produits, des marques ou des événements, en utilisant des médias traditionnels, numériques ou sociaux pour atteindre le public cible."
            },
            {
              "titre": "Conducteur/conductrice de train",
              "description": "Pilote des trains de passagers ou de marchandises sur des voies ferrées, en respectant les horaires, les règles de sécurité et les procédures opérationnelles pour assurer le transport efficace des voyageurs ou des marchandises."
            },
            {
              "titre": "Urbaniste",
              "description": "Planifie et conçoit le développement urbain en tenant compte des aspects tels que l'aménagement du territoire, les infrastructures, les transports, l'environnement et les besoins socio-économiques pour créer des communautés durables et fonctionnelles."
            },
            {
              "titre": "Conseiller/conseillère en nutrition",
              "description": "Fournit des conseils et des recommandations sur l'alimentation, la nutrition et le mode de vie pour promouvoir la santé et prévenir les maladies, en tenant compte des besoins individuels et des objectifs de bien-être."
            },
            {
              "titre": "Pilote de ligne",
              "description": "Pilote des avions commerciaux pour transporter des passagers ou des marchandises vers des destinations nationales ou internationales, en respectant les réglementations de l'aviation et en assurant la sécurité des vols."
            },
            {
              "titre": "Agent immobilier/agent immobilier de luxe",
              "description": "Spécialisé dans la vente et l'achat de biens immobiliers haut de gamme, tels que des propriétés de luxe, des résidences de prestige ou des biens immobiliers exclusifs, en offrant des services personnalisés et discrets."
            },
            {
              "titre": "Technicien/technicienne en radiologie",
              "description": "Effectue des examens d'imagerie médicale tels que des radiographies, des scanners CT ou des IRM, en utilisant des équipements d'imagerie avancés pour aider au diagnostic et au traitement des maladies ou des blessures."
            },
            {
              "titre": "Designer de mode",
              "description": "Conçoit des vêtements, des accessoires et des collections de mode en suivant les tendances de l'industrie et en exprimant sa créativité à travers des dessins, des échantillons et des prototypes de vêtements."
            },
            {
              "titre": "Gestionnaire de projet",
              "description": "Planifie, organise et supervise des projets complexes dans divers domaines tels que la construction, l'informatique, le marketing ou l'ingénierie, en coordonnant les ressources, les échéanciers et les budgets pour atteindre les objectifs du projet."
            },
            {
              "titre": "Conseiller/conseillère en environnement",
              "description": "Analyse les problèmes environnementaux, propose des solutions durables et conseille les entreprises, les gouvernements ou les organisations sur la gestion des ressources naturelles, la conservation de la biodiversité et la réduction de l'impact environnemental."
            },
            {
              "titre": "Conseiller/conseillère en voyages d'aventure",
              "description": "Organise et planifie des voyages d'aventure pour les clients, en proposant des destinations exotiques, des activités d'aventure telles que la randonnée, le rafting ou le safari, et en fournissant des conseils sur la sécurité et l'équipement."
            },
            {
              "titre": "Conseiller/conseillère en santé mentale",
              "description": "Fournit un soutien, des conseils et des traitements thérapeutiques aux personnes souffrant de troubles émotionnels, comportementaux ou mentaux, en les aidant à surmonter leurs difficultés et à améliorer leur bien-être émotionnel."
            },
            {
              "titre": "Ingénieur/ingénieure aérospatial",
              "description": "Conçoit, développe et teste des véhicules et des systèmes aérospatiaux tels que des avions, des satellites et des fusées, en utilisant des principes d'ingénierie aéronautique et spatiale pour assurer leur performance et leur sécurité."
            },
            {
              "titre": "Agent artistique",
              "description": "Représente des artistes, des acteurs, des musiciens ou des écrivains en négociant des contrats, en organisant des engagements professionnels et en gérant leur carrière artistique et leurs relations avec l'industrie du divertissement."
            },
            {
              "titre": "Conseiller/conseillère en relations publiques",
              "description": "Développe et met en œuvre des stratégies de communication pour promouvoir l'image et la réputation d'une entreprise, d'une organisation ou d'une personnalité publique, en gérant les relations avec les médias et le public."
            },
            {
              "titre": "Analyste de données",
              "description": "Collecte, analyse et interprète des données pour fournir des informations et des insights exploitables, en utilisant des outils et des techniques d'analyse de données pour aider les entreprises à prendre des décisions stratégiques basées sur des données."
            },
            {
              "titre": "Agent immobilier/agent immobilier commercial",
              "description": "Facilite la vente, la location ou la gestion de biens immobiliers commerciaux tels que des bureaux, des magasins ou des entrepôts, en représentant les propriétaires ou les locataires dans les transactions commerciales."
            },
            {
              "titre": "Professionnel/ professionnelle des ressources humaines",
              "description": "Recrute, forme, gère et développe le personnel d'une entreprise, en veillant au respect des politiques et des procédures en matière de ressources humaines et à la satisfaction des employés."
            },
            {
              "titre": "Analyste des systèmes informatiques",
              "description": "Étudie les besoins en informatique d'une organisation et conçoit des solutions technologiques pour répondre à ces besoins, en analysant les systèmes existants, en proposant des améliorations et en supervisant leur mise en œuvre."
            },
            {
              "titre": "Ingénieur/ ingénieure de logiciel",
              "description": "Conçoit, développe et teste des logiciels et des applications informatiques pour répondre aux besoins spécifiques des utilisateurs ou des clients, en utilisant des langages de programmation et des techniques de développement logiciel."
            },
            {
              "titre": "Agent de recouvrement",
              "description": "Collecte les paiements en retard sur les factures ou les prêts impayés, en contactant les débiteurs, en négociant des plans de paiement et en utilisant des méthodes de recouvrement légales pour récupérer les fonds dus."
            },
            {
              "titre": "Agent de bord/steward/hôtesse de l'air",
              "description": "Assure la sécurité et le confort des passagers à bord des avions, fournit des services de restauration et d'hospitalité, et gère les situations d'urgence conformément aux réglementations de l'aviation civile."
            },
            {
              "titre": "Chercheur/cherc heuse scientifique",
              "description": "Conçoit et réalise des expériences, des études et des recherches pour développer de nouvelles connaissances et des avancées scientifiques dans des domaines tels que la biologie, la physique, la chimie ou la technologie."
            },
            {
              "titre": "Analyste de marché",
              "description": "Étudie les tendances du marché, analyse les données économiques et identifie les opportunités commerciales pour aider les entreprises à prendre des décisions stratégiques en matière de marketing, de développement de produits ou d'expansion commerciale."
            },
            {
              "titre": "Conseiller/conseillère en planification financière",
              "description": "Fournit des conseils financiers personnalisés aux particuliers ou aux entreprises, en évaluant leur situation financière, en identifiant leurs objectifs et en proposant des stratégies d'investissement, de planification fiscale et de gestion de patrimoine."
            },
            {
              "titre": "Conseiller/conseillère en gestion de carrière",
              "description": "Fournit des conseils professionnels aux individus sur leur carrière, leur éducation et leur développement personnel, en évaluant leurs intérêts, leurs compétences et leurs objectifs."
            },
            {
              "titre": "Chef de projet informatique",
              "description": "Planifie, organise et supervise des projets informatiques, en coordonnant les équipes de développement, en respectant les délais et les budgets, et en assurant la mise en œuvre réussie de solutions logicielles ou informatiques."
            },
            {
              "titre": "Conseiller/conseillère en marketing digital",
              "description": "Développe et met en œuvre des stratégies de marketing numérique pour promouvoir des produits, des services ou des marques sur les plateformes en ligne, en utilisant des techniques telles que le référencement, les médias sociaux et la publicité en ligne."
            },
            {
              "titre": "Géologue",
              "description": "Étudie la composition, la structure et l'histoire de la Terre en analysant des roches, des minéraux et des phénomènes géologiques, en fournissant des informations essentielles pour l'exploration minière, la construction et la protection de l'environnement."
            },
            {
              "titre": "Expert en sécurité informatique",
              "description": "Protège les systèmes informatiques et les réseaux contre les cybermenaces telles que les virus, les piratages et les attaques de logiciels malveillants, en mettant en œuvre des mesures de sécurité et en surveillant les activités suspectes."
            },
            {
              "titre": "Conseiller/conseillère en relations internationales",
              "description": "Analyse les politiques étrangères, les conflits internationaux et les enjeux mondiaux, en proposant des solutions diplomatiques, en négociant des accords internationaux et en facilitant la coopération entre les nations."
            },
            {
              "titre": "Coach sportif/coach sportive",
              "description": "Fournit un entraînement personnalisé, des conseils nutritionnels et un soutien mental aux athlètes et aux amateurs de sport pour améliorer leur performance physique, leur conditionnement et leur bien-être global."
            },
            {
              "titre": "Analyste de la chaîne d'approvisionnement",
              "description": "Analyse et optimise les processus de la chaîne d'approvisionnement, en supervisant la gestion des stocks, le transport, la logistique et la distribution des produits pour assurer l'efficacité opérationnelle et la satisfaction des clients."
            },
            {
              "titre": "Conseiller/conseillère en image",
              "description": "Fournit des conseils sur l'apparence personnelle, le style vestimentaire et l'image professionnelle pour améliorer la confiance en soi, l'estime de soi et les perspectives de carrière des clients."
            }
        ]
        EOD;
    $metiers = json_decode($jsonString, true);

    echo model("ProfessionsModel")->insertBatch($metiers);
    */
    $added  = model("ConversationMembresModel")->insert([
      "conversation_id" => 4,
      "membre_id" => 16,
      "isAdmin" => false
    ]);
    echo $added;
    exit;
  }

  /**
   * Retrieve the specified user in the database.
   * Only users with admin rights can do this
   * 
   * @return ResponseInterface The HTTP response.
   */
  public function show($identifier = null)
  {
    $identifier = $this->getIdentifier($identifier);
    $utilisateur = model("UtilisateursModel")->where($identifier['name'], $identifier['value'])->first();
    if (!auth()->user()->can('users.view')) {
      $response = [
        'statut'  => 'no',
        'message' => 'Action non authorisée pour ce profil.',
      ];
      return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
    }
    if (!$utilisateur) {
      $response = [
        'statut'  => 'no',
        'message' => 'Utilisateur Inconnu.',
      ];
      return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
    }
    $utilisateur->profils;
    $utilisateur->defaultProfil;
    $utilisateur->souscriptions;
    $utilisateur->pocket;
    $utilisateur->membres;
    $response = [
      'statut'  => 'ok',
      'message' => "Infos de l'utilisateur.",
      'data'    => $utilisateur,
    ];
    return $this->sendResponse($response);
  }

  /**
   * Add or update a model resource, from "posted" properties
   *
   * @return ResponseInterface The HTTP response.
   */
  public function update($identifier = null)
  {
    if ($identifier) {
      if (!auth()->user()->can('users.update')) {
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

    $rules  = [
      'nom'        => 'if_exist',
      'prenom'     => 'if_exist',
      "dateNaissance" => [
        'rules'  => 'if_exist|valid_date[Y-m-d]',
        'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj'],
      ],
      "sexe"       => [
        'rules'  => 'if_exist|is_not_unique[sexe_lists.nom]',
        'errors' => ['is_not_unique' => "la valeur de sexe n'est pas valide"],
      ],
      "profession" => 'if_exist',
      "ville"      => [
        'rules'  => 'if_exist|string',
        'errors' => ['string' => 'Ville incorrecte'],
      ],
      "etatcivil"  => [
        'rules'  => 'if_exist|is_not_unique[etatcivil_lists.nom]',
        'errors' => ['is_not_unique' => 'Etat civil incorrect'],
      ],
      "nbrEnfant"  => [
        'rules'  => 'if_exist|integer',
        'errors' => ['integer' => 'Le nombre doit être entier']
      ],
      "photoProfil" => 'if_exist|uploaded[photoProfil]',
      "photoCni"    => 'if_exist|uploaded[photoCni]',
    ];
    $input = $this->getRequestInput($this->request);
    $photoProfil = $this->request->getFile("photoProfil");
    $photoCni    = $this->request->getFile("photoCni");

    try {
      if (!$this->validate($rules)) {
        $hasError = true;
        throw new \Exception();
      }
      model("UtilisateursModel")->db->transBegin();
      if (isset($input['nom']) || isset($input['prenom'])) {
        $nom      = strtoupper($input['nom']) ?? $utilisateur->nom;
        $prenom   = ucfirst($input['prenom']) ?? $utilisateur->prenom;
        $fullName = $nom . ' ' . $prenom;
        auth()->getProvider()->update($utilisateur->user_id, ["username" => $fullName]);
      }

      $utilisateur->fill($input);
      $userInfos = $utilisateur->toArray();
      if ($photoProfil) {
        $userInfos['photo_profil'] = saveImage($photoProfil, 'uploads/users/profils/');
      }
      if ($photoCni) {
        $userInfos['photo_cni'] = saveImage($photoCni, 'uploads/users/cnis/');
      }
      model("UtilisateursModel")->update($userInfos['idUtilisateur'], $userInfos);
      model("UtilisateursModel")->db->transCommit();
    } catch (DataException $de) {
      $response = [
        'statut'  => 'ok',
        'message' => "Aucune modification apportée.",
      ];
      return $this->sendResponse($response);
    } catch (\Throwable $th) {
      model("UtilisateursModel")->db->transRollback();
      $errorsData = $this->getErrorsData($th, isset($hasError));
      $response = [
        'statut'  => 'no',
        'message' => "Impossible de Modifier ce compte.",
        'errors'  => $errorsData['errors'],
      ];
      return $this->sendResponse($response, $errorsData['code']);
    }
    $response = [
      'status'  => 'ok',
      'message' => 'Informations de compte mises à jour.',
    ];
    return $this->sendResponse($response);
  }

  /**
   * Ajoute un assureur à la liste des assureurs d'un medecin
   * 
   * @param  string|int medecinID (email | code | idUser)
   * @param  string|int assureurID (email | code | idUser)
   * @return \CodeIgniter\HTTP\ResponseInterfaced
   */
  public function addMedAssureur()
  {
    /* Action authorisée pour les assureurs, les medecins et les admins*/
    if (!(auth()->user()->can('assurances.addMed') || auth()->user()->can('medecins.addAssur'))) {
      $response = [
        'statut'  => 'no',
        'message' => 'Action non authorisée pour ce profil.',
      ];
      return $this->sendResponse($response, ResponseInterface::HTTP_UNAUTHORIZED);
    }

    try {
      $input = $this->getRequestInput($this->request);
      $medecinID  = model('UtilisateursModel')->where('code', $input['medecinCoe'])->findColumn('id')[0];
      $assureurID  = model('UtilisateursModel')->where('code', $input['assureurCode'])->findColumn('id')[0];

      $assMedModel = model('AssureurMedecinsModel');

      $exist = $assMedModel->where('assureur_id', $assureurID)
        ->where('medecin_id', $medecinID)
        ->first();
      if (!$exist) {
        $data['assureur_id'] = $assureurID;
        $data['medecin_id'] = $medecinID;
        $assMedModel->insert($data);
      }

      $response = [
        'statut'  => 'ok',
        'message' => 'Medecin et Assureur Associés.',
      ];
      return $this->sendResponse($response);
    } catch (\Throwable $th) {
      $response = [
        'statut'  => 'no',
        'message' => 'Erreur dans l\'association.',
        'errors'  => $th->getMessage(),
      ];
      return $this->sendResponse($response, ResponseInterface::HTTP_EXPECTATION_FAILED);
    }
  }

  /**
   * Delete the designated resource object from the model
   *
   * @return mixed
   */
  public function delete($id = null)
  {
    //
  }
}
