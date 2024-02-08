<?php

declare(strict_types=1);

namespace App\Config;

use CodeIgniter\Config\BaseConfig;


class Notify extends BaseConfig
{
    /**
     * --------------------------------------------------------------------
     * View files
     * --------------------------------------------------------------------
     */
    public array $views = [
        'suscription_normaly_ended_email' => 'Modules\Assurances\Views\normalEndSubscriptEmail',
        'suscription_early_ended_email'   => 'Modules\Assurances\Views\shortEndSubscriptEmail',
        'paiement_remember_email'         => 'Modules\Assurances\Views\rememberPaiementEmail',
        'paiement_suggest_email'          => 'Modules\Assurances\Views\suggestPaiementEmail',
        'signature_email'                 => 'Modules\Assurances\Views\signatureEmail',
    ];
}
