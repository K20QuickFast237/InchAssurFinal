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
}
