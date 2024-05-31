<?php

namespace Modules\Consultations\Entities;

use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;

class ConsultationEntity extends Entity
{
    use EtatsListTrait;

    const DEFAULT_DUREE = 30;
    const ENATTENTE = 0, VALIDE = 1, EXPIREE = 2, // REFUSE = 2,
        ENCOURS = 3, TERMINE = 4, ANNULE = 5, TRANSMIS = 6, ECHOUE = 7;

    protected $datamap = [
        'idConsultation' => 'id',
        'localisation'   => 'localisation_id',
        'medecin'        => 'medecin_user_id',
        'patient'        => 'patient_user_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'duree'          => "integer",
        'prix'           => "float",
        'isExpertise'    => "?boolean",
        'isSecondAdvice' => "?boolean",
        'isAssured'      => "?boolean",
        'statut'         => "etatcaster[En Attente,Validé,Expiré,En Cours,Terminé,Annulé,Transmis,Échoué]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];
    public function showServiceInfo()
    {
        return model('ServicesModel')->where('nom', 'Consultations')->first();
    }

    public function getMedecinUserId()
    {
        if (isset($this->attributes['medecin_user_id']) && gettype($this->attributes['medecin_user_id']) === 'string') {
            $this->attributes['medecin'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['medecin_user_id']);
        }

        return $this->attributes['medecin'];
    }
    public function getPatientUserId()
    {
        if (isset($this->attributes['patient_user_id']) && gettype($this->attributes['patient_user_id']) === 'string') {
            $this->attributes['patient'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['patient_user_id']);
        }

        return $this->attributes['patient'];
    }
}
