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
    // $routes->get('motifs', 'ConsultationsController::getMotifs');

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

/******************************* Les Consultations *********************************/
$routes->post('consultations/(:segment)', '\Modules\Consultations\Controllers\ConsultationsController::update/$1');
$routes->get('allConsultations', '\Modules\Consultations\Controllers\ConsultationsController::showAll');
$routes->resource('consultations', [
    'controller' => '\Modules\Consultations\Controllers\ConsultationsController',
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

/******************************* Les Localisations *********************************/
$routes->group('localisations', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->post('(:num)', 'LocalisationsController::update/$1');
    $routes->get('medecin/(:segment)', 'LocalisationsController::index/$1');
    $routes->post('medecin/(:segment)', 'LocalisationsController::setMedLocation/$1');
    $routes->delete('(:num)/medecin/(:segment)', 'LocalisationsController::delMedLocation/$1/$2');
});
$routes->get('allLocalisations', '\Modules\Consultations\Controllers\LocalisationsController::showAll');
$routes->resource('localisations', [
    'controller' => '\Modules\Consultations\Controllers\LocalisationsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);


/******************************* Les Langues Start *********************************/
$routes->group('langues', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->post('(:num)', 'LanguesController::update/$1');
    $routes->get('medecin/(:segment)', 'LanguesController::index/$1');
    $routes->post('medecin/(:segment)', 'LanguesController::setMedlang/$1');
    $routes->delete('(:num)/medecin/(:segment)', 'LocalisationsController::delMedLangue/$1/$2');
});
$routes->resource('langues', [
    'controller' => '\Modules\Consultations\Controllers\LanguesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
/******************************* Les Langues End *********************************/


/******************************* Les Canaux  Start *********************************/
$routes->group('canaux', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->post('(:num)', 'CanauxController::update/$1');
    $routes->get('medecin/(:segment)', 'CanauxController::index/$1');
    $routes->post('medecin/(:segment)', 'CanauxController::setMedCanal/$1');
    $routes->delete('(:num)/medecin/(:segment)', 'CanauxController::delMedCanal/$1/$2');
});
$routes->resource('canaux', [
    'controller' => '\Modules\Consultations\Controllers\CanauxController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
/******************************* Les Canaux  End *********************************/


/******************************* Skills && Motifs  Start *********************************/
$routes->get('allSkills', '\Modules\Consultations\Controllers\SkillsController::showAll');
$routes->group('skills', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->post('(:num)', 'SkillsController::update/$1');
    $routes->get('(:num)/motifs', 'SkillsController::getMotifs/$1');
    $routes->post('(:num)/motifs', 'SkillsController::setMotifs/$1');
    $routes->get('medecin/(:segment)', 'SkillsController::index/$1');
    $routes->post('medecin/(:segment)', 'SkillsController::setMedSkill/$1');
    $routes->post('medecin', 'SkillsController::setMedSkill');
    $routes->post('(:num)/medecin/(:segment)', 'SkillsController::updateMedSkill/$1/$2');
    $routes->post('(:num)/medecin', 'SkillsController::updateMedSkill/$1');
    $routes->delete('(:num)/motifs/(:num)', 'SkillsController::delMotifs/$1/$2');
});
$routes->resource('skills', [
    'controller' => '\Modules\Consultations\Controllers\SkillsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
$routes->get('allMotifs', '\Modules\Consultations\Controllers\MotifsController::showAll');
$routes->group('motifs', ['namespace' => 'Modules\Consultations\Controllers'], static function ($routes) {
    $routes->post('(:num)', 'MotifsController::update/$1');
    $routes->post('(:num)', 'MotifsController::update/$1');
    $routes->get('medecin/(:segment)', 'MotifsController::index/$1');
    $routes->get('(:num)/skills', 'MotifsController::getSkills/$1');
});
$routes->resource('motifs', [
    'controller' => '\Modules\Consultations\Controllers\MotifsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
/******************************* Skills && Motifs  End *********************************/


$routes->post('localisation', '\Modules\Consultations\Controllers\ConsultationsController::addLocalisation');
