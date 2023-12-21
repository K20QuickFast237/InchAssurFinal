<?php

namespace Auth\Config;

use OpenApi\Annotations as OA;
use CodeIgniter\Shield\Controllers\ActionController;

// Create a new instance of our RouteCollection class.
$routes = \Config\Services::routes();

#----------------------------------------------------------------------
# Shield Actions routes
#----------------------------------------------------------------------
$routes->get('auth/a/show', 'CodeIgniter\Shield\Controllers\ActionController::show');
$routes->post('auth/a/handle', 'CodeIgniter\Shield\Controllers\ActionController::handle');
$routes->post('auth/a/verify', 'CodeIgniter\Shield\Controllers\ActionController::verify');


/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->group('/account', ['namespace' => 'Modules\Auth\Controllers'], static function ($routes) {
    $routes->post('',             'Auth::create');

    $routes->post('confirm',      'Auth::codeConfirm');
    // $routes->post('confirm',      'Auth::codeConfirm', ['filter' => 'auth']);

    $routes->post('login',        'Auth::signIn');

    $routes->post('resetPassword', 'Auth::initiatePwdReset');

    $routes->post('setPassword',  'Auth::resetPassword', ['filter' => 'auth']);

    $routes->get('resetConfirm',  'Auth::resetCodeConfirm', ['filter' => 'auth']);

    $routes->get('isRegistered',  'Auth::firstConnectDashbord', ['filter' => 'auth']);

    $routes->get('logout',        'Auth::deconnexion', ['filter' => 'auth']);
});
$routes->get('smsTest', 'Auth::sendTestMessage', ['namespace' => 'Modules\Auth\Controllers']);

/**
 * @OA\Get(
 *  path="/modules",
 *  tags={"Modules"},
 *  summary="liste tous les modules, par un admin",
 *  operationId="allModules by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Response(
 *      response="200",
 *      description="liste des modules",
 *      @OA\JsonContent(
 *          @OA\Property(property="statut", type="string", example="ok"),
 *          @OA\Property(property="message", type="string", description="Aucun Module trouvé!!"),
 *          @OA\Property(
 *              property="data",
 *              description="liste des modules",
 *              type="array",
 *              @OA\items(ref="#/components/schemas/ModuleDataResponse")
 *          ),
 *          @OA\Property(property="token", type="string", description="un nouveau token"),
 *      )
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised"
 *  )
 * 
 * )
 */
//$routes->get('modules', 'Auth::allModules', ['namespace' => 'Auth\Controllers', 'filter' => 'admin']);

// $routes->group('/module', ['namespace' => 'Auth\Controllers'], static function ($routes) {
/**
 * @OA\Post(
 *  path="/module",
 *  tags={"Modules"},
 *  summary="Ajoute un module à la plateforme, par un admin",
 *  operationId="addModule by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\RequestBody(
 *      description="détails du nouveau module",
 *      required=true,
 *      @OA\JsonContent(
 *          required={"nomModule"},
 *          @OA\Property(property="nomModule", type="string", example="nom du module"),   
 *          @OA\Property(property="description", type="string", example="une description du module"),    
 *      )
 *  ),
 *  @OA\Response(
 *      response="200",
 *      ref="#/components/responses/noDataSuccess"
 *  ),
 *  @OA\Response(
 *      response="409",
 *      ref="#/components/responses/Conflictual"
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised")
 *  )
 * )
 */
// $routes->post('', 'Auth::addModule', ['filter' => 'admin']);

/**
 * @OA\Post(
 *  path="/module/{num}",
 *  tags={"Modules"},
 *  summary="Met à jour u8n module, par un admin",
 *  operationId="updateModule by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\RequestBody(
 *      description="détails du module",
 *      required=true,
 *      @OA\JsonContent(
 *          @OA\Property(property="nomModule", type="string", example="nom du module"),   
 *          @OA\Property(property="description", type="string", example="une description du module"),   
 *      )
 *  ),
 *  @OA\Response(
 *      response="200",
 *      ref="#/components/responses/noDataSuccess"
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised")
 *  )
 * )
 */
// $routes->post('(:num)', 'Auth::updateModule/$1', ['filter' => 'admin']);

