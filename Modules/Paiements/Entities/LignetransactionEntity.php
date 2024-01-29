<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class LignetransactionEntity extends Entity
{
    protected $datamap = [
        'idLigneTransaction' => 'id',
        'idProduit'          => 'produit_id',
        'idSouscription'     => 'souscription_id',
        'codeReduction'      => 'reduction_code',
        "prixUnitaire"       => 'prix_unitaire',
        "prixTotal"          => 'prix_total',
        "prixReduction"      => 'prix_reduction',
        "prixTotalNet"       => 'prix_total_net',
    ];
}
