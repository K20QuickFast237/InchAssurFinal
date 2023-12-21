<?php

namespace Modules\Assurances\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;


class ServicesEntity extends Entity
{
    /*
        use EtatsListTrait; // provide a function to return the static etats property defined down bellow.

        const INACTIF = 0, ACTIF = 1;
        public static $etats = ["Inactif", "Actif"];
    */

    // Defining a type with parameters
    protected $casts = [
        'id' => "integer",
        'taux_couverture' => "float",
        'prix_couverture' => "float",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idService'       => 'id',
        'tauxCouverture' => "taux_couverture",
        'prixCouverture' => "prix_couverture",
    ];

    /*
        // Bind the type to the handler
        protected $castHandlers = [
            'etatcaster' => \App\Entities\Cast\EtatCaster::class,
        ];

        public function getStatesList()
        {
            if (!isset($this->attributes['statesList'])) {
                $this->attributes['statesList'] = $this->etatsList();
            }
            return $this->attributes['statesList'];
        }
    */
}
