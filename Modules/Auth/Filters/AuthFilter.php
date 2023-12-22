<?php

namespace Modules\Auth\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = auth()->user();
        /** @var JWTManager $manager */
        $manager = service('jwtmanager');

        $utilisateur = model("UtilisateursModel")->where('user_id', $user->id)->first();
        $utilisateur->defaultProfil;
        @$request->utilisateur = $utilisateur;
        @$request->newToken = $manager->generateToken($user);

        return $request;
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $body = json_decode($response->getJSON(), true);
        !is_array($body) ? $body = (array)$body : null;
        $body['statut']  = $body['statut'] ?? "ok";
        $body['data']    = $body['data']   ?? [];
        $body['token']   = $body['token']  ?? $request->newToken ?? '';
        $body['errors']  = $body['errors'] ?? null;

        $response->setJSON($body);
    }
}
