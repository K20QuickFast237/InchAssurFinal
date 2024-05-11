<?php

namespace Modules\Consultations\Config;

// use Auth as A;

// Create a new instance of our RouteCollection class.
$routes = \Config\Services::routes();

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
$routes->group('', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->get('motifs', 'ConsultationsController::getMotifs');

    // $routes->get('/consultFilter', 'User::filterConsult', ['filter' => 'auth']);
    $routes->get('rdv/(:num)', 'ConsultationsController::detailRdv/$1');
});

$routes->post('rdvs/(:segment)', '\Modules\Consultations\Controllers\RdvsController::update/$1');
$routes->get('allRdvs', '\Modules\Consultations\Controllers\RdvsController::showAll');
$routes->resource('rdvs', [
    'controller' => '\Modules\Consultations\Controllers\RdvsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
