<?php

namespace Modules\Produits\Entities;

use App\Traits\ParamListTrait;
use CodeIgniter\Entity\Entity;


class ProduitsEntity extends Entity
{
    use ParamListTrait; // provide a function to return the static statuts property defined down bellow.

    protected $datamap = [
        // property_name => db_column_name
        'idProduit' => 'id',
        'categorie' => 'categorie_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'   => "integer",
        'etat' => "etatcaster[Inactif,Actif]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\ListCaster::class,
    ];

    /**
     * getDocumentation
     * 
     * renvoie la documentation associée à ce produit
     *
     * @return array une liste de documents constituant la documentation
     */
    public function getDocumentation()
    {
        if (!isset($this->attributes['documentation'])) {
            $documentIDs = model('ProduitDocumentsModel')->asArray()
                ->where('produit_id', $this->attributes['id'])->findAll();

            $documentation = model('DocumentsModel')->whereIn('id', $documentIDs)->findAll();

            $this->attributes['documentation'] = $documentation;
        }

        return $this->attributes['documentation'];
    }

    /**
     * getReductions
     * 
     * renvoie les Reductions associées à ce produit
     *
     * @return array une liste de réductions associée au produit
     */
    public function getReductions()
    {
        if (!isset($this->attributes['reductions'])) {
            $reductionIDs = model('ProduitReductionsModel')->asArray()
                ->where('produit_id', $this->attributes['id'])->findAll();

            $reductions = model('ReductionsModel')->whereIn('id', $reductionIDs)->findAll();

            $this->attributes['reductions'] = $reductions;
        }

        return $this->attributes['reductions'];
    }
}
