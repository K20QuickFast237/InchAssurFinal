<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class LocalisationEntity extends Entity
{
    protected $datamap = [
        'idLocalisation' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
