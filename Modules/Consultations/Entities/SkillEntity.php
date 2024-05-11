<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class SkillEntity extends Entity
{
    protected $datamap = [
        'idSkill' => 'id',
    ];

    protected $casts = [
        'id' => "integer",
    ];
}
