<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './utils/AutentificadorJWT.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        // Creamos el usuario
        $usr = new Usuario();
        $usr->usuario = $usuario;
        $usr->clave = $clave;
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        // Buscamos usuario por nombre
        $usr = $args['usuario'];
        $usuario = Usuario::obtenerUsuario($usr);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
    
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarClavePorNombre($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usr = $args['usuario'];
        $u = Usuario::getIDByName($usr);

        $claveACambiar = $parametros['clave'];
        $fueModificado = Usuario::modificarClaveUsuarioPorNombre($u->id, $claveACambiar);

        $payload = "";
        if($fueModificado){
          $payload = json_encode(array("mensaje" => "Clave modificada con exito"));
        }else{
          $payload = json_encode(array("mensaje" => "No se pudo modificar la clave. Usuario no encontrado"));
        }

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id = $parametros['id'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];
      

        Usuario::modificarUsuario($id, $usuario, $clave);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    //cambiar el mensaje para cuando no existe
    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuarioId = $parametros['id'];
        $fueBorrado = Usuario::borrarUsuario($usuarioId);

        $payload = "";
        if($fueBorrado){
          $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        }else{
          $payload = json_encode(array("mensaje" => "No se pudo borrar. Usuario no encontrado"));
        }
  
        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    //No use al final el fecha antes middleware porque se me pisaban los dos response body y se me complico parsearlo a string
    public function Login($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        $retornoLogin = Usuario::verificarDatosLogin($usuario, $clave);
        
        if($retornoLogin === 1)
        {       
          $datos = array('usuario' => $usuario);
          $token = AutentificadorJWT::CrearToken($datos);
          //soluciono el response que se pisa y opaca al otro
          $payload = json_encode(array('jwt' => $token));
          $payload .= json_encode(array('fecha' => $msj = UsuarioController::GetFechaLogueo()));
          $payload .= json_encode(array('msj' => "Sesión iniciada correctamente"));

          $response->getBody()->write($payload);
          return $response
            ->withHeader(
              'Content-Type',
              'application/json'
            );
        }
        else if($retornoLogin === 2)
        {
          $response->getBody()->write("Datos Invalidos. Contraseña incorrecta");
        }
        else if($retornoLogin === -1)
        {
          $response->getBody()->write("El usuario no existe");
        }

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function GetFechaLogueo()
    {
        $fechaAntes = date("d/m/Y h:i:s"); //minuto es i, sino pone el mes
        sleep(1);
        $fechaAhora = date("d/m/Y h:i:s");
        return "Fecha Logueo: $fechaAntes ". " ". "Fecha Entrada $fechaAhora";
    }

    //Ejercicio Middleware Credenciales
    //Como traer con get un usuario. No uso body en postman que es con: $parametros = $request->getParsedBody();
    //Sino que uso  $args['usuario'];
    //en el ruteo del index, debe ir '/{usuario}' o sino el mi programa no espera ningun parametro
    public function VerificarCredenciales($request, $response, $args)
    {
        $usr = $args['usuario'];
        //Como lo que retorna Usuario::obtenerUsuario($usr); es un objeto, lo paso a json. Accedo a propiedades con ->
        //el payload es siempre un json. 
        $usuario = Usuario::obtenerUsuario($usr); 
     
        $payload = json_encode(array("Verifico credenciales" => "Bienvenido $usuario->usuario, No necesito credenciales para get"));
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function VerificarCredencialesPost($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $perfil = $parametros['perfil'];
        
        $usuario = Usuario::obtenerUsuario($nombre); 

        $payload = json_encode(array("Verifico credenciales" => "Bienvenido $usuario->usuario, Post necesita credenciales"));
        $response->getBody()->write($payload);

        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
