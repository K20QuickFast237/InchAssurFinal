<?php

namespace Modules\Produits\Models;

use Modules\Produits\Models\PaiementOptionsModel;

class EtagePayOptsModel extends PaiementOptionsModel
{
    protected $returnType       = 'Modules\Produits\Entities\EtagePayOptsEntity';
    protected $allowedFields    = ["type", "nom", "description", "depot_initial_taux", "etape_duree"];


    public function getSimplified($id)
    {
        return $this->select("id, nom, description, depot_initial_taux, type, etape_duree")
            ->where('id', $id)->first();
    }
}
