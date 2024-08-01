<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class OrdonnanceEntity extends Entity
{
    protected $datamap = [
        'idOrdonnance' => 'id',
        'idConsultation' => 'consultation_id',
    ];

    protected $casts = [
        'id'              => "integer",
        'consultation_id' => "integer",
        'prescription'    => "?json-array",
    ];
}
