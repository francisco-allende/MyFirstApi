<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './models/Usuario.php';

//No es mas que una funcion validadora. Uso el __invoke para tratar de llamar a un objeto (el objeto class VerificadorLoginMiddleware)
// Como si fuera una funcion. No lo es, pero hago como que si lo es gracias al __invoke
class VerificadorLoginMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $reponse = new Response();

        $parametros = $request->getParsedBody();

        if(isset($parametros["clave"]) && isset($parametros["usuario"]))
        {
            if($parametros["clave"] != "" && !empty($parametros["usuario"])) //dos maneras de validar campos vacios
            { 
                $reponse = $handler->handle($request); // llama al controllador. No llega hasta el controller de UsuarioController marcado en el index sin anter haber pasado la validacion del Middleware. Gracias a esto, jamas me llega vacio
            }else{
                $reponse->getBody()->write("Error hay campos vacios");
            }
        }else{
            $reponse->getBody()->write("Faltan completar los campos");
        }

        return $reponse;

    }
}