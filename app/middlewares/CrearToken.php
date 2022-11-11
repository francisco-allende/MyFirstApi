<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class CrearToken
{
    public function __invoke(Request $request, Response $response): Response
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $perfil = $parametros['perfil'];
        $alias = $parametros['alias'];
    
        $datos = array('usuario' => $usuario, 'perfil' => $perfil, 'alias' => $alias);

        $token = AutentificadorJWT::CrearToken($datos); //creo token
        $payload = json_encode(array('jwt' => $token));
    
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}