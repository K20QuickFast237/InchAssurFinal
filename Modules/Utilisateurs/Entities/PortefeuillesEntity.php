<?php

namespace Modules\Utilisateurs\Entities;

use CodeIgniter\Entity\Entity;

class PortefeuillesEntity extends Entity
{
    protected $datamap = [
        // property_name => db_column_name
        'idPortefeuille' => 'id',
        'idUtilisateur'  => 'utilisateur_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'utilisateur_id' => "integer",
        'solde'          => "float",
    ];
}
