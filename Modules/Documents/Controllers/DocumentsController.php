<?php

namespace Modules\Documents\Controllers;

use App\Controllers\BaseController;
use App\Traits\ControllerUtilsTrait;

class DocumentsController extends BaseController
{
    use ControllerUtilsTrait;

    public function getDocumentTitles()
    {
        $doc = model("DocumentTitresModel")->findAll();
        $response = [
            'statut'  => 'ok',
            'message' => $doc ? "Différernts titres de documents." : "Aucun titre de document trouvé.",
            'data'    => $doc,
        ];
        return $this->sendResponse($response);
    }
}
