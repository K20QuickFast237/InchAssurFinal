<?php

namespace Modules\Produits\Models;

use Modules\Produits\Models\PaiementOptionsModel;

class PlanifiePayOptsModel extends PaiementOptionsModel
{
    protected $returnType       = 'Modules\Produits\Entities\EtagePayOptsEntity';
    protected $allowedFields    = ["type", "nom", "description", "depot_initial_taux", "cycle_longueur", "cycle_taux", "cycle_nombre"];


    public function getSimplified($id)
    {
        return $this->select("id, nom, description, depot_initial_taux, type, cycle_longueur, cycle_taux, cycle_nombre")
            ->where('id', $id)->first();
    }
}
