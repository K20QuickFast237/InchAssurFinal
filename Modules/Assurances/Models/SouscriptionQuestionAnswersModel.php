<?php

namespace Modules\Assurances\Models;

use CodeIgniter\Model;

class SouscriptionQuestionAnswersModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'souscription_questionans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['souscription_id', 'questionans_id'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = 'dateModification';
    protected $deletedField  = 'dateSuppression';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function answerExist($souscriptionID, $questionID)
    {
        return $this->join('question_answers', 'souscription_questionans.questionans_id = question_answers.id')
            ->select('question_answers.id, question_answers.added_price')
            ->where('souscription_questionans.souscription_id', $souscriptionID)
            ->where('question_answers.question_id', $questionID)
            ->first() ?? false;
        // ->findColumn('question_answers.id')[0] ?? false;
    }
}
