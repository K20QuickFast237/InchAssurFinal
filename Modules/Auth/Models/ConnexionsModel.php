<?php

namespace Modules\Auth\Models;

// use CodeIgniter\Model;
use Modules\Auth\Entities\ConnexionsEntity;
use CodeIgniter\Shield\Models\UserIdentityModel;

class ConnexionsModel extends UserIdentityModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->returnType    = ConnexionsEntity::class;
        $this->allowedFields = [
            ...$this->allowedFields,
            "codeconnect",
        ];
    }
}
