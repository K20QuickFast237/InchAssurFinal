<?php

namespace Modules\Utilisateurs\Entities;

use CodeIgniter\Entity\Entity;

class PortefeuillesEntity extends Entity
{
    protected $datamap = [
        // property_name => db_column_name
        'idPortefeuille' => 'id',
        'idUtilisateur'  => 'utilisateur_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'utilisateur_id' => "integer",
        'solde'          => "float",
    ];

    public function debit(float $amount)
    {
        // Vérifier que le portefeuille couvre les frais
        if ($this->attributes['solde'] > $amount) {
            $this->attributes['solde'] = $this->attributes['solde'] - $amount;
            model('PortefeuillesModel')->update($this->attributes['id'], ['solde' => $this->attributes['solde']]);
        } else {
            throw new \Exception("Solde du portefeuille insuffisant.", 1);
        }
    }
}
