<?php

namespace Modules\Agendas\Config;

// Create a new instance of our RouteCollection class.
$routes = \Config\Services::routes();

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */
$routes->group('agendas', ['namespace' => 'Modules\Agendas\Controllers'], static function ($routes) {
    /**
     * @OA\Get(
     *  path="/agenda/user/{user-code}",
     *  @OA\Parameter(ref="#/components/parameters/user-code"),
     *  tags={"Agenda"},
     *  summary="Récupère par un admin, l'agenda du user dont le code est spécifié dans le chemin",
     *  operationId="getAgenda by admin",
     *  security={{"bearerAuth": {}}},
     *  @OA\Response(
     *      response="200",
     *      description="liste les elements de l'agenda d'un médecin",
     *      @OA\JsonContent(
     *          @OA\Property(property="statut", type="string", example="ok"),
     *          @OA\Property(property="message", type="string", description="filter aucun token"),
     *          @OA\Property(
     *              property="data",
     *              description="les agendas",
     *              type="array",
     *              @OA\items(ref="#/components/schemas/AgendaResult")
     *          ),
     *          @OA\Property(property="token", type="string", description="un nouveau token"),
     *      )
     *  ),
     *  @OA\Response(
     *      response="401",
     *      ref="#/components/responses/401_unAuthorised")
     *  )
     * )
     * 
     */
    $routes->get('user/(:segment)', 'AgendasController::index/$1');

    /**
     * @OA\Delete(
     *  path="/agenda/{num}",
     *  @OA\Parameter(ref="#/components/parameters/num"),
     *  tags={"Agenda"},
     *  summary="Supprime l'agenda dont l'identifiant est spécifié dans le path",
     *  operationId="delAgenda by medecin",
     *  security={{"bearerAuth": {}}},
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
    $routes->delete('(:num)', 'AgendasController::delDispo/$1');
    // $routes->delete('(:num)', 'AgendasController::deleteAgenda/$1');

    /**
     * @OA\Delete(
     *  path="/agenda/{num-agenda}/slot/{num-slot}",
     *  @OA\Parameter(
     *      name="num-agenda",
     *      description="identifiant de l'agenda",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  @OA\Parameter(
     *      name="num-slot",
     *      description="identifiant du créneau",
     *      in="path",
     *      required=true,
     *      @OA\Schema(type="integer")
     *  ),
     *  tags={"Agenda"},
     *  summary="Supprime le crénau spécifié dans l'agenda désigné",
     *  operationId="delAgenda by admin",
     *  security={{"bearerAuth": {}}},
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
    $routes->delete('(:num)/slot/(:num)', 'AgendasController::delCreneau/$1/$2');

    //update
    $routes->post('(:num)', '\Modules\Agendas\Controllers\AgendasController::update/$1');

    /**
     * @OA\Post(
     *  path="/agenda/user/{user-code}",
     *  tags={"Agenda"},
     *  summary="Cree un agenda pour un medecin, par un admin",
     *  operationId="addAgenda by admin",
     *  security={{"bearerAuth": {}}},
     *  @OA\Parameter(ref="#/components/parameters/user-code"),
     *  @OA\RequestBody(
     *      description="jours et plages de disponibilité",
     *      required=true,
     *      @OA\JsonContent(
     *          type="array",
     *          @OA\Items(ref="#/components/schemas/AgendaData")
     *      )
     *  ),
     *  @OA\Response(
     *      response="201",
     *      ref="#/components/responses/noDataSuccess"
     *  ),
     *  @OA\Response(
     *      response="401",
     *      ref="#/components/responses/401_unAuthorised"
     *  )
     * )
     */
    $routes->post('user/(:segment)', 'AgendasController::create/$1');
});

$routes->get('allAgendas', '\Modules\Agendas\Controllers\AgendasController::showAll');
$routes->resource('agendas', [
    'controller' => '\Modules\Agendas\Controllers\AgendasController',
    'placeholder' => '(:segment)',
    'except' => 'new,edit',
]);
