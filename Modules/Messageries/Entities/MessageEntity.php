<?php

namespace Modules\Messageries\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use App\Traits\EtatsListTrait;

class MessageEntity extends Entity
{
    use EtatsListTrait;

    public static $etats = ["Inactif", "Actif"];

    // Defining a type with parameters
    protected $casts = [
        'id'              => "integer",
        'etat'            => "etatcaster[Inactif,Actif]",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idConversation' => 'id',
        'image'          => "image_id",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];
}
