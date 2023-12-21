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