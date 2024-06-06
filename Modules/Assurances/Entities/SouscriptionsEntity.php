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
     * getDateCreation
     * 
     * renvoie la date de création formatée
     *
     * @return object les données souscripteur
     */
    public function getDateCreation()
    {
        if (isset($this->attributes['dateCreation']) && gettype($this->attributes['dateCreation']) === 'string') {
            $this->attributes['dateCreation'] = date('d-m-Y', strtotime($this->attributes['dateCreation']));
        }

        return $this?->attributes['dateCreation'];
    }

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
        return $this->attributes['beneficiaires'] ?? [];
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

        return $this->attributes['questionAnswers'] ?? [];
    }

    /**
     * getDocuments
     * 
     * renvoie les services fournis par cette souscription
     *
     * @return array les services fournis par la souscription
     */
    public function getDocuments()
    {
        if (!isset($this->attributes['documents'])) {
            $documentIDs = model('SouscriptionDocumentsModel')->where('souscription_id', $this->attributes['id'])->findColumn('document_id');
            $this->attributes['documents'] = $documentIDs ? model("DocumentsModel")->whereIn("id", $documentIDs)->findAll() : null;
        }

        return $this?->attributes['documents'] ?? [];
    }

    /**
     * renvoie les services associés à cette souscription
     *
     * @return array les données du service
     */
    public function getServices()
    {
        if (!isset($this->attributes['services'])) {
            $this->attributes['services'] = model('SouscriptionServicesModel')->join('services', 'services.id = souscription_services.service_id')
                ->where('souscription_id', $this->attributes['id'])
                ->findAll();
        }

        return $this?->attributes['services'];
    }

    /**
     * Retrieves a service by its ID from the list of services associated with this object.
     *
     * @param int $serviceId The ID of the service to retrieve.
     * @throws \Exception If the service with the given ID is not found.
     * @return mixed The service object with the given ID.
     */
    public function getService(int $serviceId)
    {
        $services = $this->getServices();
        $service = array_filter($services, fn ($serv) => $serv->id == $serviceId);
        $service = reset($service);
        if (!$service) {
            throw new \Exception("Ce service n'est pas offert par cette souscription", 1);
        }
        return $service;
    }

    public function isValid()
    {
        return $this->attributes['etat'] == self::ACTIF;
    }

    /**
     * Renvoie le montant couvert par cette souscription,
     * pour le service spécifié
     *
     * @param  int $serviceId
     * @return float
     */
    public function coverage(int $serviceId, float $cout)
    {
        $service = array_filter($this->getServices(), fn ($serv) => $serv->id == $serviceId);
        $service = reset($service);
        if (!$service) {
            throw new \Exception("Ce service n'est pas ou plus offert par cette souscription", 1);
        }
        $prixCouverture = $service->prix_couverture - $service->prix_couvert;
        $qtiteCouverture = $service->quantite - $service->quantite_utilise;

        if (($prixCouverture <= 0) || ($qtiteCouverture <= 0)) {
            return 0;
        }
        if ($service->taux_couverture < 100) {
            $toCover = $cout * $service->taux_couverture / 100;
            return $prixCouverture - $toCover > 0 ? $toCover : $prixCouverture;
        } else {
            return $prixCouverture - $cout > 0 ? $cout : $prixCouverture;
        }
    }
}
