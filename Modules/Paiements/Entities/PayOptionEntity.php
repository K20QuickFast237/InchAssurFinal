<?php

namespace Modules\Paiements\Entities;

use CodeIgniter\Entity\Entity;

class PayOptionEntity extends Entity
{
    const UNIQUE_PAY_OPT = "Unique", ECHEANCE_PAY_OPT = "A Echéance", PLANED_PAY_OPT = "Planifié";

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
}
