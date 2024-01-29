<?php

namespace Modules\Assurances\Models;

// use CodeIgniter\Model;
use Modules\Produits\Models\ProduitsModel;

// class AssurancesModel extends Model
class AssurancesModel extends ProduitsModel
{
    protected $table            = 'assurances';
    protected $primaryKey       = 'id';
    protected $returnType       = '\Modules\Assurances\Entities\AssurancesEntity';
    protected $allowedFields    = [
        "nom", "code", "description", "short_description", "prix", "image_id", "type_id", "duree", "etat",
        "pieces_a_joindre", "assureur_id", "categorie_id", "services", "listeReductions"
    ];
    //  "type_contrat",

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
    protected $beforeInsert   = ['add_default_piece_jointe'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function add_default_piece_jointe(array $data)
    {
        if (!isset($data['data']['pieces_a_joindre'])) {
            $data['data']['pieces_a_joindre'] = json_encode([]);

            return $data;
        }
    }

    public function getSimplified($id)
    {
        return $this->select("id, code, nom, description, short_description")->where('id', $id)->first();
    }
}
