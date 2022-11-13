<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class EsAdmin
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $reponse = new Response();
        $parametros = $request->getParsedBody();

        if(isset($parametros["perfil"]))
        {
            if($parametros["perfil"] == "admin")
            { 
                $reponse = $handler->handle($request);
            }else{
                $reponse->getBody()->write("Error, no tiene el permiso de administrador");
            }
        }else{
            $reponse->getBody()->write("Faltan completar los campos");
        }

        return $reponse;
    }
}