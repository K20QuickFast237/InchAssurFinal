<?php

namespace Modules\Utilisateurs\Models;

use Modules\Utilisateurs\Entities\AssureursEntity;
use Modules\Utilisateurs\Models\UtilisateursModel;

class AssureursModel extends UtilisateursModel
{
    // protected $returnType = '\Modules\Utilisateurs\Entities\AssureursEntity';
    protected $returnType = AssureursEntity::class;
}
