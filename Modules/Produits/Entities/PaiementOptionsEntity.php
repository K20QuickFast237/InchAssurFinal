<?php

namespace Modules\Produits\Entities;

use CodeIgniter\Entity\Entity;


class PaiementOptionsEntity extends Entity
{
    const UNIQUE = 'Unique', CYCLIQUE = 'Planifié', PERIODE = 'A Echéance';
    const DefaultID = 1;
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

    /*Détermine le montant minimal à payer avec cette option pour le montant final donné en paramètre*/
    public function get_initial_amount_from_option($toPay)
    {
        return ($toPay * $this->attributes['depot_initial_taux']) / 100;
    }
}
