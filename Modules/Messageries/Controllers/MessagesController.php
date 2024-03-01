<?php

namespace Modules\Messageries\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Traits\ControllerUtilsTrait;
use App\Traits\ErrorsDataTrait;
use Modules\Messageries\Entities\MessageEntity;

class MessagesController extends BaseController
{
    use ControllerUtilsTrait;
    use ResponseTrait;
    use ErrorsDataTrait;

    protected $helpers = ['Modules\Documents\Documents', 'Modules\Images\Images', 'text'];

    /**
     * Retourne la liste des conversations d'un utilisateur
     *
     * @return ResponseInterface The HTTP response.
     */
    public function index($identifier = null)
    {
    }

    /**
     * Retourne la liste de toutes les conversations enregistrées
     *
     * @return ResponseInterface The HTTP response.
     */
    public function showAll()
    {
    }

    /**
     * Returne les détails d'une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function show($id = null)
    {
    }

    /**
     * Cree une conversdation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function create()
    {
    }

    /** @todo think about the use cases
     * Modifie une converesation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function update($id = null)
    {
    }

    /** @todo think about the use cases
     * Supprime une conversation
     *
     * @return ResponseInterface The HTTP response.
     */
    public function delete($id = null)
    {
    }
}
