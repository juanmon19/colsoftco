<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require '../config/conexion.php';

// login de acceso
if (isset($_POST['login'])) {
    //proceso de login

    if (isset($_POST['documento']) and isset($_POST['password'])) {
        $login = $_POST['documento'];
        $Password = $_POST['password'];

        login(
            [
                'documento' => $login,
                'password' => $Password
            ]
        );
    } else {
        $_SESSION['error'] = 'Ingrese sus credenciales';
        header("location:../view/login/login.html");
    }
}


// registro nuevos usuarios
if (isset($_POST['registro'])) {
    //crear variables para los datos a enviar

    $Email = $_POST['email'] ?? '';
    $Documento = $_POST['documento'] ?? '';
    $Nombre = $_POST['nombre'] ?? '';
    $Apellido = $_POST['apellido'] ?? '';
    $Rol = $_POST['rol'] ?? '';
    $Password = $_POST['password'] ?? '';



    // var_dump($Rol);
    // die();

    $respuesta = saveUser([
        'email' => $Email,
        'documento' => $Documento,
        'nombre' => $Nombre,
        'apellido' => $Apellido,
        'rol' => $Rol,
        'password' => password_hash($Password, PASSWORD_BCRYPT),

    ]);

    $Mensaje = $respuesta ? 'usuario registrado' : 'Error al registrar usuario';

    $_SESSION['mensaje'] = $Mensaje;

    header("location:../view/registro/registro.html");
}
//function saveUser este metodo es para registrar los usuarios
function saveUser(array $datos)
{
    try {

        $Conex = new Conexion;

        $MiConexion = $Conex->getConnection();

        $Conex->pps = $MiConexion->prepare(
            "INSERT INTO usuarios
            (email, documento, nombre, apellido, rol, password_hash)
            VALUES
            (:email, :documento, :nombre, :apellido, :rol, :password)"
        );

        $Conex->pps->bindParam(":email", $datos['email']);
        $Conex->pps->bindParam(":documento", $datos['documento']);
        $Conex->pps->bindParam(":nombre", $datos['nombre']);
        $Conex->pps->bindParam(":apellido", $datos['apellido']);
        $Conex->pps->bindParam(":rol", $datos['rol']);
        $Conex->pps->bindParam(":password", $datos['password']);

        return $Conex->pps->execute();
    } catch (\Throwable $th) {

        die($th->getMessage());
    } finally {

        $Conex->closeDataBase();
    }
}

//realizar el login al sistema
function login(array $credenciales)
{
    //consultar la base de datos
    $Conex = new Conexion;

    $Usuario = ConsultaUsuario($Conex, ['documento' => $credenciales['documento']]);


    // print_r(ConsultaUsuario($Conex,['name'=>$credenciales['name'],
    // 'email'=>$credenciales['email']]));

    if ($Usuario) {
        $UserName = $Usuario[0]['email'];
        $Email = $Usuario[0]['documento'];

        $HashPassword = $Usuario[0]['password_hash'];

        if ($UserName === $credenciales['documento'] or $Email === $credenciales['documento']) {
            //accesos la verificacion del password
            if (password_verify($credenciales['password'], $HashPassword)) {

                // Guardar los datos del usuario en la sesión
                $_SESSION['rol'] = $Usuario[0]['rol'];
                $_SESSION['nombre'] = $Usuario[0]['nombre'];
                $_SESSION['apellido'] = $Usuario[0]['apellido'];
                $_SESSION['documento'] = $Usuario[0]['documento'];
                $_SESSION['email'] = $Usuario[0]['email'];

                $Rol = $_SESSION['rol'];

                if ($Rol == 'administrador') {
                    header("location:../view/panel_admin/panel_admin.html");
                } elseif ($Rol == 'bodeguero') {
                    header("location:../view/panel_bodeguero/panel_bodeguero.html");
                } elseif ($Rol == 'operario') {
                    header("location:../view/panel_operario/panel_operario.html");
                }

                exit();
        
            } else {
                $_SESSION['error'] = 'Error en el password';
                header("location:../view/login/login.html");
            }
        } else {
            $_SESSION['error'] = 'Error en el documento';
            header("location:../view/login/login.html");
        }
    } else {
        $_SESSION['error'] = 'Error, no existe ese usuario';
        header("location:../view/login/login.html");
    }
}

//consultar usuario
function ConsultaUsuario($conexion, array $dataConsulta)
{

    $consulta = "
      SELECT * FROM usuarios WHERE documento = :documento OR email = :email
    ";

    try {
        $conexion->pps = $conexion->getConnection()->prepare($consulta);

        $conexion->pps->bindParam(":documento", $dataConsulta['documento']);
        $conexion->pps->bindParam(":email", $dataConsulta['documento']);

        $conexion->pps->execute();

        return $conexion->pps->fetchAll();
    } catch (Exception $e) {
        echo $e->getMessage();
    } finally {
        $conexion->closeDataBase();
    }
}
