<?php

namespace Modules\Produits\Models;

use CodeIgniter\Model;

class PaiementOptionListsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'paiement_options_lists';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    // protected $returnType       = '\Modules\Produits\Entities\PaiementOptionListsEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nom', 'description'];

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

    public function getEntityName($type)
    {
        if ($type === null) {
            return 'Modules\Produits\Entities\PaiementOptionsEntity';
        }
        $name = explode(' ', $type)[1];
        $name = str_replace('é', 'e', $name);

        $className = 'Modules\Produits\Entities\\' . $name . 'PayOptsEntity';
        return $className;
    }

    public function getModelName($type)
    {
        if ($type === null) {
            return 'PaiementOptionsModel';
        }
        $name = explode(' ', $type)[1];
        $name = str_replace('é', 'e', $name);

        $className = $name . 'PayOptsModel';
        return $className;
    }
    /*
        public function getEntity($type, $data)
        {
            $name = explode(' ', $type)[1];
            $name = str_replace('é', 'e', $name);

            $className = 'Modules\Produits\Entities\\' . $name . 'PayOptsEntity';
            return new $className($data);
        }
    */
}
