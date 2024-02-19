<?php

namespace Modules\Assurances\Models;

use CodeIgniter\Model;
use Modules\Assurances\Entities\SinistresEntity;

class SinistresModel extends Model
{
    protected $table            = 'sinistres';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = SinistresEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "code", "titre", "description", "etat", "auteur_id", "type_id", "souscription_id", "conversation_id"
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
