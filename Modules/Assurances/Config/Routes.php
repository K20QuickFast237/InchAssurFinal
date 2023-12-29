<?php

namespace Modules\Assurances\Config;


//-------- Assurances --------------
$routes->group('assurances', ['namespace' => 'Modules\Assurances\Controllers'], function ($routes) {
    // $routes->post("register", "Register::index");
    $routes->post("(:segment)/reductions", "AssurancesController::setAssurReductions/$1"); // codeAssurance
    $routes->post("(:segment)/questionnaire", "AssurancesController::setAssurQuestionnaire/$1"); // codeAssurance
    $routes->post("(:segment)/images", "AssurancesController::setAssurImages/$1"); // codeAssurance
    $routes->post("(:segment)/services", "AssurancesController::setAssurServices/$1"); // codeAssurance
    $routes->post("(:segment)/categories", "AssurancesController::setAssurCategories/$1"); // codeAssurance
    $routes->post("(:segment)/defaultCategory", "AssurancesController::setAssurdefaultCategory/$1"); // codeAssurance
    $routes->post("(:segment)/payOptions", "AssurancesController::setAssurPayOptions/$1");
    $routes->post("(:segment)/documentation", "AssurancesController::setAssurDocumentation/$1"); // codeAssurance
    $routes->post("(:segment)/defaultImage", "AssurancesController::setAssurDefaultImg/$1"); // codeAssurance
    $routes->get("categorie/(:num)", "AssurancesController::getAssursOfCategor/$1");
    $routes->get("(:segment)/documentation", "AssurancesController::getAssurDocumentation/$1"); // codeAssurance
    $routes->get("(:segment)/questionnaire", "AssurancesController::getAssurQuestionnaire/$1"); // codeAssurance
    $routes->get("(:segment)/reductions", "AssurancesController::getAssurReductions/$1"); // codeAssurance
    $routes->get("(:segment)/services", "AssurancesController::getAssurServices/$1"); // codeAssurance
    $routes->get("(:segment)/images", "AssurancesController::getAssurImages/$1"); // codeAssurance
    $routes->get("(:segment)/payOptions", "AssurancesController::getAssurPayOptions/$1"); // codeAssurance
    $routes->get("(:segment)/infos", "AssurancesController::getAssurInfos/$1"); // codeAssurance
    $routes->post("(:segment)", "AssurancesController::update/$1"); // codeAssurance
});

$routes->resource('assurances', [
    'controller' => '\Modules\Assurances\Controllers\AssurancesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);



//------------- Questions --------------------
$routes->group('questions', ['namespace' => 'Modules\Assurances\Controllers'], function ($routes) {
    $routes->get('tarifTypes', 'QuestionsController::getTarifTypes');
});
/** @todo Ajouter la gestion des accès sur la ressource questions */
$routes->post('questions/(:segment)', '\Modules\Assurances\Controllers\QuestionsController::update/$1');
$routes->resource('questions', [
    'controller' => '\Modules\Assurances\Controllers\QuestionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);


//------------ Services ---------------------------
/** @todo Ajouter la gestion des accès sur la ressource services */
$routes->post('services/(:segment)', '\Modules\Assurances\Controllers\ServicesController::update/$1');
$routes->resource('services', [
    'controller' => '\Modules\Assurances\Controllers\ServicesController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);


//------------ Souscriptions ---------------------------
$routes->group('souscriptions', ['namespace' => 'Modules\Assurances\Controllers'], function ($routes) {
    // Enregistre la réponse à une question pour une souscription
    $routes->get("assurances/(:segment)/infos", "SouscriptionsController::getSouscriptAssurInfo/$1"); // codeAssurance
    // $routes->get("(:segment)/infos", "SouscriptionsController::getSouscriptionInfos/$1"); // codeAssurance
    $routes->get("(:segment)/documents", "SouscriptionsController::getSouscriptionDocument/$1"); // codeAssurance
    $routes->get('(:segment)/questionAnswers', 'SouscriptionsController::getQuestionAnswer/$1');
    $routes->get('(:segment)/beneficiaires', 'SouscriptionsController::getBeneficiaires/$1');
    $routes->post('(:segment)/beneficiaires', 'SouscriptionsController::addBeneficiaires/$1');
    $routes->post('(:segment)/questionAnswer', 'SouscriptionsController::addQuestionAnswer/$1');
    $routes->post("(:segment)/documents", "SouscriptionsController::addSouscriptionDocument/$1"); // codeAssurance

});
/** @todo n'autoriser la mise à jour qu'à un administrateur */
$routes->post('souscriptions/(:segment)', '\Modules\Assurances\Controllers\SouscriptionsController::update/$1');
/** @todo Ajouter la gestion des accès sur la ressource souscriptions */
$routes->resource('souscriptions', [
    'controller' => '\Modules\Assurances\Controllers\SouscriptionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

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