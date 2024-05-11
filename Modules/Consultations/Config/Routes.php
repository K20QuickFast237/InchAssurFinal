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

/******************************* Les RDVs *********************************/
$routes->post('rdvs/(:segment)', '\Modules\Consultations\Controllers\RdvsController::update/$1');
$routes->get('allRdvs', '\Modules\Consultations\Controllers\RdvsController::showAll');
$routes->resource('rdvs', [
    'controller' => '\Modules\Consultations\Controllers\RdvsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

/******************************* Les Villes *********************************/
$routes->post('villes/(:num)', '\Modules\Consultations\Controllers\VillesController::update/$1');
$routes->resource('villes', [
    'controller' => '\Modules\Consultations\Controllers\VillesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

/******************************* Les Langues *********************************/
$routes->post('langues/(:num)', '\Modules\Consultations\Controllers\LanguesController::update/$1');
$routes->resource('langues', [
    'controller' => '\Modules\Consultations\Controllers\LanguesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

/******************************* Skills && Motifs  Start *********************************/
$routes->post('skills/(:num)', '\Modules\Consultations\Controllers\SkillsController::update/$1');
$routes->resource('skills', [
    'controller' => '\Modules\Consultations\Controllers\SkillsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->post('motifs/(:num)', '\Modules\Consultations\Controllers\MotifsController::update/$1');
$routes->get('motifs/(:num)/skills', '\Modules\Consultations\Controllers\MotifsController::getSkills/$1');
$routes->get('skills/(:num)/motifs', '\Modules\Consultations\Controllers\SkillsController::getMotifs/$1');
$routes->post('skills/(:num)/motifs', '\Modules\Consultations\Controllers\SkillsController::setMotifs/$1');
$routes->delete('skills/(:num)/motifs/(:num)', '\Modules\Consultations\Controllers\SkillsController::delMotifs/$1/$2');
$routes->resource('motifs', [
    'controller' => '\Modules\Consultations\Controllers\MotifsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
/******************************* Skills && Motifs  End *********************************/


$routes->post('localisation', '\Modules\Consultations\Controllers\ConsultationsController::addLocalisation');
