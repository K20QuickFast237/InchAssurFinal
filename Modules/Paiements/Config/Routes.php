<?php

namespace Paiements\Config;

// Create a new instance of our RouteCollection class.
$routes = \Config\Services::routes();

#----------------------------------------------------------------------
# Shield Actions routes
#----------------------------------------------------------------------
// $routes->get('auth/a/show', 'CodeIgniter\Shield\Controllers\ActionController::show');


/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->post('initpaiement/assurance', '\Modules\Paiements\Controllers\PaiementsController::InitiateAssurPayment');
$routes->group('paiements', ['namespace' => 'Modules\Paiements\Controllers'], static function ($routes) {
    $routes->get('pays', 'PaiementsController::getCountries');
    $routes->post('notify', 'PaiementsController::setPayStatus');
    $routes->post('statut', 'PaiementsController::localSetPayStatus');
});
