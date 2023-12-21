<?php

namespace Modules\Images\Entities;

use Modules\Documents\Entities\DocumentsEntity;
// use CodeIgniter\Entity\Entity;


// class ImagesEntity extends Entity
class ImagesEntity extends DocumentsEntity
{
    protected $datamap = [
        // property_name => db_column_name
        'idImage' => 'id',
        'url'     => 'uri'
    ];

    // // Defining a type with parameters
    // protected $casts = [
    //     'id'       => "integer",
    //     'uri'      => "linkcaster",
    // ];

    // // Bind the type to the handler
    // protected $castHandlers = [
    //     'linkcaster' => \App\Entities\Cast\LinkCaster::class,
    // ];
}
