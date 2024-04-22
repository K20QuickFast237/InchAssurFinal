<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class TransactionEntity extends Entity
{
    const INITIE = 0, TERMINE = 1, EN_COURS = 2;
    public static $etats = ["Initiée", "Terminée", "En Cours"];

    protected $datamap  = [
        'idTransaction' => 'id',
        'prixTotal'     => 'prix_total',
        'tauxTVA'       => 'tva_taux',
        'valeurTVA'     => 'valeur_tva',
        'paiementOption' => 'pay_option_id',
        'beneficiaire'  => 'beneficiaire_id',
        // 'netAPayer'   => 'net_a_payer',
        // 'resteAPayer' => 'reste_a_payer',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'            => "integer",
        'net_a_payer'   => "?float",
        'reste_a_payer' => "?float",
        'prix_total'    => "?float",
        'tva_taux'      => "?float",
        'valeur_tva'    => "?float",
        'avance'        => "?float",
        'cout'          => "?float",
        'etat'          => "etatcaster[Initiée,Terminée,En Cours]",
    ];

    // Bind the type to the handler
    protected $castHandlers = [
        'etatcaster' => \App\Entities\Cast\EtatCaster::class,
    ];

    /**
     * renvoie l'option de paiement associée à cette transaction
     *
     * @return object les infos de l'option de paiement
     */
    public function getPayOptionId()
    {
        if (isset($this->attributes['pay_option_id']) && gettype($this->attributes['pay_option_id']) === 'string') {
            $this->attributes['pay_option_id'] = model('PaiementOptionsModel')->getUltraSimplified($this->attributes['pay_option_id']);
        }

        return $this?->attributes['pay_option_id'];
    }

    /**
     * getBeneficiaireId
     * 
     * renvoie le beneficiaire associée à cette souscription
     *
     * @return object les données du beneficiaire
     */
    public function getBeneficiaireId()
    {
        if (isset($this->attributes['beneficiaire_id']) && gettype($this->attributes['beneficiaire_id']) === 'string') {
            $this->attributes['beneficiaire_id'] = model('UtilisateursModel')->getSimplified($this->attributes['beneficiaire_id']);
        }

        return $this?->attributes['beneficiaire_id'];
    }

    public function getLignes()
    {
        if (!isset($this->attributes['lignes'])) {
            $ligneIDs = model('TransactionLignesModel')->where('transaction_id', $this->attributes['id'])->findColumn('ligne_id');
            $lignes   = $ligneIDs ? model("LignetransactionsModel")->whereIn('id', $ligneIDs)->findAll() : [];
            $this->attributes['lignes'] = $lignes;
        }

        return $this->attributes['lignes'];
    }

    public function getPaiements()
    {
        if (!isset($this->attributes['paiements'])) {
            $paiements = model("PaiementsModel")->where('transaction_id', $this->attributes['id'])->findAll();
            $this->attributes['paiements'] = $paiements;
        }

        return $this->attributes['paiements'];
    }
}
