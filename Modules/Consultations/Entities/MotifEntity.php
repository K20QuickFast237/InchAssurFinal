<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class MotifEntity extends Entity
{
    protected $datamap = [
        'idMotif' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
