<?php

namespace Modules\Produits\Models;

use CodeIgniter\Model;

class ReductionsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'reductions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = '\Modules\Produits\Entities\ReductionsEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['description', 'code', 'auteur_id', 'valeur', 'taux', 'usage_max_nombre', 'expiration_date', 'utilise_nombre'];

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

    public function getSimplified($id)
    {
        return $this->select("id, code, description, valeur, taux, usage_max_nombre, expiration_date, utilise_nombre")
            ->where('id', $id)->first();
    }
}
