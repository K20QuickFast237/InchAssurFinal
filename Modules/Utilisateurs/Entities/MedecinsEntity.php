<?php

namespace Modules\Utilisateurs\Entities;

use Modules\Utilisateurs\Entities\UtilisateursEntity;


class MedecinsEntity extends UtilisateursEntity
{
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

    protected $datamap = [
        // property_name => db_column_name
        'idMedecin'   => 'id',
        'photoProfil' => 'photo_profil',
        'profil'      => 'profil_id',
        'photoCni'    => 'photo_cni',
        'etatCivil'   => 'etatcivil',
    ];
}
