<?php

namespace Modules\Incidents\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use App\Traits\EtatsListTrait;

class IncidentsEntity extends Entity
{
    use EtatsListTrait; // provide a function to return the static etats property defined down bellow.

    const INACTIF = 0, ACTIF = 1;
    public static $etats = ["Inactif", "Actif"];

    // Defining a type with parameters
    protected $casts = [
        'id'              => "integer",
        'conversation_id' => "integer",
        'etat'            => "etatcaster[Inactif,Actif]",
    ];

    protected $datamap = [
        // property_name => db_column_name
        'idIncident'     => 'id',
        'auteur'         => "auteur_id",
        'type'           => "type_id",
        'idConversation' => "conversation_id",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    public function getTypeId()
    {
        if (isset($this->attributes['type_id']) && gettype($this->attributes['type_id']) === 'string') {
            $type = model('IncidentTypesModel')->select("nom, description")->where('id', $this->attributes['type_id'])->first();
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
            $imageIDs = model('IncidentImagesModel')->where('incident_id', $this->attributes['id'])->findColumn('image_id');
            $images   = $imageIDs ? model("ImagesModel")->select("id,uri")->whereIn('id', $imageIDs)->findAll() : [];
            $this->attributes['images'] = $images;
        }

        return $this->attributes['images'];
    }

    public function getStatesList()
    {
        if (!isset($this->attributes['statesList'])) {
            $this->attributes['statesList'] = $this->etatsList();
        }
        return $this->attributes['statesList'];
    }
}
