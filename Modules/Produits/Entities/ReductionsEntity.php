<?php

namespace Modules\Produits\Entities;

use CodeIgniter\Entity\Entity;


class ReductionsEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id'                => "integer",
        'valeur'            => "float",
        'taux'              => "float",
        'utilise_nombre'    => "integer",
        'usage_max_nombre'  => "integer",
        // 'expiration_date'   => "datetime",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idReduction'    => 'id',
        'nombreUtilise'  => 'utilise_nombre',
        'dateExpiration' => 'expiration_date',
        'nombreUsageMax' => 'usage_max_nombre',
        'auteur'         => 'auteur_id',
    ];
}
