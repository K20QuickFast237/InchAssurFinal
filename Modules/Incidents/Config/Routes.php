<?php

namespace Modules\Incidents\Config;


$routes->get('allIncidents', '\Modules\Incidents\Controllers\IncidentsController::getAllIncidents');
$routes->post('incidents/(:segment)', '\Modules\Incidents\Controllers\IncidentsController::update/$1');

$routes->group('incidents', ['namespace' => 'Modules\Incidents\Controllers'], function ($routes) {
    $routes->get('utilisateur/(:segment)',             "IncidentsController::index/$1");
    $routes->get("(:segment)/images",                  "IncidentsController::getIncidentImages/$1");
    $routes->post("(:segment)/images",                 "IncidentsController::setIncidentImages/$1");
    $routes->delete("(:segment)/images/(:segment)",    "IncidentsController::delIncidentImage/$1/$2");
    // $routes->get("(:segment)/documents",               "IncidentsController::getSinistreDocuments/$1");
    // $routes->post("(:segment)/documents",              "IncidentsController::setSinistreDocument/$1");
    // $routes->delete("(:segment)/documents/(:segment)", "IncidentsController::delSinistreDocument/$1/$2");
});
$routes->resource('incidents', [
    'controller'  => '\Modules\Incidents\Controllers\IncidentsController',
    'placeholder' => '(:segment)',
    'except'      => 'new,edit',
]);
