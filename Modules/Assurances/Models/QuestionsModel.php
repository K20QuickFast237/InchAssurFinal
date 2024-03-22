<?php

namespace Modules\Assurances\Models;

use CodeIgniter\Model;

class QuestionsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'questions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = '\Modules\Assurances\Entities\QuestionsEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['auteur_id', 'libelle', 'description', 'field_type', 'is_required', 'options', 'tarif_type'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

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


    /** @todo Think about optimising by never requestion twice for the same id of subquestion
     * Retourne une liste détaillée d'informations sur les questions.
     *
     * @param  array $questionIDs - liste d'identifiant de questions dont on veut obtenir les détails.
     * @return array - les détails des questions avec les sous-questions des options étants elles même des questions.
     */
    public function getBulkQuestionDetails(array $questionIDs)
    {
        $dataQuestions = $questionIDs ? $this->whereIn('id', $questionIDs)->findAll() : [];
        foreach ($dataQuestions as $dataQuestion) {
            $options = $dataQuestion->optionsDetails;
            foreach ($options as $key => $option) {
                if (isset($option->subquestions) && is_array($option->subquestions)) {
                    // $options[$key]->subquestions = $this->getBulkQuestionDetails($option->subquestions);
                    $option->subquestions = $this->getBulkQuestionDetails($option->subquestions);
                }
            }
            $dataQuestion->options = $options;
        }
        // return array_map(fn ($elmt) => $elmt->toArray(), $dataQuestions);
        return $dataQuestions;
    }

    /*public function getBulkSubscriptionQuestionDetails(array $questionIDs, array $answers)
    {
        /* $answers a les champs idOption, idQuestion * /
        $optionIDs = array_merge($this->whereIn('id', $questionIDs)->findColumn("options"));
        $subquestionIDs = array_merge(model("QuestionOptionsModel")->whereIn('id', $optionIDs)->findColumn('subquestions'));

        $allQuestionIDs = array_unique(array_merge($questionIDs, $subquestionIDs));
        $dataQuestions  = $this->whereIn('id', $allQuestionIDs)->findAll();
        foreach ($dataQuestions as $dataQuestion) {
            $options = $dataQuestion->optionsDetails;
            $
        }
        foreach ($dataQuestions as $dataQuestion) {
            $options = $dataQuestion->optionsDetails;
            foreach ($options as $key => $option) {
                if () {
                    # code...
                }
                if (isset($option->subquestions) && is_array($option->subquestions)) {
                    // $options[$key]->subquestions = $this->getBulkQuestionDetails($option->subquestions);
                    $option->subquestions = $this->getBulkQuestionDetails($option->subquestions);
                }
            }
            $dataQuestion->options = $options;
        }
        // return array_map(fn ($elmt) => $elmt->toArray(), $dataQuestions);
        return $dataQuestions;
    }*/

    public function getSimplified($id)
    {
        return $this->select("id, auteur_id, libelle, description")
            ->where('id', $id)->first();
    }
}
