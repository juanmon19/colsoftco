<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
        header("location:../view/login/login.php");
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

// Validar requisitos de la contraseña
if (strlen($Password) < 8) {
    $_SESSION['mensaje'] = 'La contraseña debe tener mínimo 8 caracteres.';
    header("location:../view/registro/registro.php");
    exit();
}

if (!preg_match('/[A-Z]/', $Password)) {
    $_SESSION['mensaje'] = 'La contraseña debe tener al menos una letra mayúscula.';
    header("location:../view/registro/registro.php");
    exit();
}

if (!preg_match('/[a-z]/', $Password)) {
    $_SESSION['mensaje'] = 'La contraseña debe tener al menos una letra minúscula.';
    header("location:../view/registro/registro.php");
    exit();
}

if (!preg_match('/[0-9]/', $Password)) {
    $_SESSION['mensaje'] = 'La contraseña debe tener al menos un número.';
    header("location:../view/registro/registro.php");
    exit();
}

if (!preg_match('/[\W_]/', $Password)) {
    $_SESSION['mensaje'] = 'La contraseña debe tener al menos un carácter especial.';
    header("location:../view/registro/registro.php");
    exit();
}



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

    header("location:../view/registro/registro.php");
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

        error_log('Error en saveUser: ' . $th->getMessage());
        return false;
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




    if ($Usuario) {
        $UsuarioEmail = $Usuario[0]['email'];
        $UsuarioDocumento = $Usuario[0]['documento'];

        $HashPassword = $Usuario[0]['password_hash'];

        if ($UsuarioEmail === $credenciales['documento'] or $UsuarioDocumento === $credenciales['documento']) {
            //accesos la verificacion del password
            if (password_verify($credenciales['password'], $HashPassword)) {
                // Regenerar ID de sesión para evitar fijación de sesión
                session_regenerate_id(true);

                // Guardar los datos del usuario en la sesión
                $_SESSION['rol'] = $Usuario[0]['rol'];
                $_SESSION['nombre'] = $Usuario[0]['nombre'];
                $_SESSION['apellido'] = $Usuario[0]['apellido'];
                $_SESSION['documento'] = $Usuario[0]['documento'];
                $_SESSION['email'] = $Usuario[0]['email'];

                $Rol = $_SESSION['rol'];

                if ($Rol == 'administrador') {
                    header("location:../view/panel_admin/panel_admin.php");
                } elseif ($Rol == 'bodeguero') {
                    header("location:../view/panel_bodeguero/panel_bodeguero.php");
                } elseif ($Rol == 'operario') {
                    header("location:../view/panel_operario/panel_operario.php");
                }

                exit();
        
            } else {
                $_SESSION['error'] = 'Contraseña Incorrecta';
                header("location:../view/login/login.php");
            }
        } else {
            $_SESSION['error'] = 'Error en el documento';
            header("location:../view/login/login.php");
        }
    } else {
        $_SESSION['error'] = 'Error, no existe ese usuario';
        header("location:../view/login/login.php");
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
        error_log('Error en ConsultaUsuario: ' . $e->getMessage());
        return [];
    } finally {
        $conexion->closeDataBase();
    }
}
