<?php

namespace Modules\Utilisateurs\Entities;

use Modules\Utilisateurs\Entities\EntreprisesEntity;


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
        'idAssureur'  => 'id',
        'photoProfil' => 'photo_profil',
        'profil'      => 'profil_id',
        'agents'      => 'membres',
    ];
}
