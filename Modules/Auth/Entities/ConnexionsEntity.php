<?php

namespace Modules\Auth\Entities;


use CodeIgniter\Shield\Entities\UserIdentity as ShieldConnexionsEntity;

class ConnexionsEntity extends ShieldConnexionsEntity
{
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => '?integer',
        'force_reset' => 'int_bool',
        'codeconnect' => 'string',
    ];
}
