<?php

namespace Modules\Produits\Entities;

use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;


class CategorieProduitsEntity extends Entity
{
    /*
        use EtatsListTrait; // provide a function to return the static etats property defined down bellow.

        const INACTIF = 0, ACTIF = 1;
        public static $etats = ["Inactive", "Active"];
    */

    // Defining a type with parameters
    protected $casts = [
        'id'    => "integer",
        // 'tag' => "json",
        // 'etat' => "etatcaster[Inactive,Active]",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idCategorie' => 'id',
        'image'       => 'image_id',
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'linkcaster' => \App\Entities\Cast\LinkCaster::class,
    ];

    public function getImage()
    {
        if (!isset($this->attributes['image'])) {
            return model("ImagesModel")->where('id', $this->attributes['id'])->first();
        }
        return $this?->attributes['image'];
    }
    public function getImageId()
    {
        if (isset($this->attributes['image_id']) && preg_match('/[integer|string]/i', gettype($this->attributes['image_id']))) {
            $img = model('ImagesModel')->getSimplified($this->attributes['image_id']);
            $this->attributes['image_id'] = $img;
        }

        return $this->attributes['image_id'] ?? null;
    }
    /*
        
        public function getStatesList()
        {
            if (!isset($this->attributes['statesList'])) {
                $this->attributes['statesList'] = $this->etatsList();
            }
            return $this->attributes['statesList'];
        }
    */
}
