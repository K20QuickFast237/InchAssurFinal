<?php

namespace Modules\Consultations\Models;

use CodeIgniter\Model;
use Modules\Consultations\Entities\ConsultationEntity;

class ConsultationsModel extends Model
{
    const DEFAULT_DUREE = 30;
    const ENATTENTE = 0, VALIDE = 1, EXPIREE = 2, ENCOURS = 3, TERMINE = 4, ANNULE = 5, TRANSMIS = 6, ECHOUE = 7;
    const OFFICE_CONSULT = 0, ONLINE_CONSULT = 1, HOME_CONSULT = 2;
    public static $rdvStatut = ['En Attente', 'Validé', 'Expiré', 'En Cours', 'Terminé', 'Annulé', 'Transmis', 'Échoué', 'bilanPostAvis'];
    // public static $canneaux = ['Au Cabinet', 'En Ligne', 'A Domicile'];

    protected $DBGroup          = 'default';
    protected $table            = 'consultations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 1;
    protected $returnType       = ConsultationEntity::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['objet', 'description', 'duree', 'heure', 'prix', 'isAssured', 'bilan', 'previous_id', 'withExpertise', 'isExpertise', 'isSecondAdvice', 'statut', 'canal', 'skill', 'date', 'code', 'medecin_user_id', 'patient_user_id', 'localisation_id', 'langue', 'souscription_id'];

    // Dates
    protected $useTimestamps = true;  // autorise l'ajout automatique de la valeur à l'insertion
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'dateCreation';
    protected $updatedField  = '';
    // protected $updatedField  = 'dateModification';
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
