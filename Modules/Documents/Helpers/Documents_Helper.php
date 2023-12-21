<?php

use CodeIgniter\HTTP\Files\UploadedFile;

function saveDocument($titre, UploadedFile $img, $folderpath = "uploads/Documents")
{
    $data = getInfoDoc($img, $folderpath);
    if ($data) {
        $data['titre'] = $titre;
        return model('DocumentsModel')->insert($data);
    }
}

function deleteDocument($id)
{
    model("DocumentsModel")->where('id', $id)->delete();
    /** @todo penser à supprimer aussi les images stockées sur le serveur.*/
}

function getInfoDoc(UploadedFile $doc, $folderpath = "uploads/Documents")
{
    if (!$doc->hasMoved()) {
        $filepath = $folderpath . date("Ymd") . '/';
        $filename = $doc->getRandomName();

        $doc->move(WRITEPATH . $filepath, $filename);
        // $img->move(PUBLICPATH . $filepath, $filename);

        return [
            'uri'       => $filepath . $filename,
            'extension' => '.' . $doc->getClientExtension(),
            'type'      => $doc->getClientMimeType(),
        ];
    }
}
