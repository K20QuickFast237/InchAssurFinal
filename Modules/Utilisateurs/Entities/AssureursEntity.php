<?php

namespace Modules\Utilisateurs\Entities;

use Modules\Utilisateurs\Entities\EntreprisesEntity;
use \App\Entities\Cast\LinkCaster;


class AssureursEntity extends EntreprisesEntity
{
    /* Les attributs de la class (Pour reference)
        - idEntreprise         (requis)
        - code                 (requis)
        - nom                  (requis)
        - email                (requis)
        - tel1                 (requis)
        - tel2                 (requis)
        - photo_profil         
        - etat                 (requis)
        - statut               (requis)
        - ville                
        - documents            (requis)
        - agents             
        - socialLinks          
    */
    protected $datamap = [
        // property_name => db_column_name
        'idAssureur' => 'id',
        // 'logo'       => 'photoProfil',
        'logo'       => 'photo_profil',
        // 'profil'     => 'profil_id',
        'agents'     => 'membres',
    ];

    // // Defining a type with parameters
    // protected $casts = [
    //     'photo_profil' => "imgcaster",
    // ];

    // // Bind the type to the handler
    // protected $castHandlers = [
    //     'imgcaster'  => LinkCaster::class,
    // ];

    /**
     * renvoie la liste des images de l'assurance
     *
     * @return array
     */
    public function getPhotoProfil()
    {
        if (isset($this->attributes['photo_profil']) && gettype($this->attributes['photo_profil']) === 'string') {
            $img = model("ImagesModel")->select("id,uri")->where('id', $this->attributes['photo_profil'])->first();
            $this->attributes['photo_profil'] = $img;
        }

        return $this->attributes['photo_profil'] ?? null;
    }

    public function getSimplified()
    {
        $exple = clone $this;
        $exple->nom = $exple->nom . ' ' . $exple->prenom;
        unset($exple->prenom);
        return $exple;
    }
}
