<?php

namespace Modules\Utilisateurs\Entities;

use CodeIgniter\Entity\Entity;

class ProfilsEntity extends Entity
{
    protected $datamap = [
        // property_name => db_column_name
        'idProfil'      => 'profil_id',
        'idUtilisateur' => 'utilisateur_id',
        'default'       => 'defaultProfil',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'utilisateur_id' => "integer",
    ];
}
