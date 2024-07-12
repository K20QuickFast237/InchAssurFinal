<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class PayOptionEntity extends Entity
{
    const UNIQUE_PAY_OPT = "Unique", ECHEANCE_PAY_OPT = "A Echéance", PLANED_PAY_OPT = "Planifié";
    // Defining a type with parameters
    protected $casts = [
        'id'             => "integer",
        'etape_duree'    => "integer",
        'cycle_longueur' => "integer",
        'cycle_nombre'   => "integer",
        'cycle_taux'     => "integer",
        'depot_initial_taux' => "integer",
    ];

    public function get_initial_amount_from_option($unitPrice)
    {
        switch ($this->type) {
            case self::UNIQUE_PAY_OPT:
                return $unitPrice;
            case self::ECHEANCE_PAY_OPT:
                return ($unitPrice * $this->depot_initial_taux) / 100;
            case self::PLANED_PAY_OPT:
                if (isset($this->depot_initial_taux)) {
                    return ($unitPrice * $this->depot_initial_taux) / 100;
                }
                return ($unitPrice * $this->cycle_taux) / 100;
        }
    }

    public function get_nextStepAmount(object $transaction)
    {
        switch ($this->type) {
            case self::UNIQUE_PAY_OPT:
                return 0;
            case self::ECHEANCE_PAY_OPT:
                return null;
            case self::PLANED_PAY_OPT:
                if ($transaction->reste_a_payer <= 0) {
                    return 0;
                }
                if (isset($this->cycle_taux)) {
                    $result = ($transaction->net_a_payer * $this->cycle_taux) / 100;
                    return $transaction->reste_a_payer <= $result ? $transaction->reste_a_payer : $result;
                }
                $netToPay   = (float)$transaction->net_a_payer;
                $initAmount = $this->get_initial_amount_from_option($netToPay);
                $result     = ($netToPay - $initAmount) / ($this->cycle_nombre - 1);
                return $transaction->reste_a_payer <= $result ? $transaction->reste_a_payer : $result;
        }
    }
}
