<?php

namespace Modules\CLI\Controllers;

use CodeIgniter\Controller;

class CliController extends Controller
{
    public function message($to = 'World')
    {
        if (!is_cli()) {
            exit("Use only from CLI.");
        }
        return "Hello {$to}!" . PHP_EOL;
    }
    /*
        Ici mettre les fonctions qui s'exécuterons automatiquement en arrière plan tous les jours.
    */
    // Netttoyage ou archivage des fichiers non répertoriés dans la bd.
    // Vérification des statuts de souscriptions, consultations et de transactions.
    // 
}
