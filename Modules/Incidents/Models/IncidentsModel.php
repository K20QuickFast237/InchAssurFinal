<?php

namespace Modules\Incidents\Models;

use CodeIgniter\Model;
use Modules\Incidents\Entities\IncidentsEntity;

class IncidentsModel extends Model
{
    protected $table            = 'incidents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = IncidentsEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["code", "titre", "description", "etat", "type_id", "auteur_id", "conversation_id"];

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
