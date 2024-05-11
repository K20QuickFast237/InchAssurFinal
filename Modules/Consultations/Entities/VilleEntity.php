<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class VilleEntity extends Entity
{
    protected $datamap = [
        'idVille' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
