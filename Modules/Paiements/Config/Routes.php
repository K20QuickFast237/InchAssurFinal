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
    $routes->get('',                       'PaiementsController::index');
    $routes->get('utilisateur/(:segment)', 'PaiementsController::index/$1');
    $routes->get('pays', 'PaiementsController::getCountries');
    $routes->get('modes', 'PaiementsController::getAllmodePaiement');
    $routes->post('notify', 'PaiementsController::setPayStatus');
    $routes->post('statut', 'PaiementsController::localSetPayStatus');
    $routes->post('confirmConsult', 'PaiementsController::localSetConsultPayStatus');
    $routes->post('notfyConsult', 'PaiementsController::setConsultPayStatus');
});

/*--------------------- For transactions ----------------------------- */
$routes->post('alltransactions', '\Modules\Paiements\Controllers\TransactionsController::getAllTransacts');
$routes->post('allreglements', '\Modules\Paiements\Controllers\TransactionsController::getAllReglements');

$routes->group('transactions', ['namespace' => 'Modules\Paiements\Controllers'], static function ($routes) {
    $routes->get('',                       'TransactionsController::index');
    $routes->get('utilisateur/(:segment)', 'TransactionsController::index/$1');
    $routes->get('(:segment)',             'TransactionsController::getDetails/$1');
    $routes->get('(:segment)/paiements',   'TransactionsController::getReglements/$1');
});
