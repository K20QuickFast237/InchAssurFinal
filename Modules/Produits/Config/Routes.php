<?php

namespace Modules\Produits\Config;

//------------- PaiementOptions --------------------
/** @todo Ajouter la gestion des accès sur la ressource payOptions */
$routes->post('payOptions/(:segment)', '\Modules\Produits\Controllers\PaiementOptionsController::update/$1');
$routes->resource('payOptions', [
    'controller' => '\Modules\Produits\Controllers\PaiementOptionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);


//------------ Categories ---------------------------
/** @todo Ajouter la gestion des accès sur la ressource categosries  */
$routes->resource('categories', [
    'controller' => '\Modules\Produits\Controllers\CategoriesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->post('categories/(:segment)', '\Modules\Produits\Controllers\CategoriesController::update/$1');
$routes->post('subCategories', '\Modules\Produits\Controllers\CategoriesController::addSubCat');
$routes->get('subCategories', '\Modules\Produits\Controllers\CategoriesController::getAllSubCat');
$routes->get('subCategories/(:num)', '\Modules\Produits\Controllers\CategoriesController::showSubcat/$1');


//------------ Reductions ---------------------------
/** @todo Ajouter la gestion des accès sur la ressource reductions  */
$routes->resource('reductions', [
    'controller' => '\Modules\Produits\Controllers\ReductionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->get('allReductions', '\Modules\Produits\Controllers\ReductionsController::index/true');
$routes->post('reductions/(:segment)', '\Modules\Produits\Controllers\ReductionsController::update/$1');


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