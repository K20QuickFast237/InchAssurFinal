<?php

namespace Modules\Produits\Entities;

use CodeIgniter\Entity\Entity;


class ProduitDocumentsEntity extends Entity
{
    protected $datamap = [
        // property_name => db_column_name
        'idProduit'  => 'produit_id',
        'idDocument' => 'document_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'produit_id'  => "integer",
        'document_id' => "integer",
    ];
}
