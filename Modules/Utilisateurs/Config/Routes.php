<?php

namespace Modules\Utilisateurs\Config;

//------------ utilisateurs ---------------------------

//------------ Users ---------------------------
$routes->group('/users', ['namespace' => 'Modules\Utilisateurs\Controllers'], static function ($routes) {
    $routes->get('testes',                    'UtilisateursController::test');
    $routes->get('dashboard',                 'UtilisateursController::dashboardInfos');
    $routes->post('defaultProfil',            'UtilisateursController::setDefaultProfil');
    $routes->post('souscriptions',            'UtilisateursController::getSouscriptions');
    $routes->post('infos',                    'UtilisateursController::update');
    $routes->post('(:segment)/souscriptions', 'UtilisateursController::getSouscriptions/$1');
    $routes->get('(:segment)/pocket',         'PortefeuillesController::getUserPocket/$1');
    $routes->get('(:segment)/membres',        'UtilisateursController::getMember/$1');
    $routes->post('(:segment)/membres',       'UtilisateursController::addMember/$1');
    $routes->post('(:segment)/profil',        'UtilisateursController::addprofil/$1');
    $routes->post('(:segment)/defaultProfil', 'UtilisateursController::setDefaultProfil/$1');
});
/** @todo Ajouter la gestion des accès sur la ressource membres  */
// $routes->resource('utilisateurs', [
$routes->post('users/(:segment)', '\Modules\Utilisateurs\Controllers\utilisateursController::update/$1');
$routes->resource('users', [
    'controller' => '\Modules\Utilisateurs\Controllers\utilisateursController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit,create',
]);
$routes->post('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::addMember');


//------------ Pocket ---------------------------
$routes->get('pocket', '\Modules\Utilisateurs\Controllers\PortefeuillesController::getUserPocket');
$routes->get('pocket/recharge', '\Modules\Utilisateurs\Controllers\PortefeuillesController::recharge');

//------------ Membres ---------------------------
$routes->post('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::addMember');
$routes->get('membres', '\Modules\Utilisateurs\Controllers\UtilisateursController::getMember');


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