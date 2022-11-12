<?php

class Usuario
{
    public $id;
    public $usuario;
    public $clave;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO usuarios (usuario, clave) VALUES (:usuario, :clave)");
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();  //funcion ya codeada que me trae al ultimo usuario creado
    }

    public static function getIDByName($usuario){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    }

    public static function obtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, usuario, clave FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function modificarUsuario($id, $usuario, $clave)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave WHERE id = :id");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function modificarClaveUsuarioPorNombre($id, $clave)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET clave = :clave WHERE id = :id");
        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        if($consulta->rowCount() == 1){
            return true;
        }else{
            return false;
        }
    }

    //Comente lo anterior porque no da de baja sino que hace baja logica, agrega una fecha de baja
    public static function borrarUsuario($usuarioId)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM usuarios WHERE id = :id;");
        //$consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja; WHERE id = :id;");
        //$fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $usuarioId, PDO::PARAM_INT);
        //$consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();

        //rowCount es de PDO y retorna el nro de filas afectadas
        if($consulta->rowCount() == 1){
            return true;
        }else{
            return false;
        }
    }

    public static function verificarDatosLogin($user, $clave)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("SELECT id, usuario, clave FROM usuarios WHERE usuario = :user");
        $consulta->bindValue(':user', $user, PDO::PARAM_STR);
        $consulta->execute();

        $userDataBase = $consulta->fetchObject('Usuario'); //retorna un objeto Usuario
        $retorno = -1;

        //var_dump($userDataBase);

        if($userDataBase->usuario) //literal la property usuario ya que creo un objeto usuario
        {
            if(password_verify($clave,$userDataBase->clave) ||  $userDataBase->clave == $clave)
            {
                $retorno = 1; //el usuario exsite y matchea la contraseña, ya sea una de tipo password_hash o no
            }else{
                $retorno = 2; //el usuario exsite, no matchea la contraseña
            }
        }

        return $retorno;
    }
}