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
        // 'netAPayer'   => 'net_a_payer',
        // 'resteAPayer' => 'reste_a_payer',
    ];

    // Defining a type with parameters
    protected $casts = [
        'id'   => "integer",
        'cout' => "float",
        'etat' => "etatcaster[Initiée,Terminée,En Cours]",
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
}
