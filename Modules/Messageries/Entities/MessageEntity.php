<?php

namespace Modules\Messageries\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use App\Traits\EtatsListTrait;

class MessageEntity extends Entity
{
    use EtatsListTrait;

    public static $etats = ["Inactif", "Actif"]; // $statuts = ["Non Lu", "Lu"];
    const INACTIVE_STATE = 0, ACTIVE_STATE = 1;
    const UNREADED = 0, READED = 1;

    // Defining a type with parameters
    protected $casts = [
        'id'     => "integer",
        'statut' => "etatcaster[Non Lu,Lu]",
        'etat'   => "etatcaster[Inactif,Actif]",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idMessage'      => 'id',
        'images'         => "image_id",
        'documents'      => "document_id",
        // 'message'        => "msg_text",
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
            // $img = model('ImagesModel')->getSimplified($this->attributes['image_id']);
            $img = model('ImagesModel')->getMultiSimplified($this->attributes['image_id']);
            $this->attributes['image_id'] = $img;
        }

        return $this->attributes['image_id'] ?? [];
    }

    public function getDocumentId()
    {
        if (isset($this->attributes['document_id']) && gettype($this->attributes['document_id']) === 'string') {
            // $doc = model('DocumentsModel')->getSimplified($this->attributes['document_id']);
            $doc = model('DocumentsModel')->getMultiSimplified((array)$this->attributes['document_id']);
            $this->attributes['document_id'] = $doc;
        }

        return $this->attributes['document_id'] ?? [];
    }
}
