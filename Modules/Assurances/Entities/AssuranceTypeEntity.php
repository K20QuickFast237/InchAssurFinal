<?php

namespace Modules\Assurances\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;


class AssuranceTypeEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id' => "integer",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idTypeAssurance' => 'id',
    ];
}
