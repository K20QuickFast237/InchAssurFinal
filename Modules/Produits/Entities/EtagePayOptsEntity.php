<?php

namespace Modules\Produits\Entities;

use Modules\Produits\Entities\PaiementOptionsEntity;


class EtagePayOptsEntity extends PaiementOptionsEntity
{
    protected $casts = [
        'id'                 => "integer",
        'nom'                => "string",
        'description'        => "string",
        'depot_initial_taux' => "float",
        'etape_duree'        => "integer",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idPayOption'      => 'id',
        "tauxDepotInitial" => "depot_initial_taux",
        "dureeEtape"       => "etape_duree",
    ];
    /*
        Ici nous implémenterons les méthodes propres à ce mode de paiement
    */
    // protected $attributes = [];

    public function initiatePaiement()
    {
    }

    /** @todo implementer le setter de tauxDepotInitial à être différent de 100 dans tous les cas*/
}
