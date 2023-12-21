<?php

namespace Modules\Assurances\Entities;

use CodeIgniter\Entity\Entity;

class SouscriptionsEntity extends Entity
{
    const PENDING = 0, ACTIF = 1, TERMINE = 2;
    public static $etats = ["Pending", "Actif", "Terminée"];

    protected $datamap = [
        // property_name => db_column_name
        'idSouscription' => 'id',
        'souscripteur'   => 'souscripteur_id',
        'assurance'      => 'assurance_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'   => "integer",
        'cout' => "float",
        'etat' => "etatcaster[Pending,Actif,Terminée]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    /**
     * getSouscripteurId
     * 
     * renvoie le souscripteur associée à cette souscription
     *
     * @return object les données souscripteur
     */
    public function getSouscripteurId()
    {
        if (isset($this->attributes['souscripteur_id']) && gettype($this->attributes['souscripteur_id']) === 'string') {
            $this->attributes['souscripteur_id'] = model('UtilisateursModel')->getSimplified($this->attributes['souscripteur_id']);
        }

        return $this?->attributes['souscripteur_id'];
    }

    /** 
     * getAssuranceId
     * 
     * renvoie l'assurance associée à cette souscription
     *
     * @return object les données souscripteur
     */
    public function getAssuranceId()
    {
        // if (isset($this->attributes['assurance_id']) && preg_match('/[integer|string]/i', gettype($this->attributes['assurance_id']))) {
        if (isset($this->attributes['assurance_id']) && gettype($this->attributes['assurance_id']) === 'string') {
            $assurance = model('AssurancesModel')->getSimplified($this->attributes['assurance_id']);
            $assurance->images = array_values(array_filter($assurance->images ?? [], fn ($img) => $img->isDefault));
            $this->attributes['assurance_id'] = $assurance;
        }

        return $this?->attributes['assurance_id'];
    }

    /** 
     * getAssuranceId
     * 
     * renvoie l'assurance associée à cette souscription
     *
     * @return object les données souscripteur
     */
    // public function getBeneficiairesDetails()
    public function getBeneficiaires()
    {
        if (!isset($this->attributes['beneficiaires'])) {
            $benefIDs = model('SouscriptionBeneficiairesModel')->where('souscription_id', $this->attributes['id'])->findColumn('beneficiaire_id');
            $this->attributes['beneficiaires'] = $benefIDs ? model('UtilisateursModel')->getBulkSimplified($benefIDs) : null;
        }
        return $this->attributes['beneficiaires'];
    }

    /**
     * questionAnswers
     * 
     * renvoie les réponses de questions associées à cette souscription
     *
     * @return object les données du document
     */
    public function getQuestionAnswers()
    {
        if (!isset($this->attributes['questionAnswers'])) {
            $answerIDs = model('SouscriptionQuestionAnswersModel')->where('souscription_id', $this->attributes['id'])->findColumn('questionans_id');
            $this->attributes['questionAnswers'] = $answerIDs ? model("QuestionAnswersModel")->whereIn("id", $answerIDs)->findAll() : null;
        }

        return $this->attributes['questionAnswers'];
    }

    /**
     * getDocuments
     * 
     * renvoie les documents associés à cette souscription
     *
     * @return object les données du document
     */
    public function getDocuments()
    {
        if (!isset($this->attributes['documents'])) {
            $documentIDs = model('SouscriptionDocumentsModel')->where('souscription_id', $this->attributes['id'])->findColumn('document_id');
            $this->attributes['documents'] = $documentIDs ? model("DocumentsModel")->whereIn("id", $documentIDs)->findAll() : null;
        }

        return $this?->attributes['documents'];
    }
}
