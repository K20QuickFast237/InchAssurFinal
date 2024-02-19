<?php

namespace Modules\Messageries\Entities;

// use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;


class ConversationEntity extends Entity
{
    const TYPE_INCIDENT = 1, TYPE_SINISTRE = 2, TYPE_Groupe = 3, TYPE_MESSAGE = 4, TYPE_AUTRE = 5;
}
