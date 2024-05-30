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
        'heureDebut'     => 'heure_dispo_debut',
        'heureFin'       => 'heure_dispo_fin',
        'jour'           => 'jour_dispo',
        'proprietaire'   => 'proprietaire_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
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
}
