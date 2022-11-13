<?php 

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
require_once "./utils/AutentificadorJWT.php"; //es importante tener en cuenta que estoy siempre parado en index.php

class VerificarJWT{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        //Chequeo que la ruta privada tenga un jwt activo y con firma (signature) valida
        try
        {
            $header = $request->getHeaderLine('Authorization');
            $response = new Response();
            if (!empty($header) && $header != null) 
            {
                $token = trim(explode("Bearer", $header)[1]);
                AutentificadorJWT::VerificarToken($token);
                $response = $handler->handle($request);
            }else{
                $payload = json_encode(array('error' => 'Token vacio'));
                $response->getBody()->write($payload);
            }
        }catch (\Throwable $e) 
        {
            //Solo escribo en el payload si hay un error. Sino, no lo informo. Me libero del error de que se pisen los write body de payloads
            $payload = json_encode(array('error' => $e->getMessage()));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}



