<?php

namespace Modules\Produits\Models;

use Modules\Produits\Models\PaiementOptionsModel;

class UniquePayOptsModel extends PaiementOptionsModel
{
    protected $returnType       = 'Modules\Produits\Entities\UniquePayOptsEntity';
    protected $allowedFields    = ["type", "nom", "description", "depot_initial_taux"];


    public function getSimplified($id)
    {
        return $this->select("id, nom, description, depot_initial_taux, type")
            ->where('id', $id)->first();
    }
}
