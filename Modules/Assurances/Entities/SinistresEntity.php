<?php

namespace Modules\Assurances\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use App\Traits\EtatsListTrait;
use Modules\Utilisateurs\Models\UtilisateursModel;

class SinistresEntity extends Entity
{
    use EtatsListTrait; // provide a function to return the static etats property defined down bellow.

    const INACTIF = 0, ACTIF = 1;
    public static $etats = ["Inactif", "Actif"];

    // Defining a type with parameters
    protected $casts = [
        'id'              => "integer",
        'conversation_id' => "integer",
        'souscription_id' => "integer",
        'etat'            => "etatcaster[Inactif,Actif]",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idSinistre'     => 'id',
        'auteur'         => "auteur_id",
        'type'           => "type_id",
        'idConversation' => "conversation_id",
        'idSouscription' => "souscription_id",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getTypeId()
    {
        if (isset($this->attributes['type_id']) && gettype($this->attributes['type_id']) === 'string') {
            $type = model('SinistreTypesModel')->select("nom, description")->where('id', $this->attributes['type_id'])->first();
            $this->attributes['type_id'] = $type;
        }

        return $this->attributes['type_id'] ?? null;
    }

    public function getAuteurId()
    {
        if (isset($this->attributes['auteur_id']) && gettype($this->attributes['auteur_id']) === 'string') {
            $this->attributes['auteur_id'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['auteur_id']);
        }

        return $this->attributes['auteur_id'] ?? null;
    }

    public function getImages()
    {
        if (!isset($this->attributes['images'])) {
            $imageIDs = model('SinistreImagesModel')->where('sinistre_id', $this->attributes['id'])->findColumn('image_id');
            $images   = $imageIDs ? model("ImagesModel")->select("id,uri")->whereIn('id', $imageIDs)->findAll() : [];
            // $images   = $imageIDs ? model("ImagesModel")->getMultiSimplified($imageIDs) : [];  // Marche aussi
            $this->attributes['images'] = $images;
        }

        return $this->attributes['images'];
    }

    public function getDocuments()
    {
        if (!isset($this->attributes['documents'])) {
            $documentIDs = model('SinistreDocumentsModel')->where('sinistre_id', $this->attributes['id'])->findColumn('document_id');
            $documents  = $documentIDs ? model('DocumentsModel')->whereIn('id', $documentIDs)->findAll() : [];
            $this->attributes['documents'] = $documents;
        }

        return $this->attributes['documents'];
    }

    public function getStatesList()
    {
        if (!isset($this->attributes['statesList'])) {
            $this->attributes['statesList'] = $this->etatsList();
        }
        return $this->attributes['statesList'];
    }
}
