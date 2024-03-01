<?php

use CodeIgniter\HTTP\Files\UploadedFile;

function saveImage(UploadedFile $img, $folderpath = "uploads/image")
{
    $data = getInfoImage($img, $folderpath);
    if ($data) {
        return model('ImagesModel')->insert($data);
    }
}
function getImageInfo(UploadedFile $img, $folderpath = "uploads/image")
{
    return getInfoImage($img, $folderpath);
}

function deleteImage($id)
{
    model("ImagesModel")->where('id', $id)->delete();
    /** @todo penser à supprimer aussi les images stockées sur le serveur.*/
}

function getInfoImage(UploadedFile $img, $folderpath = "uploads/image")
{
    if (!$img->hasMoved()) {
        $filepath = $folderpath . date("Ymd") . '/';
        $filename = $img->getRandomName();

        $img->move(WRITEPATH . $filepath, $filename);
        // $img->move(PUBLICPATH . $filepath, $filename);

        return [
            'uri'       => $filepath . $filename,
            'extension' => '.' . $img->getClientExtension(),
            'type'      => $img->getClientMimeType(),
        ];
    }
}
