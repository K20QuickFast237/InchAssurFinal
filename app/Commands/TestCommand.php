<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Modules\Assurances\Entities\SouscriptionsEntity;
use Modules\Paiements\Entities\PayOptionEntity;
use Modules\Paiements\Entities\TransactionEntity;
use CodeIgniter\Events\Events;

class TestCommand extends BaseCommand
{
    protected $group       = 'Test';
    protected $name        = 'app:getAssurs';
    protected $description = 'Displays available Assurances.';

    public function run(array $params)
    {

        $assurances = model("AssurancesModel")->findAll();
        $thead = ['ID', 'Code', 'Nom', 'Duree (en Jours)', 'etat'];
        $tbody = [];
        foreach ($assurances as $assurance) {
            $tbody[] = [
                $assurance->id,
                $assurance->code,
                $assurance->nom,
                $assurance->duree,
                $assurance->etat,
            ];
        }
        CLI::table($tbody, $thead);

        return EXIT_SUCCESS;
    }
}
