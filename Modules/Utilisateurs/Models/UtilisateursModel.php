<?php

namespace Modules\Utilisateurs\Models;

use CodeIgniter\Model;

class UtilisateursModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'Utilisateurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = '\Modules\Utilisateurs\Entities\UtilisateursEntity';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "id", "code", "nom", "prenom", "date_naissance", "sexe", "profession",
        "email", "tel1", "tel2", "photo_profil", "photo_cni", "profil_id",
        "ville", "etatcivil", "nbr_enfant", "documents", "user_id"
    ];
    // "facebook", "twitter", "linkedin", "documents", "membres", "etat", "statut",

    // Dates
    protected $useTimestamps = true;
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

    public function getSimplified(int $id)
    {
        return $this->select("id, code, nom, prenom, photo_profil")->where('id', $id)->first();
    }

    public function getSimplifiedArray(int $id)
    {
        $data = $this->join("images", "utilisateurs.photo_profil = images.id")
            ->asArray()->where('utilisateurs.id', $id)->first();
        return [
            "idUtilisateur" => $id,
            "code" => $data['code'],
            "nom"  => $data['nom'],
            "prenom" => $data['prenom'],
            "photoProfil" => [
                "idImage" => $data['id'],
                "url" => $data['isLink'] ? $data['isLink'] : base_url($data['uri'])
            ],
        ];
    }

    public function getBulkSimplified(array $ids)
    {
        return $this->select("id, code, nom, prenom, photo_profil")->whereIn('id', $ids)->findAll();
    }
}
