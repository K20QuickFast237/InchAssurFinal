<?php

namespace Modules\Produits\Entities;

use CodeIgniter\Entity\Entity;


class PaiementOptionsEntity extends Entity
{
    // Defining a type with parameters
    protected $casts = [
        'id'                 => "integer",
        'nom'                => "string",
        'description'        => "string",
        'depot_initial_taux' => "float",
        'etape_duree'        => "integer",
        'montant_cible'      => "float",
        'cycle_taux'         => "float",
        'cycle_longueur'     => "integer",
        'cycle_nombre'       => "integer",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idPayOption'      => 'id',
        "tauxDepotInitial" => "depot_initial_taux",
        "montantCible"     => "montant_cible",
        "dureeEtape"       => "etape_duree",
        "longueurCycle"    => "cycle_longueur",
        "tauxCycle"        => "cycle_taux",
        "nombreCycle"      => "cycle_nombre",
    ];
}
