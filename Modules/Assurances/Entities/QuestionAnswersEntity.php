<?php

namespace Modules\Assurances\Entities;

use CodeIgniter\Entity\Entity;


class QuestionAnswersEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id'    => "integer",
        'choix' => 'json-array',
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idAnswer'       => 'id',
        'question'       => 'question_id',
        'supplementPrix' => 'added_price',
    ];

    public function getQuestionId()
    {
        if (isset($this->attributes['question_id']) && gettype($this->attributes['question_id']) === 'string') {
            $this->attributes['question_id'] = model('QuestionsModel')->getSimplified($this->attributes['question_id']);
        }

        return $this?->attributes['question_id'];
    }
}
