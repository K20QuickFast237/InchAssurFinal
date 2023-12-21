<?php

namespace Modules\Assurances\Entities;

use App\Traits\EtatsListTrait;
use App\Traits\StatutsListTrait;
use CodeIgniter\Entity\Entity;
/* Remarques importatntes pour cette entity class
    - l'attribut services lors de son enregistrement 
    doit être au format json_encode d'un tableau de doublons:
    [
        'idService' : 2,
        'quantité'  : 3,
    ]
*/

class AssurancesEntity extends Entity
{
    use StatutsListTrait; // provide a function to return the static statuts property defined down bellow.

    public static $etats = ["Inactif", "Actif"];


    protected $datamap = [
        // property_name => db_column_name
        'idAssurance' => 'id_assurance',
        // 'idCategorie' => 'categorie_id',
        'categorie' => 'categorie_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id_assurance' => "integer",
        'questions'    => "json",
        'etat' => "etatcaster[Inactif,Actif]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    /**
     * getServices
     * 
     * renvoie la liste des services de l'assurance
     * il s'agit d'un tableau au format ['idService', 'nomService', descriptionService,'quantité']
     *
     * @return array
     */
    public function getServices()
    {
        if (isset($this->attributes['services']) && gettype($this->attributes['services']) === 'string') {
            $dataServices = json_decode($this->attributes['services'], true);

            $servicesIDs  = array_map(function ($val) {
                return $val['idService'];
            }, $dataServices);

            $servicesNames = model('servicesModel')
                ->select('id_service, nomService, descriptionService')
                ->whereIn('id_service', $servicesIDs)
                ->where('etat', ServicesEntity::ACTIF)
                ->findAll();

            $servicesIDs = array_flip($servicesIDs);

            // foreach ($servicesNames as $key => $value) {
            //     $position = $servicesIDs[$value['id_service']];
            //     unset($value['id_service']);
            //     $dataServices[$position] = array_merge($dataServices[$position], $value);
            // }

            foreach ($servicesNames as $service) {
                $position = $servicesIDs[$service->idService];
                $dataServices[$position] = array_merge($dataServices[$position], $service->toArray());
            }

            $this->attributes['services'] = $dataServices;
            return $this->attributes['services'];
        } elseif (isset($this->attributes['services'])) {
            return $this->attributes['services'];
        }

        return null;
    }

    /**
     * getQuestionnaire
     * 
     * renvoie le questionnaire associé à cette assurance
     *
     * @return array une liste de questions constituant le questionnaire
     */
    public function getQuestionnaire()
    {
        if (isset($this->attributes['questions'])) {
            $questionIDs = json_decode($this->attributes['questions'], true);

            $questionnaire = model('QuestionsModel')->getBulkQuestionDetails($questionIDs);

            $this->attributes['questionnaire'] = $questionnaire;
        } else {
            $this->attributes['questionnaire'] = [];
        }

        return $this->attributes['questionnaire'];
    }

    /**
     * @deprecated now using getQuestionnaire
     */
    public function oldGetQuestionnaire()
    {
        if (isset($this->attributes['questions'])) {
            $questionIDs = json_decode($this->attributes['questions'], true);

            $questionnaire = model('QuestionsModel')
                ->whereIn('id_question', $questionIDs)
                ->findAll();

            $questionIDs = array_flip($questionIDs);

            foreach ($questionnaire as $question) {
                $position = $questionIDs[$question->idQuestion];
                $questionnaire[$position] =  $question->toArray();
            }

            $this->attributes['questionnaire'] = $questionnaire;
        } else {
            $this->attributes['questionnaire'] = [];
        }

        return $this->attributes['questionnaire'];
    }

    public function getCategorieId()
    {
        if (isset($this->attributes['categorie_id']) && gettype($this->attributes['categorie_id']) === 'string') {
            $cat = model('CategorieAssurancesModel')->where('id_categorieassurance', $this->attributes['categorie_id'])->first();
            $this->attributes['categorie_id'] = $cat?->toArray();
            return $this->attributes['categorie_id'];
        } elseif (isset($this->attributes['categorie_id'])) {
            return $this->attributes['categorie_id'];
        }
        return null;
    }

    public function getPieceAJoindre()
    {
        if (isset($this->attributes['piece_a_joindre']) && gettype($this->attributes['piece_a_joindre']) === 'string') {
            $piecesIDs = json_decode($this->attributes['piece_a_joindre']);

            $pieces = model('PiecejointesModel')->whereIn('id_piecejointe', $piecesIDs)
                ->findAll();

            $this->attributes['piece_a_joindre'] = array_map(fn ($p) => $p->toArray(), $pieces);
            return $this->attributes['piece_a_joindre'];
        } elseif (isset($this->attributes['piece_a_joindre'])) {
            return $this->attributes['piece_a_joindre'];
        }

        return null;
    }
}
