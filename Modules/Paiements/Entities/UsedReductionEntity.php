<?php

namespace Paiements\Entities;

use CodeIgniter\Entity\Entity;

class UsedReductionEntity extends Entity
{
    protected $datamap = [
        "idUtilisateur" => "utilisateur_id",
        "idReduction"   => "reduction_id",
        "prixInitial"   => "prix_initial",
        "prixDeduit"    => "prix_deduit",
        "prixFinal"     => "prix_final",
    ];
    protected $casts   = [];
}
