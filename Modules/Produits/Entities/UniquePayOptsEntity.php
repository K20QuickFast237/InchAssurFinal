<?php

namespace Modules\Produits\Entities;

use Modules\Produits\Entities\PaiementOptionsEntity;


class UniquePayOptsEntity extends PaiementOptionsEntity
{
    protected $casts = [
        'id'                 => "integer",
        'nom'                => "string",
        'description'        => "string",
        'depot_initial_taux' => "float",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idPayOption'      => 'id',
        "tauxDepotInitial" => "depot_initial_taux",
    ];
    /*
        Ici nous implémenterons les méthodes propres à ce mode de paiement
    */

    public function initiatePaiement()
    {
    }

    /** @todo implementer le setter de tauxDepotInitial à 100 dans tous les cas*/
}
