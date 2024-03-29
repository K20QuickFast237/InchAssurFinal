<?php

namespace Modules\Utilisateurs\Models;

use Modules\Utilisateurs\Models\UtilisateursModel;

class MedecinsModel extends UtilisateursModel
{
    protected $returnType       = '\Modules\Utilisateurs\Entities\MedecinsEntity';
    protected $allowedFields    = [
        "id", "code", "nom", "prenom", "date_naissance", "sexe", "profession",
        "email", "tel1", "tel2", "photo_profil", "photo_cni", "etat", "statut",
        "specialisation", "ville", "etatcivil", "nbr_enfant", "documents", "user_id"
    ];
}
