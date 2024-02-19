<?php

namespace Modules\Images\Models;

use Modules\Documents\Models\DocumentsModel;


class ImagesModel extends DocumentsModel
{

    // protected $primaryKey    = 'id';
    protected $table         = 'images';
    protected $returnType    = '\Modules\Images\Entities\ImagesEntity';
    protected $allowedFields = ["uri", "extension", "isLink", "type"];
    /**
     * Called during initialization. Appends
     * our custom field to the parent model.
     */
    // protected function initialize()
    // {
    //     $this->table            = 'images';
    //     $this->returnType       = '\Modules\Images\Entities\ImagesEntity';
    //     $this->allowedFields    = ["uri", "extension", "isLink", "type"];
    // }

    public function getSimplified($id)
    {
        return $this->select("id, uri")->where('id', $id)->first();
    }

    public function getMultiSimplified($ids)
    {
        return $this->select("id, uri")->whereIn('id', $ids)->findAll();
    }
}
