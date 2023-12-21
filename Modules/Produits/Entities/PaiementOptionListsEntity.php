<?php

namespace Modules\Produits\Entities;

use CodeIgniter\Entity\Entity;


class PaiementOptionListsEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
    ];
}
