<?php

namespace Modules\Assurances\Entities;

use CodeIgniter\Entity\Entity;

class SouscriptionServiceEntity extends Entity
{
    const INACTIF = 0, ACTIF = 1;
    public static $etats = ["Actif", "InActif"];

    protected $datamap = [
        // property_name => db_column_name
        'idSouscription'  => 'souscription_id',
        'idService'       => 'service_id',
        'quantiteUtilise' => 'quantite_utilise',
        'prixCouvert'     => 'prix_couvert',
    ];

    // Defining a type with parameters
    protected $casts = [
        'souscription_id'  => "integer",
        'service_id'       => "integer",
        'quantite'         => "integer",
        'quantite_utilise' => "integer",
        'prix_couverture'  => "float",
        'prix_couvert'     => "float",
        'taux_couverture'  => "float",
        'etat'             => "etatcaster[InActif,Actif]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];
}
