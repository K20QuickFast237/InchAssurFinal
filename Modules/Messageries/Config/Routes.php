<?php

namespace Modules\Messageries\Config;


//------------ conversations ---------------------------
$routes->group('/conversations', ['namespace' => 'Modules\Messageries\Controllers'], static function ($routes) {
    $routes->get('(:num)/membres',           'ConversationsController::getConversationMembers/$1');
    $routes->get('(:num)/messages',          'ConversationsController::getConversationMessages/$1');
    $routes->get('(:num)/setAdmin/(:num)',   'ConversationsController::setConversationAdminMember/$1/$2');
    $routes->delete('(:num)/membres/(:num)', 'ConversationsController::removeConversationMember/$1/$2');
    $routes->post('(:num)/membres',          'ConversationsController::addConversationMember/$1');
    $routes->post('(:num)/messages',         'ConversationsController::addConversationMessage/$1');
});

$routes->post('conversations/(:segment)', '\Modules\Messageries\Controllers\ConversationsController::update/$1');
$routes->get('allConversations', '\Modules\Messageries\Controllers\ConversationsController::showAll');
$routes->resource('conversations', [
    'controller' => '\Modules\Messageries\Controllers\ConversationsController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);

//--------- ------------ messages ---------------------------