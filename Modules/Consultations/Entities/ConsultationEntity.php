<?php

namespace Modules\Consultations\Entities;

use App\Traits\EtatsListTrait;
use CodeIgniter\Entity\Entity;
use Modules\Paiements\Entities\TransactionEntity;

class ConsultationEntity extends Entity
{
    use EtatsListTrait;

    const DEFAULT_DUREE = 30, EPIRATION_TIME = 3;
    const ENATTENTE = 0, VALIDE = 1, EXPIREE = 2, // REFUSE = 2,
        ENCOURS = 3, TERMINE = 4, ANNULE = 5, TRANSMIS = 6, ECHOUE = 7;
    const statuts = ["En Attente", "Validé", "Expiré", "En Cours", "Terminé", "Annulé", "Transmis", "Échoué"];

    protected $datamap = [
        'idConsultation' => 'id',
        'localisation'   => 'localisation_id',
        'medecin'        => 'medecin_user_id',
        'patient'        => 'patient_user_id',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'duree'          => "integer",
        'prix'           => "float",
        'withExpertise'  => "?boolean",
        'isSecondAdvice' => "?boolean",
        'isAssured'      => "?boolean",
        'isExpertise'    => "?boolean",
        'statut'         => "etatcaster[En Attente,Validé,Expiré,En Cours,Terminé,Annulé,Transmis,Échoué]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];
    public function showServiceInfo()
    {
        return model('ServicesModel')->where('nom', 'Consultations')->first();
    }

    public function getMedecinUserId()
    {
        if (isset($this->attributes['medecin_user_id']) && gettype($this->attributes['medecin_user_id']) === 'string') {
            $this->attributes['medecin'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['medecin_user_id']);
        }

        return $this->attributes['medecin'];
    }
    public function getPatientUserId()
    {
        if (isset($this->attributes['patient_user_id']) && gettype($this->attributes['patient_user_id']) === 'string') {
            $this->attributes['patient'] = model("UtilisateursModel")->getSimplifiedArray($this->attributes['patient_user_id']);
        }

        return $this->attributes['patient'];
    }
    public function getExpertise()
    {
        if (!isset($this->attributes['expertise'])) {
            $this->attributes['expertise'] = model("AvisExpertModel")->where('consultation_id', $this->attributes['id'])->first();
            $this->attributes['withExpertise'] = (bool)$this->attributes['expertise'];
        }

        return $this->attributes['expertise'];
    }

    public function getLocalisationId()
    {
        if (isset($this->attributes['localisation_id']) && gettype($this->attributes['localisation_id']) === 'string') {
            $this->attributes['localisation_id'] = model("LocalisationsModel")->find($this->attributes['localisation_id']);
        }

        return $this->attributes['localisation_id'];
    }

    public function getDocuments()
    {
        if (!isset($this->attributes['documents'])) {
            $docIds = model("ConsultationDocumentsModel")->where('consultation_id', $this->attributes['id'])->findColumn('document_id');
            $this->attributes['documents'] = $docIds ? model("DocumentsModel")->getMultiSimplified($docIds) : [];
        }

        return $this->attributes['documents'];
    }
    public function getPrevious()
    {
        if (!isset($this->attributes['previous'])) {
            $previous = model("ConsultationsModel")->find($this->attributes['previous_id']);
            $previous->documents;
            $this->attributes['previous'] = $previous;
        }

        return $this->attributes['previous'];
    }

    public function getTransaction()
    {
        if (!isset($this->attributes['transaction'])) {
            $transaction = model('TransactionsModel')
                ->join('transaction_lignes', 'transactions.id=transaction_id', 'left')
                ->join('lignetransactions',  'ligne_id=lignetransactions.id', 'left')
                ->select('transactions.id as idTransaction, transactions.code, net_a_payer, reste_a_payer, transactions.etat')
                ->where('produit_group_name', 'Consultation')
                ->where('produit_id', $this->attributes['id'])
                ->asArray()->first();
            if ($transaction) {
                $transaction["idTransaction"] = (int)$transaction["idTransaction"];
                $transaction["net_a_payer"] = (float)$transaction["net_a_payer"];
                $transaction["reste_a_payer"] = (float)$transaction["reste_a_payer"];
                $transaction["etat"] = TransactionEntity::$etats[(int)$transaction["etat"]];
            }
            $this->attributes['transaction'] = $transaction;
        }

        return $this->attributes['transaction'];
    }
}