/**
 * @OA\Post(
 *  path="/module/{num}/action",
 *  tags={"Modules"},
 *  summary="Ajoute une action au module, par un admin",
 *  operationId="addModuleAction by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\RequestBody(
 *      description="nouvelle action",
 *      required=true,
 *      @OA\JsonContent(
 *          required={"nomsousaction"},
 *          @OA\Property(property="nomsousaction", type="string", example="nom de l'action"),   
 *          @OA\Property(property="description", type="string", example="une description de l'action"),    
 *      )
 *  ),
 *  @OA\Response(
 *      response="201",
 *      description="Action ajoutée au module",
 *      @OA\JsonContent(
 *          @OA\Property(property="statut", type="string", example="ok"),
 *          @OA\Property(property="message", type="string", description="Action Ajoutée"),
 *          @OA\Property(
 *              property="data",
 *              description="liste des actions du module",
 *              type="array",
 *              @OA\items(ref="#/components/schemas/ActionModuleDataResponse")
 *          ),
 *          @OA\Property(property="token", type="string", description="un nouveau token"),
 *      )
 *  ),
 *  @OA\Response(
 *      response="409",
 *      ref="#/components/responses/Conflictual"
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised")
 *  )
 * )
 */
// $routes->post('(:num)/action', 'Auth::addActionModule/$1', ['filter' => 'admin']);

/**
 * @OA\Post(
 *  path="/module/action/{num}",
 *  tags={"Modules"},
 *  summary="Met à jour l'action dont l'identifiant est indiqué dans le path, par un admin",
 *  operationId="updateModuleAction by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\RequestBody(
 *      description="met à jour l'action",
 *      required=true,
 *      @OA\JsonContent(
 *          required={"nomsousaction"},
 *          @OA\Property(property="nomsousaction", type="string", example="nom de l'action"),   
 *          @OA\Property(property="description", type="string", example="une description de l'action"),    
 *      )
 *  ),
 *  @OA\Response(
 *      response="200",
 *      description="Action modifiée",
 *      @OA\JsonContent(
 *          @OA\Property(property="statut", type="string", example="ok"),
 *          @OA\Property(property="message", type="string", description="Action modifiée"),
 *          @OA\Property(property="data", type="object", description="les éléments modifés"),
 *          @OA\Property(property="token", type="string", description="un nouveau token"),
 *      )
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised")
 *  )
 * )
 */
// $routes->post('action/(:num)', 'Auth::updateActionModule/$1', ['filter' => 'admin']);

/**
 * @OA\Get(
 *  path="/module/{num}/activate",
 *  tags={"Modules"},
 *  summary="Active le module, par un admin",
 *  operationId="activateModule by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\Response(
 *      response="200",
 *      description="Module activé",
 *      ref="#components/responses/noDataSuccess"
 *  ),
 *  @OA\Response(
 *      response="500",
 *      ref="#/components/responses/InternalError")
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised"
 *  )
 * )
 */
// $routes->get('(:num)/activate', 'Auth::activateModule/$1', ['filter' => 'admin']);

/**
 * @OA\Get(
 *  path="/module/{num}/disactivate",
 *  tags={"Modules"},
 *  summary="Desactive le module, par un admin",
 *  operationId="desactivateModule by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\Response(
 *      response="200",
 *      description="Module desactivé",
 *      ref="#components/responses/noDataSuccess"
 *  ),
 *  @OA\Response(
 *      response="500",
 *      description="erreur inconnue",
 *      ref="#/components/responses/InternalError"
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised"
 *  )
 * )
 */
// $routes->get('(:num)/disactivate', 'Auth::desactivateModule/$1', ['filter' => 'admin']);

/**
 * @OA\Get(
 *  path="/module/{num}/actions",
 *  tags={"Modules"},
 *  summary="Desactive le module, par un admin",
 *  operationId="getModuleActions by admin",
 *  security={{"bearerAuth": {}}},
 *  @OA\Parameter(ref="#/components/parameters/num"),
 *  @OA\Response(
 *      response="200",
 *      description="liste des actions",
 *      @OA\JsonContent(
 *          @OA\Property(property="statut", type="string", example="ok"),
 *          @OA\Property(property="message", type="string", description="Action trouvées"),
 *          @OA\Property(
 *              property="data",
 *              description="liste des actions du module",
 *              type="array",
 *              @OA\items(ref="#/components/schemas/ActionModuleDataResponse")
 *          ),
 *          @OA\Property(property="token", type="string", description="un nouveau token"),
 *      )
 *  ),
 *  @OA\Response(
 *      response="401",
 *      ref="#/components/responses/401_unAuthorised"
 *  )
 * 
 * )
 */
    // $routes->get('(:num)/actions', 'Auth::getModuleActions/$1', ['filter' => 'admin']);
// });

//$routes->post('profil/(:num)/actions', 'Auth::addModuleByProfileUser/$1', ['namespace' => 'Auth\Controllers', 'filter' => 'admin']);
