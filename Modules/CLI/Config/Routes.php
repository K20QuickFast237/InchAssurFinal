<?php

namespace Modules\CLI\Config;



$routes->cli('tools/message', '\Modules\CLI\Controllers\CliController::message');
$routes->cli('tools/message/(:segment)', '\Modules\CLI\Controllers\CliController::message/$1');
