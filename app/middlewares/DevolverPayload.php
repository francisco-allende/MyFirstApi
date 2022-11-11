<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class DevolverPayload
{
    public function __invoke(Request $request, Response $response): Response
    {
        $reponse = new Response();

        $parametros = $request->getParsedBody();

        $header = $request->getHeaderLine('Authorization'); //ruta privada, requiere header de autenticacion
        $token = trim(explode("Bearer", $header)[1]);
    
        try {
          $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
        } catch (Exception $e) {
          $payload = json_encode(array('error' => $e->getMessage()));
        }
    
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}