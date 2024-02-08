<?php

namespace Modules\Assurances\Entities;

use CodeIgniter\Entity\Entity;


class QuestionOptionsEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id'           => "integer",
        'subquestions' => "json",
        'prix'         => "integer",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idOption' => 'id',
    ];

    // public function getSubquestions()
    // {
    //     if (isset($this->attributes['subquestions']) && gettype($this->attributes['subquestions']) === 'string') {
    //         $this->attributes['subquestions'] = json_decode($this->attributes['subquestions']);
    //     }

    //     return $this->attributes['subquestions'] ?? [];
    // }

    // public function setSubquestions($value)
    // {
    //     if (is_array($value)) {
    //         return json_encode($value);
    //     }

    //     return null;
    // }
}
