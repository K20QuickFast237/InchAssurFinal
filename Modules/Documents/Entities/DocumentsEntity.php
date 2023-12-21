<?php

namespace Modules\Documents\Entities;

use CodeIgniter\Entity\Entity;


class DocumentsEntity extends Entity
{
    protected $datamap = [
        // property_name => db_column_name
        'idDocument' => 'id',
        'url'        => 'uri'
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'       => "integer",
        'uri'      => "linkcaster",
        'isLink'   => "int-bool",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'linkcaster' => \App\Entities\Cast\LinkCaster::class,
    ];
}
