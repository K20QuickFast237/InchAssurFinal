<?php

namespace App\Traits;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\IncomingRequest;

trait ControllerUtilsTrait
{
    /**
     * Generates a response with the given response body and status code.
     *
     * @param array $responseBody The response body.
     * @param int $code The status code (default: ResponseInterface::HTTP_OK).
     * @return Response The generated response.
     */
    public function sendResponse(array $responseBody, int $code = ResponseInterface::HTTP_OK)
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON($responseBody);
    }


    /**
     * Retourne la valeur castée de la valeur en paramètre
     * Le cast est entier où chaine.
     *
     * @param  string $value
     * @return string|int
     */
    public function getConvertedValue(string $value)
    {
        if ($value == 0) {
            return (int)$value;
        } else {
            $valueNum = (int)$value;
            // if (strlen($value) >= 1 && $valueNum) {
            if (strlen($value) == strlen($valueNum)) {
                return $valueNum;
            }
        }
        return (string)$value;
    }


    /**
     * caste le champ identificateur dont le nom est code où celui indiqué dans le paramètre $idname
     *
     * @param  string $identifier - valeur de l'identificateur
     * @param  string $idName - nom de l'identificateur
     * @return array
     */
    private function getIdentifier(string $identifier, string $idName = 'id'): array
    {
        if (is_int($this->getConvertedValue($identifier))) {
            return [
                'name'  => $idName,
                'value' => (int)$identifier,
            ];
        }

        return [
            'name' => 'code',
            'value' => $identifier
        ];
    }

    /**
     * Retrieves the input from the incoming request.
     *
     * @param IncomingRequest $request The incoming request object.
     * @return mixed The input data from the request.
     */
    public function getRequestInput(IncomingRequest $request)
    {
        $input = $request->getPost();
        if (empty($input)) {
            //convert request body to associative array
            $input = json_decode($request->getBody() ?? "", true);
        }
        // print_r($input);
        return $input;
    }
}
