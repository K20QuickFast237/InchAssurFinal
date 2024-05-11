<?php

namespace Modules\Consultations\Models;

use CodeIgniter\Model;
use Modules\Consultations\Entities\RdvEntity;

class RdvsModel extends Model
{
    const DEFAULT_DUREE = 30;
    const ENATTENTE = 0, VALIDE = 1, REFUSE = 2, ENCOURS = 3, TERMINE = 4, ANNULE = 5, TRANSMIS = 6, ECHOUE = 7;
    const OFFICE_CONSULT = 0, ONLINE_CONSULT = 1, HOME_CONSULT = 2;
    public static $rdvStatut = ['En Attente', 'Validé', 'Reporté', 'En Cours', 'Terminé', 'Annulé', 'Transmis', 'Échoué'];
    // public static $canneaux = ['Au Cabinet', 'En Ligne', 'A Domicile'];

    protected $DBGroup          = 'default';
    protected $table            = 'rdvs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 1;
    protected $returnType       = RdvEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['objet', 'duree', 'heure', 'statut', 'lieu', 'date', 'code', 'destinataire_user_id', 'emetteur_user_id'];

    // Dates
    protected $useTimestamps = true;  // autorise l'ajout automatique de la valeur à l'insertion
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = 'dateModification';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = false;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ['showStatut', 'showCanal', 'showBilan'];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
