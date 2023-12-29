<?php

namespace Modules\Utilisateurs\Models;

use Modules\Utilisateurs\Models\UtilisateursModel;

class EntreprisesModel extends UtilisateursModel
{
    protected $returnType       = '\Modules\Utilisateurs\Entities\EntreprisesEntity';
    protected $allowedFields    = [
        "id", "code", "nom", "email", "tel1", "tel2", "photo_profil", "etat", "statut",
        "ville", "documents", "membres", "user_id"
    ];
}
