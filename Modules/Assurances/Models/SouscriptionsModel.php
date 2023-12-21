<?php

namespace Modules\Assurances\Models;

use CodeIgniter\Model;

class SouscriptionsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'souscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = '\Modules\Assurances\Entities\SouscriptionsEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["code", "cout", "souscripteur_id", "assurance_id", "etat", "dateDebutValidite", "dateFinValidite"];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = '';
    protected $deletedField  = '';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

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

    public function getBasePrice($id)
    {
        return $this->join('assurances', 'assurances.id = souscriptions.assurance_id')
            ->where('souscriptions.id', $id)
            ->findColumn('assurances.prix');
    }
}
