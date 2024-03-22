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
        'idMessage'      => 'id',
        'image'          => "image_id",
        'message'        => "msg_text",
        'idConversation' => "to_conversation_id",
        'idAuteur'       => "from_user_id",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getImageId()
    {
        if (isset($this->attributes['image_id']) && gettype($this->attributes['image_id']) === 'string') {
            $img = model('ImagesModel')->getSimplified($this->attributes['image_id']);
            $this->attributes['image_id'] = $img;
        }

        return $this->attributes['image_id'] ?? null;
    }

    // public function getDocumentId(){

    // }
}
