<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class LangueEntity extends Entity
{
    protected $datamap = [
        'idLangue' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
