<?php

namespace Modules\Assurances\Models;

use CodeIgniter\Model;
use Modules\Assurances\Entities\SouscriptionServiceEntity;

class SouscriptionServicesModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'souscription_services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = SouscriptionServiceEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['souscription_id', 'service_id', 'etat', 'quantite_utilise', 'prix_couvert'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = 'dateModification';
    protected $deletedField  = 'dateSuppression';

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
