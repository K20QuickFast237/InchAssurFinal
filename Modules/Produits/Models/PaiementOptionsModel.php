<?php

namespace Modules\Produits\Models;

use CodeIgniter\Model;

class PaiementOptionsModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'paiement_options';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = '\Modules\Produits\Entities\PaiementOptionsEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "type", "nom", "description", "depot_initial_taux", "montant_cible",
        "cycle_longueur", "cycle_nombre", "cycle_taux", "etape_duree",
    ];

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
        return $this->select("id, nom, description, depot_initial, cycle_longueur, cycle_taux, cycle_nombre, etape_duree")
            ->where('id', $id)->first();
    }

    public function getUltraSimplified($id)
    {
        return $this->select("id, type, nom, description")->where('id', $id)->first();
    }
}
