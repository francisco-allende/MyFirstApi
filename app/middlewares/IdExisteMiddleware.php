<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './models/Usuario.php';

class IdExisteMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $reponse = new Response();

        $parametros = $request->getParsedBody();

        if(isset($parametros["id"]))
        {
            $value = intval($parametros["id"]);

            if(is_int($value) && $value > 0) 
            { 
                $reponse = $handler->handle($request); // llama al controllador.
            }else{
                $reponse->getBody()->write("El id es incorrecto");
            }
        }else{
            $reponse->getBody()->write("Falta el id del usuario");
        }

        return $reponse;

    }
}