<?php

namespace Modules\Consultations\Entities;

use CodeIgniter\Entity\Entity;

class AvisExpertEntity extends Entity
{
    const EN_ATTENTE = 0, TERMINE = 1, EN_COURS = 2, ANNULE = 3;

    protected $datamap = [
        'idAvisExpert' => 'id',
        'from'         => 'medecin_sender_id',
        'to'           => 'medecin_receiver_id',
        'idConsultation' => 'consultation_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'              => "integer",
        'consultation_id' => "integer",
        'statut'          => "etatcaster[En Attente,Terminé,En Cours, Annulé]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getMedecinSenderId()
    {
        if (isset($this->attributes['medecin_sender_id']) && gettype($this->attributes['medecin_sender_id']) === 'string') {
            $this->attributes['medecin_sender_id'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['medecin_sender_id']);
        }

        return $this->attributes['medecin_sender_id'];
    }

    public function getMedecinReceiverId()
    {
        if (isset($this->attributes['medecin_receiver_id']) && gettype($this->attributes['medecin_receiver_id']) === 'string') {
            $this->attributes['medecin_receiver_id'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['medecin_receiver_id']);
        }

        return $this->attributes['medecin_receiver_id'];
    }

    public function getDocuments()
    {
        if (!isset($this->attributes['documents'])) {
            $docIds = model("AvisDocumentsModel")->where('avis_id', $this->attributes['id'])->findColumn('document_id');
            $this->attributes['documents'] = model("DocumentsModel")->getMultiSimplified($docIds);
        }

        return $this->attributes['documents'];
    }

    public function getAttachements()
    {
        if (!isset($this->attributes['attachements'])) {
            $docIds = model("AvisAttachementsModel")->where('avis_id', $this->attributes['id'])->findColumn('document_id');
            $this->attributes['attachements'] = model("DocumentsModel")->getMultiSimplified($docIds);
        }

        return $this->attributes['attachements'];
    }
}
