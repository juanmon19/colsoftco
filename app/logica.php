<?php

// Habilitar errores en pantalla para identificar la falla exacta
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require '../config/conexion.php';

// Verificar si existe la clase Historial antes de requerirla

$rutaHistorial = __DIR__ . '/HistorialMovimientos.php';

if (file_exists($rutaHistorial)) {
    require_once $rutaHistorial;
}

// login de acceso
if (isset($_POST['login'])) {
    if (isset($_POST['documento']) and isset($_POST['password'])) {
        $login = $_POST['documento'];
        $Password = $_POST['password'];

        login([
            'documento' => $login,
            'password' => $Password
        ]);
    } else {
        $_SESSION['error'] = 'Ingrese sus credenciales';
        header("location:../view/registro/registro.php");
        exit();
    }
}

// registro nuevos usuarios
if (isset($_POST['registro'])) {

    $Email = $_POST['email'] ?? '';
    $Documento = $_POST['documento'] ?? '';
    $Nombre = $_POST['nombre'] ?? '';
    $Apellido = $_POST['apellido'] ?? '';
    $Rol = $_POST['rol'] ?? '';
    $Password = $_POST['password'] ?? '';
    $Telefono = $_POST['telefono'] ?? '';

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

    // MANEJO Y SUBIDA DE LA FOTO
    $rutaFotoBD = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = __DIR__ . '/../public/uploads/usuarios/';
        
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'usr_' . time() . '_' . uniqid() . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
            $rutaFotoBD = 'public/uploads/usuarios/' . $nombreArchivo;
        }
    }

    // REGISTRO EN BASE DE DATOS
    $nuevoUsuarioId = saveUser([
        'email' => $Email,
        'documento' => $Documento,
        'nombre' => $Nombre,
        'apellido' => $Apellido,
        'rol' => $Rol,
        'telefono' => $Telefono,
        'foto' => $rutaFotoBD,
        'password' => password_hash($Password, PASSWORD_BCRYPT),
    ]);

    if ($nuevoUsuarioId) {
        // REGISTRO EN HISTORIAL DE MOVIMIENTOS
        if (class_exists('HistorialMovimientos')) {
            try {
                $historial = new HistorialMovimientos();
                $historial->registrar([
                    'modulo'         => 'Usuarios',
                    'accion'         => 'Registro',
                    'id_registro'    => $nuevoUsuarioId,
                    'descripcion'    => "Se registró el usuario {$Nombre} {$Apellido} (Doc: {$Documento}) con el rol {$Rol}.",
                    'datos_nuevos'   => [
                        'email'     => $Email,
                        'documento' => $Documento,
                        'nombre'    => $Nombre,
                        'apellido'  => $Apellido,
                        'rol'       => $Rol,
                        'foto'      => $rutaFotoBD
                    ],
                    'usuario_id'     => $_SESSION['user_id'] ?? null,
                    'usuario_nombre' => $_SESSION['nombre'] ?? 'Sistema'
                ]);
            } catch (\Throwable $e) {
                error_log('Error al registrar historial en registro: ' . $e->getMessage());
            }
        }

        $_SESSION['mensaje'] = 'Usuario registrado exitosamente';
    } else {
        $_SESSION['mensaje'] = 'Error al registrar usuario';
    }

    header("location:../view/registro/registro.php");
    exit();
}

// Función saveUser con captura de errores
function saveUser(array $datos)
{
    try {
        $Conex = new Conexion;
        $MiConexion = $Conex->getConnection();

        $Conex->pps = $MiConexion->prepare(
            "INSERT INTO usuarios
            (email, documento, nombre, apellido, rol, telefono, foto, password_hash)
            VALUES
            (:email, :documento, :nombre, :apellido, :rol, :telefono, :foto, :password)"
        );

        $Conex->pps->bindParam(":email", $datos['email']);
        $Conex->pps->bindParam(":documento", $datos['documento']);
        $Conex->pps->bindParam(":nombre", $datos['nombre']);
        $Conex->pps->bindParam(":apellido", $datos['apellido']);
        $Conex->pps->bindParam(":rol", $datos['rol']);
        $Conex->pps->bindParam(":telefono", $datos['telefono']);
        $Conex->pps->bindParam(":foto", $datos['foto']);
        $Conex->pps->bindParam(":password", $datos['password']);

        if ($Conex->pps->execute()) {
            return $MiConexion->lastInsertId();
        }
        return false;
    } catch (\Throwable $th) {
        // Muestra el mensaje exacto de PDO si falta una columna o hay fallo SQL
        echo "<h3>Error de Base de Datos / Ejecución:</h3>";
        echo "<pre>" . $th->getMessage() . "</pre>";
        exit();
    } finally {
        $Conex->closeDataBase();
    }
}

// realizar el login al sistema
function login(array $credenciales)
{
    $Conex = new Conexion;
    $Usuario = ConsultaUsuario($Conex, ['documento' => $credenciales['documento']]);

    if ($Usuario) {
        $UsuarioEmail = $Usuario[0]['email'];
        $UsuarioDocumento = $Usuario[0]['documento'];
        $HashPassword = $Usuario[0]['password_hash'];

        if ($UsuarioEmail === $credenciales['documento'] or $UsuarioDocumento === $credenciales['documento']) {
            if (password_verify($credenciales['password'], $HashPassword)) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $Usuario[0]['id'];
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
                exit();
            }
        } else {
            $_SESSION['error'] = 'Error en el documento';
            header("location:../view/login/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Error, no existe ese usuario';
        header("location:../view/login/login.php");
        exit();
    }
}

function ConsultaUsuario($conexion, array $dataConsulta)
{
    $consulta = "SELECT * FROM usuarios WHERE documento = :documento OR email = :email";

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