<?php

namespace Modules\Consultations\Models;

use CodeIgniter\Model;

class SkillsMotifModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'skills_motif';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 1;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['skill_id', 'motif_id'];

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

    public function findBulkMotifs(array $skillIds)
    {
        // $data = $this->select('id_motif as id, nomMotif as motif, description')
        $data = $this->select('distinct(motifs.id), motifs.nom, description, skill_id')
            ->join('motifs', 'motif_id = motifs.id', 'left')
            ->whereIn('skill_id', $skillIds)
            // ->orderBy('id', 'ASC')
            // ->orderBy('nom', 'ASC')
            ->findAll();
        return $data;
    }
}
