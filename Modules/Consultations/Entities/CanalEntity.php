<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class CanalEntity extends Entity
{
    protected $datamap = [
        'idCanal' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
