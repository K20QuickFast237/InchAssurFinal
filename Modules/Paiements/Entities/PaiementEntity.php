<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class PaiementEntity extends Entity
{
    const ECHOUE = 0, VALIDE = 1, ANNULE = 2, EN_COURS = 3;
    public static $statuts = ["Echoué", "Validé", "Annulé", "En Cours"];

    protected $datamap  = [
        'idPaiement'    => 'id',
        'idMode'        => 'mode_id',
        'idAuteur'      => 'auteur_id',
        'idTransaction' => 'transaction_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'      => "integer",
        'montant' => "?float",
        'statut'  => "etatcaster[Echoué,Validé,Annulé,En Cours]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];
}
