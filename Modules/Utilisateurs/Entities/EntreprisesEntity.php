<?php

namespace Modules\Utilisateurs\Entities;

use Modules\Utilisateurs\Entities\UtilisateursEntity;


class EntreprisesEntity extends UtilisateursEntity
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
        - employes             
        - socialLinks          
    */

    protected $datamap = [
        // property_name => db_column_name
        'idEntreprise' => 'id',
        'photoProfil'  => 'photo_profil',
        'employes'     => 'membres',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'     => "integer",
        'tel1'   => "integer",
        'tel2'   => "integer",
        'membres' => "json",
        'etat'   => "etatcaster['Hors Ligne','En Ligne']",
        'statut' => "etatcaster['Inactif','Actif','Bloqué','Archivé']",
        'photo_profil' => "imgcaster",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\ListCaster::class,
        'imgcaster'  => \App\Entities\Cast\LinkCaster::class,
    ];
}
