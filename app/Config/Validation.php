<?php

namespace Config;

use CodeIgniter\Validation\CreditCardRules;
use CodeIgniter\Validation\FileRules;
use CodeIgniter\Validation\FormatRules;
use CodeIgniter\Validation\Rules;

class Validation
{
    //--------------------------------------------------------------------
    // Setup
    //--------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var string[]
     */
    public $ruleSets = [
        Rules::class,
        FormatRules::class,
        FileRules::class,
        CreditCardRules::class,
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array<string, string>
     */
    public $templates = [
        'list'   => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single',
    ];


    //--------------------------------------------------------------------
    // Rules For Registration
    //--------------------------------------------------------------------
    public $registration = [
        'email'           => [
            'label'  => 'Email',
            'rules'  => 'required|max_length[254]|valid_email|is_unique[utilisateurs.email]|is_unique[connexions.secret]',
            'errors' => [
                'required'    => "L'email est requis.",
                'max_length'  => "Email trop long.",
                'valid_email' => "Email invalide.",
                'is_unique'   => "Cet Email est déjà utilisé.",
            ],
        ],
        'password'        => [
            'label'  => 'Password|min_length[8]',
            'rules'  => 'required',
            'errors' => ['required' => "Le mot de passe est requis.", "min_length" => "Le mot de passe est trop court."],
        ],
        // 'passwordConfirm' => [
        //     'label'  => 'Password Confirmation',
        //     'rules'  => 'required|matches[password]',
        //     'errors' => ['required'    => "La confirmation de mot de passe est requis.",],
        // ],
        'categorie' => [
            'label'  => 'categorie',
            'rules'  => 'required|is_not_unique[profils.niveau]',
            'errors' => ['required' => "La categorie est requise.", 'is_not_unique' => 'Categorie incorrecte.'],
        ],
        'nom'             => [
            'rules'  => 'required',
            'errors' => ['required' => 'Valeur Inappropriée.'],
        ],
        'prenom'          => [
            'rules' => 'required',
            'errors' => ['required' => 'Valeur Inappropriée.'],
        ],
        "dateNaissance"   => [
            'rules'  => 'if_exist|valid_date[Y-m-d]',
            'errors' => ['valid_date' => 'La date dois être au format AAAA-mm-jj'],
        ],
        "sexe"            => [
            'rules'  => 'if_exist|is_not_unique[sexe_lists.nom]',
            'errors' => ['is_not_unique' => "la valeur de sexe n'est pas valide"],
        ],
        "profession"      => [
            'rules'  => 'if_exist|is_not_unique[profession_lists.titre]',
            'errors' => ['is_not_unique' => "la valeur de sexe n'est pas valide"],
        ],
        "tel1"            => [
            'rules'  => 'required|numeric|is_unique[utilisateurs.tel1]',
            'errors' => [
                'required'  => 'Numero de telephone principal requis',
                'numeric'   => 'Le numero de telephone principal contient des caractères inapprovpriés',
                'is_unique' => 'Le numero de telephone secondaire est déjà utilisé',
            ]
        ],
        "tel2"            => [
            'rules'  => 'if_exist|numeric|is_unique[utilisateurs.tel1]',
            'errors' => [
                'numeric'   => 'Le numero de telephone principal contient des caractères inapprovpriés',
                'is_unique' => 'Le numero de telephone secondaire est déjà utilisé',
            ]
        ],
        "ville"           => [
            'rules'  => 'if_exist|string',
            'errors' => ['string' => 'Ville incorrecte'],
        ],
        "etatcivil"       => [
            'rules'  => 'if_exist|is_not_unique[etatcivil_lists.nom]',
            'errors' => ['is_not_unique' => 'Etat civil incorrect'],
        ],
        "nbrEnfant"       => [
            'rules'  => 'if_exist|integer',
            'errors' => ['integer' => 'Le nombre doit être entier']
        ],
        "photoProfil"     => 'if_exist|uploaded[photoProfil]',
        "photoCni"        => 'if_exist|uploaded[photoCni]',
    ];


    //--------------------------------------------------------------------
    // Rules For Login
    //--------------------------------------------------------------------
    public $login = [
        /*  Functionnal for other 
            'email' => [
                'label' => 'Email',
                'rules' => 'valid_email|required_without[code,tel1]',
            ],
            'code' => [
                'label' => 'Code',
                'rules' => 'required_without[code,tel1]',
            ],
            'tel1' => [
                'label' => 'Tel1',
                'rules' => [
                    'required',
                    'max_length[254]',
                    'valid_email',
                    'required_without[code,tel1]'
                ],
            ],
        */
        // 'identifiant' => "required",
        'email' => "required",
        'password' => [
            'label' => 'Password',
            'rules' => 'required',
        ],
    ];
}
