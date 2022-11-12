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
        echo $payload;

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
        echo $payload; //no pude hacer que no se superpongan el write body payload con el token valido del middleware

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
        echo $payload;

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
        echo $payload;
  
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
}
