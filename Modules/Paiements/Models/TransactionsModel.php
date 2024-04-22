<?php

namespace Modules\Paiements\Models;

use CodeIgniter\Model;
use Modules\Paiements\Entities\TransactionEntity;

class TransactionsModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = TransactionEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "code", "motif", "pay_option_id", "prix_total", "tva_taux", "valeur_tva", "net_a_payer",
        "avance", "reste_a_payer", "etat", "beneficiaire_id"
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

    public function getUserTransactions(int $userID)
    {
        $data = $this->asArray()->select('transactions.*, nom, prenom, utilisateurs.code, photo_profil')
            ->join('utilisateurs', 'transactions.beneficiaire_id = utilisateurs.id')
            ->where('beneficiaire_id', $userID)
            ->orderBy('dateCreation', 'desc')
            ->findAll();
        // foreach ($data as $transact) {
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['beneficiaire'] = [
                'nom'   => $data[$i]['nom'],
                'prenom' => $data[$i]['prenom'],
                'code'  => $data[$i]['code'],
                'photo' => $data[$i]['photo_profil'] ? base_url($data[$i]['photo_profil']) : null
            ];
            unset($data[$i]['beneficiaire_id'], $data[$i]['beneficiaire_id'], $data[$i]['nom'], $data[$i]['prenom'], $data[$i]['code'], $data[$i]['photo_profil'], $data[$i]['dateModification']);
        }
        return $data;
    }
}
