<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;

trait ErrorsDataTrait
{
    /**
     * etatsList
     * 
     * renvoie la liste des etats d'une entite
     *
     * @return array
     */
    public function getErrorsData(\Throwable $th, $hasErrors): array
    {
        if ($hasErrors) {
            $errors = $this->validator->getErrors();
            $code = ResponseInterface::HTTP_NOT_ACCEPTABLE;
        } else {
            $errors = $th->getMessage();
            $code = ResponseInterface::HTTP_INTERNAL_SERVER_ERROR;
        }

        return [
            'errors' => $errors,
            'code' => $code,
        ];
    }
}
