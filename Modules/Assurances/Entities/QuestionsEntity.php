<?php

namespace Modules\Assurances\Entities;

use App\Traits\ParamListTrait;
use CodeIgniter\Entity\Entity;


class QuestionsEntity extends Entity
{
    use ParamListTrait; // provide a function to return a static property in those defined down bellow selected by the parameter.

    const POURCENTAGE = 0, ADDITION = 1;
    public static $tarifTypes = ["Pourcentage", "Addition"];

    // Defining a type with parameters
    protected $casts = [
        'id'         => "integer",
        'auteur_id'  => "integer",
        'requis'     => "boolean",
        'options'    => "json",
        'tarif_type' => "listcaster[Pourcentage,Addition]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'listcaster' => \App\Entities\Cast\ListCaster::class,
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idQuestion' => 'id',
        'idAuteur'   => 'auteur_id',
        'isRequired' => 'requis',
        'tarifType' => 'tarif_type',
        'fieldType' => 'field_type',
    ];

    public function getTarifTypeList()
    {
        if (!isset($this->attributes['tarifTypeList'])) {
            $this->attributes['tarifTypeList'] = $this->paramList('tarifTypes');
        }
        return $this->attributes['tarifTypeList'];
    }

    public function getOptionsDetails()
    {
        if (isset($this->attributes['options']) && gettype($this->attributes['options']) === 'string') {
            $optionIDs = json_decode($this->attributes['options']);

            $options = model('QuestionOptionsModel')
                ->whereIn('id', $optionIDs)
                ->findAll();

            $optionIDs = array_flip($optionIDs);

            foreach ($options as $option) {
                $position = $optionIDs[$option->idOption];
                $options[$position] = $option;
            }

            $this->attributes['options'] = $options;
        }

        return $this->attributes['options'] ?? [];
    }
}
