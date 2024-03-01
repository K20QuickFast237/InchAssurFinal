<?php

namespace Modules\Messageries\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use App\Traits\EtatsListTrait;

class ConversationEntity extends Entity
{
    use EtatsListTrait;

    const TYPE_INCIDENT = "Incident",
        TYPE_SINISTRE = "Sinistre",
        TYPE_Groupe = "Groupe",
        TYPE_MESSAGE = "Message",
        TYPE_AUTRE = "Autre";
    const INACTIF = 0, ACTIF = 1;
    const DEFAULT_USER_IMAGE = 'uploads/images/default/user_img.jpg',
        DEFAULT_GROUP_IMGAE = 'uploads/images/default/group_img.png';
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

    public function getImageId()
    {
        // if (isset($this->attributes['image_id']) && gettype($this->attributes['image_id']) === 'string') {
        // if (isset($this->attributes['image_id']) && preg_match('/[integer|string]/i', gettype($this->attributes['image_id']))) {
        if (!isset($this->attributes['image_id'])) {
            $image = isset($this->attributes['image_id'])
                ? model("ImagesModel")->where('id', $this->attributes['image_id'])->findColumn('uri')
                : null;
            if ($image) {
                $this->attributes['image_id'] = base_url($image[0]);
            } elseif ($this->attributes['type'] == self::TYPE_Groupe) {
                $this->attributes['image_id'] = base_url(self::DEFAULT_GROUP_IMGAE);
            } else {
                $this->attributes['image_id'] = base_url(self::DEFAULT_USER_IMAGE);
            }
        }

        return $this->attributes['image_id'] ?? null;
    }

    public function getMembres()
    {
        if (!isset($this->attributes['membres'])) {
            $membreInfos = model("ConversationMembresModel")
                ->select("isAdmin, membre_id")
                ->where("conversation_id", $this->attributes['id'])
                ->findAll();
            $membres = model("UtilisateursModel")->getBulkSimplifiedArray(array_column($membreInfos, "membre_id"));

            // $this->attributes['membres'] = array_map(function ($info) use ($membres) {
            //     $membre = array_values(
            //         array_filter($membres, fn ($e) => $e["idUtilisateur"] == $info['membre_id'])
            //     );
            //     $membre["0"]['isAdmin'] = (bool)$info['isAdmin'];
            //     return $membre["0"];
            // }, $membreInfos);

            foreach ($membreInfos as $info) {
                $membre = array_values(
                    array_filter($membres, fn ($e) => $e["idUtilisateur"] == $info['membre_id'])
                );
                $membre[0]['isAdmin'] = (bool)$info['isAdmin'];
                $this->attributes['membres'][] = $membre[0];
            }
        }

        return $this->attributes['membres'] ?? null;
    }

    public function getMessages()
    {
        if (!isset($this->attributes['messages'])) {
            $messages = model("MessagesModel")
                ->select("messages.*, message_images.image_id, message_documents.document_id")
                ->join("message_images", "messages.id = message_images.message_id", "left")
                ->join("message_documents", "messages.id = message_documents.message_id", "left")
                ->where("conversation_id", $this->attributes['id'])
                ->where("messages.id", $this->attributes['id'])
                ->findAll();
            foreach ($messages as $msg) {
                $this->attributes['messages'][] = new MessageEntity($msg);
            }
        }
        return $this->attributes['messages'] ?? null;
    }
}
