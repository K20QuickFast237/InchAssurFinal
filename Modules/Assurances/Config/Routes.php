<?php

namespace Modules\Assurances\Config;


//-------- Assurances --------------
$routes->group('assurances', ['namespace' => 'Modules\Assurances\Controllers'], function ($routes) {
    // $routes->post("register", "Register::index");
    $routes->post("(:segment)/reductions",      "AssurancesController::setAssurReductions/$1");
    $routes->post("(:segment)/questionnaire",   "AssurancesController::setAssurQuestionnaire/$1");
    $routes->post("(:segment)/images",          "AssurancesController::setAssurImages/$1");
    $routes->post("(:segment)/services",        "AssurancesController::setAssurServices/$1");
    $routes->post("(:segment)/categories",      "AssurancesController::setAssurCategories/$1");
    $routes->post("(:segment)/sous-categories", "AssurancesController::setAssursubCategories/$1");
    $routes->post("(:segment)/defaultCategory", "AssurancesController::setAssurdefaultCategory/$1");
    $routes->post("(:segment)/payOptions",      "AssurancesController::setAssurPayOptions/$1");
    $routes->post("(:segment)/piecesAJoindre",  "AssurancesController::setAssurPieceAjoindres/$1");
    $routes->post("(:segment)/documentation",   "AssurancesController::setAssurDocumentation/$1");
    $routes->post("(:segment)/defaultImage",    "AssurancesController::setAssurDefaultImg/$1");
    $routes->get("utilisateur/(:segment)",      "AssurancesController::index/$1");
    $routes->get("types",                       "AssurancesController::getAssurTypes/$1");
    $routes->get("categorie/(:num)",            "AssurancesController::getAssursOfCategory/$1");
    $routes->get("sous-categorie/(:num)",       "AssurancesController::getAssursOfSubCategory/$1");
    $routes->get("(:segment)/piecesAJoindre",   "AssurancesController::getAssurPieceAjoindres/$1");
    $routes->get("(:segment)/documentation",    "AssurancesController::getAssurDocumentation/$1");
    $routes->get("(:segment)/questionnaire",    "AssurancesController::getAssurQuestionnaire/$1");
    $routes->get("(:segment)/reductions",       "AssurancesController::getAssurReductions/$1");
    $routes->get("(:segment)/services",         "AssurancesController::getAssurServices/$1");
    $routes->get("(:segment)/images",           "AssurancesController::getAssurImages/$1");
    $routes->get("(:segment)/payOptions",       "AssurancesController::getAssurPayOptions/$1");
    $routes->delete("(:num)/document/(:num)",   "AssurancesController::delAssurDocument/$1/$2");
    $routes->get("(:segment)/infos",            "AssurancesController::getAssurInfos/$1");
    $routes->post("(:segment)/active",          "AssurancesController::activateAssur/$1");
    $routes->post("(:segment)/desactive",       "AssurancesController::disactivateAssur/$1");
    $routes->post("(:segment)",                 "AssurancesController::update/$1");
});

$routes->get('allAssurances', '\Modules\Assurances\Controllers\AssurancesController::allInsurances');
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
$routes->get('allQuestions', '\Modules\Assurances\Controllers\QuestionsController::index');
$routes->post('questions/(:segment)', '\Modules\Assurances\Controllers\QuestionsController::update/$1');
$routes->resource('questions', [
    'controller' => '\Modules\Assurances\Controllers\QuestionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);


//------------ Services ---------------------------
/** @todo Ajouter la gestion des accès sur la ressource services */
$routes->get('allServices', '\Modules\Assurances\Controllers\ServicesController::index');
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
    $routes->get('signature', 'SouscriptionsController::codeSignature');
    $routes->get('utilisateur/(:segment)', 'SouscriptionsController::index/$1');
    $routes->get('sinistreTypes', 'SouscriptionsController::getWithTypeSinistre');
    // $routes->get("(:segment)/infos", "SouscriptionsController::getSouscriptionInfos/$1"); // codeAssurance
    $routes->get("(:segment)/documents", "SouscriptionsController::getSouscriptionDocument/$1"); // codeAssurance
    $routes->get('(:segment)/questionAnswers', 'SouscriptionsController::getQuestionAnswer/$1');
    $routes->get('(:segment)/beneficiaires', 'SouscriptionsController::getBeneficiaires/$1');
    // $routes->get('(:segment)/payoption', 'SouscriptionsController::getPaymentOption/$1');
    // $routes->post('(:segment)/payoption', 'SouscriptionsController::setPaymentOption/$1');
    $routes->post('signature', 'SouscriptionsController::decodeSignature');
    $routes->post('(:segment)/beneficiaires', 'SouscriptionsController::addBeneficiaires/$1');
    $routes->post('(:segment)/questionAnswer', 'SouscriptionsController::addQuestionAnswer/$1');
    $routes->post("(:segment)/documents", "SouscriptionsController::addSouscriptionDocument/$1"); // codeAssurance

});
/** @todo n'autoriser la mise à jour qu'à un administrateur */
$routes->get('allSouscriptions', '\Modules\Assurances\Controllers\SouscriptionsController::allSubscriptions');
$routes->post('souscriptions/(:segment)', '\Modules\Assurances\Controllers\SouscriptionsController::update/$1');
/** @todo Ajouter la gestion des accès sur la ressource souscriptions */
$routes->resource('souscriptions', [
    'controller' => '\Modules\Assurances\Controllers\SouscriptionsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

//------------ Sinistres ---------------------------
$routes->get('allSinistres', '\Modules\Assurances\Controllers\SinistresController::getAllSinistres');
$routes->get('sinistreTypes', '\Modules\Assurances\Controllers\SinistresController::getActiveTypeSinistres');
$routes->post('sinistres/(:segment)', '\Modules\Assurances\Controllers\SinistresController::update/$1');

$routes->group('sinistres', ['namespace' => 'Modules\Assurances\Controllers'], function ($routes) {
    $routes->get('utilisateur/(:segment)',             "SinistresController::index/$1");
    $routes->get("(:segment)/images",                  "SinistresController::getSinistreImages/$1");
    $routes->post("(:segment)/images",                 "SinistresController::setSinistreImages/$1");
    $routes->delete("(:segment)/images/(:segment)",    "SinistresController::delSinistreImage/$1/$2");
    $routes->get("(:segment)/documents",               "SinistresController::getSinistreDocuments/$1");
    $routes->post("(:segment)/documents",              "SinistresController::setSinistreDocument/$1");
    $routes->delete("(:segment)/documents/(:segment)", "SinistresController::delSinistreDocument/$1/$2");
});
$routes->resource('sinistres', [
    'controller'  => '\Modules\Assurances\Controllers\SinistresController',
    'placeholder' => '(:segment)',
    'except'      => 'new,edit',
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