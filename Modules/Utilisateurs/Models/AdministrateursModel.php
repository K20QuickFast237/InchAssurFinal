<?php

namespace Modules\Utilisateurs\Models;

use Modules\Utilisateurs\Models\UtilisateursModel;

class AdministrateursModel extends UtilisateursModel
{
    protected $returnType       = '\Modules\Utilisateurs\Entities\AdministrateursEntity';
    protected $allowedFields    = ["id", "code", "nom", "prenom", "email", "tel1", "tel2", "photo_profil", "etat", "statut", "user_id"];
}
