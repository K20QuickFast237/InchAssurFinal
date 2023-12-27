<?php

namespace Modules\Utilisateurs\Config;

//------------ utilisateurs ---------------------------
/** @todo Ajouter la gestion des accès sur la ressource membres  */
$routes->resource('utilisateurs', [
    'controller' => '\Modules\Utilisateurs\Controllers\utilisateursController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->post('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::addMember');


//------------ Membres ---------------------------
$routes->post('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::addMember');
$routes->get('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::getMember');

//------------ Users ---------------------------
$routes->group('/users', ['namespace' => 'Modules\Utilisateurs\Controllers'], static function ($routes) {
    $routes->get('dashboard', 'UtilisateursController::dashboardInfos');
    $routes->get('pocket',    'PortefeuillesController::getUserPocket');
    $routes->get('(:segment)/pocket',    'PortefeuillesController::getUserPocket/$1');
});

//------------ PorteFeuilles ---------------------------
$routes->resource('pockets', [
    'controller' => '\Modules\Utilisateurs\Controllers\PortefeuillesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->post('pockets/(:segment)', '\Modules\Utilisateurs\Controllers\PortefeuillesController::update/$1');


/* Exemple
    $routes->resource('photos');

    // Equivalent to the following:
        $routes->get('photos/new', 'Photos::new');
    $routes->post('photos', 'Photos::create');
    $routes->get('photos', 'Photos::index');
    $routes->get('photos/(:segment)', 'Photos::show/$1');
        $routes->get('photos/(:segment)/edit', 'Photos::edit/$1');
    $routes->put('photos/(:segment)', 'Photos::update/$1');
    $routes->patch('photos/(:segment)', 'Photos::update/$1');
    $routes->delete('photos/(:segment)', 'Photos::delete/$1');
*/