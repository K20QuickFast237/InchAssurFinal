<?php

namespace Modules\Utilisateurs\Entities;

use App\Traits\ParamListTrait;
use CodeIgniter\Entity\Entity;
// use CodeIgniter\Shield\Entities\User as ShieldUserEntity;

class UtilisateursEntity extends Entity
// class UtilisateursEntity extends ShieldUserEntity
{
    use ParamListTrait; // provide a function to return the static statuts property defined down bellow.

    /* Les attributs de la class (Pour reference)
        - idUtilisateur        (requis)
        - code                 (requis)
        - nom                  (requis)
        - prenom               (requis)
        - date_naissance       
        - sexe                 
        - profession           
        - email                (requis)
        - tel1                 (requis)
        - tel2                 (requis)
        - photo_profil         
        - photo_cni            
        - etat                 (requis)
        - statut               (requis)
        - ville                
        - etatcivil            
        - nbr_enfant           
        - documents            (requis)
        - membres              
        - socialLinks          
    */
    protected function initialize(): void
    {
    }

    // protected $dates = ['dateNaissance'];
    protected $datamap = [
        // property_name => db_column_name
        'idUtilisateur' => 'id',
        'profil'        => 'profil_id',
        'photoProfil'   => 'photo_profil',
        'photoCni'      => 'photo_cni',
        'etatCivil'     => 'etatcivil',
        'nbrEnfant'     => 'nbr_enfant',
        'dateNaissance' => 'date_naissance',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'     => "integer",
        'tel1'   => "integer",
        'tel2'   => "integer",
        'etat'   => "etatcaster['Hors Ligne','En Ligne']",  // Not more really using
        'statut' => "etatcaster['Inactif','Actif','Bloqué','Archivé']", // Not more really using
        'photo_profil'   => "imgcaster",
        'photo_cni'      => "imgcaster",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'imgcaster'  => \App\Entities\Cast\LinkCaster::class,
        // 'etatcaster' => \App\Entities\Cast\ListCaster::class,
    ];

    /**
     * retrive the default profil of current user
     *
     * @return array
     */
    public function getProfilId()
    {
        if (isset($this->attributes['profil_id']) && gettype($this->attributes['profil_id']) === 'string') {
            $profil = model("ProfilsModel")->where('id', $this->attributes['profil_id'])->first();
            $this->attributes['profil_id'] = ["id" => $profil->niveau, "value" => $profil->titre];
        }

        return $this->attributes['profil_id'] ?? null;
    }

    /**
     * Retrieve all current user's profils
     *
     * @return array
     */
    public function getProfils()
    {
        if (!isset($this->attributes['profils'])) {
            $profils = model("UtilisateurProfilsModel")->join("profils", "profils.id=profil_id")
                ->where("utilisateur_id", $this->attributes['id'])
                ->findAll();
            $profils = array_map(fn ($p) => ["id" => $p->niveau, "value" => $p->titre], $profils); // "default" => (bool)$p->defaultProfil
            $this->attributes['profils'] = $profils;
        }

        return $this->attributes['profils'];
    }

    /**
     * An other way to get the current user's default profil
     *
     * @return array
     */
    public function getDefaultProfil()
    {
        // if (!isset($this->attributes['defaultProfil'])) {
        //     foreach ($this->profils as $profil) {
        //         if ($profil["default"]) {
        //             $this->attributes['defaultProfil'] = $profil;
        //         }
        //     }
        // }
        return $this->attributes['profil'] ?? null;
    }

    /**
     * retrieve all current user's members
     *
     * @return array
     */
    public function getMembres()
    {
        if (!isset($this->attributes['membres'])) {
            $memberIDs = model("UtilisateurMembresModel")
                ->where("utilisateur_id", $this->attributes['id'])
                ->findColumn('membre_id');
            $this->attributes['membres'] = $memberIDs ? model('UtilisateursModel')
                ->select("id, code, nom, prenom, date_naissance, email, photo_profil")
                ->whereIn("id", $memberIDs)
                ->findAll()
                : null;
        }
        return $this->attributes['membres'];
    }

    /**
     * Retrieve the current user's pocket
     *
     * @return void
     */
    public function getPocket()
    {
        if (!isset($this->attributes['pocket'])) {
            $this->attributes['pocket'] = model("PortefeuillesModel")->where('utilisateur_id', $this->attributes['id'])->first();
        }
        return $this->attributes['pocket'];
    }
}
