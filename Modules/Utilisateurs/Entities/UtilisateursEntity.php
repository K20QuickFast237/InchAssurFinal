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

    protected $datamap = [
        // property_name => db_column_name
        'idUtilisateur' => 'id',
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
        // 'etat'   => "etatcaster['Hors Ligne','En Ligne']",
        // 'statut' => "etatcaster['Inactif','Actif','Bloqué','Archivé']",
        'photo_profil' => "imgcaster",
        'photo_cni'    => "imgcaster",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'imgcaster'  => \App\Entities\Cast\LinkCaster::class,
        // 'etatcaster' => \App\Entities\Cast\ListCaster::class,
    ];

    public function getProfils()
    {
        if (!isset($this->attributes['profils'])) {
            $profils = model("UtilisateurProfilsModel")->join("profils", "profils.id=profil_id")
                ->where("utilisateur_id", $this->attributes['id'])
                ->findAll();
            $profils = array_map(fn ($p) => ["id" => $p->niveau, "value" => $p->titre, "default" => (bool)$p->defaultProfil], $profils);
            $this->attributes['profils'] = $profils;
        }

        return $this->attributes['profils'];
    }

    public function getDefaultProfil()
    {
        if (!isset($this->attributes['defaultProfil'])) {
            foreach ($this->profils as $profil) {
                if ($profil["default"]) {
                    $this->attributes['defaultProfil'] = $profil;
                }
            }
        }
        return $this->attributes['defaultProfil'] ?? null;
    }
}
