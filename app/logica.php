<?php

// El detalle técnico de los errores va al log del servidor, nunca a pantalla.
// Para depurar en local, cambia APP_DEBUG a true en tu .env (o config/setting.php).
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('America/Bogota');

require '../config/conexion.php';
require '../config/setting.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    // Se guarda en la misma carpeta que usa perfil_usuario.php (public/imagenes/perfiles/)
    // y en la BD se guarda SOLO el nombre del archivo, porque así es como
    // panel_admin.php arma la URL: `../../public/imagenes/perfiles/${u.foto}`
    $rutaFotoBD = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $directorioDestino = __DIR__ . '/../public/imagenes/perfiles/';

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'usr_' . time() . '_' . uniqid() . '.' . $extension;
        $rutaCompleta = $directorioDestino . $nombreArchivo;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
            $rutaFotoBD = $nombreArchivo;
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
        // El detalle técnico se va al log del servidor, NUNCA a pantalla.
        error_log('Error de Base de Datos en saveUser(): ' . $th->getMessage());
        return false;
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
        // Verificar si la cuenta está activa
        if (isset($Usuario[0]['activo']) && (int) $Usuario[0]['activo'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
            header("location:../view/login/login.php");
            exit();
        }
        $UsuarioEmail = $Usuario[0]['email'];
        $UsuarioDocumento = $Usuario[0]['documento'];
        $HashPassword = $Usuario[0]['password_hash'];

        if ($UsuarioEmail === $credenciales['documento'] or $UsuarioDocumento === $credenciales['documento']) {
            if (password_verify($credenciales['password'], $HashPassword)) {
                session_regenerate_id(true);

               
                if (!empty($Usuario[0]['token_sesion'])) {
                    $ipIngreso = $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida';
                    $fechaIngreso = date('d/m/Y H:i:s');

                    EnviarAlertaSesionDuplicada(
                        $Usuario[0]['email'],
                        $Usuario[0]['nombre'] . ' ' . $Usuario[0]['apellido'],
                        $ipIngreso,
                        $fechaIngreso
                    );

                    if (class_exists('HistorialMovimientos')) {
                        try {
                            (new HistorialMovimientos())->registrar([
                                'modulo'         => 'Seguridad',
                                'accion'         => 'sesion_duplicada',
                                'id_registro'    => $Usuario[0]['id_usuario'],
                                'descripcion'    => "Se detectó un nuevo inicio de sesión para {$Usuario[0]['nombre']} {$Usuario[0]['apellido']} (Doc: {$Usuario[0]['documento']}) mientras ya existía una sesión activa. IP: {$ipIngreso}.",
                                'usuario_id'     => $Usuario[0]['id_usuario'],
                                'usuario_nombre' => $Usuario[0]['nombre'] . ' ' . $Usuario[0]['apellido'],
                            ]);
                        } catch (\Throwable $e) {
                            error_log('Error al registrar alerta de sesión duplicada: ' . $e->getMessage());
                        }
                    }
                }

                // Generar un nuevo token y guardarlo tanto en la sesión como
                // en la base de datos. Esto invalida automáticamente
                // cualquier sesión anterior (verificar_sesion.php compara
                // este valor en cada petición).
                $nuevoTokenSesion = bin2hex(random_bytes(32));
                guardarTokenSesion($Usuario[0]['id_usuario'], $nuevoTokenSesion);
                refrescarUltimaActividad($Usuario[0]['id_usuario']);
                $_SESSION['token_sesion'] = $nuevoTokenSesion;

                $_SESSION['user_id'] = $Usuario[0]['id_usuario'];
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

// ══════════════════════════════════════════════
// CONTROL DE SESIÓN ÚNICA
// ══════════════════════════════════════════════

/**
 * Guarda el token de sesión activo del usuario en la base de datos.
 * Al hacer login desde otro lugar, este valor cambia y la sesión
 * anterior queda invalidada (ver app/verificar_sesion.php).
 */
function guardarTokenSesion($idUsuario, $token)
{
    $conex = new Conexion();
    $conex->sql = "UPDATE usuarios SET token_sesion = :token WHERE id_usuario = :id";

    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":token", $token);
        $conex->pps->bindParam(":id", $idUsuario);
        return $conex->pps->execute();
    } catch (\Throwable $th) {
        error_log('Error al guardar token_sesion: ' . $th->getMessage());
        return false;
    } finally {
        $conex->closeDataBase();
    }
}

/**
 * Marca "ahora" como última actividad justo al iniciar sesión.
 * Sin esto, el primer chequeo de inactividad en verificar_sesion.php
 * compara contra un valor viejo (de una sesión anterior) y expira
 * la sesión inmediatamente después de loguearse.
 */
function refrescarUltimaActividad($idUsuario)
{
    $conex = new Conexion();
    $conex->sql = "UPDATE usuarios SET ultima_actividad = NOW() WHERE id_usuario = :id";

    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":id", $idUsuario);
        return $conex->pps->execute();
    } catch (\Throwable $th) {
        error_log('Error al refrescar ultima_actividad: ' . $th->getMessage());
        return false;
    } finally {
        $conex->closeDataBase();
    }
}

/**
 * Envía un correo de alerta al dueño de la cuenta cuando se detecta
 * un nuevo inicio de sesión mientras ya existía una sesión activa.
 */
function EnviarAlertaSesionDuplicada($correo, $nombreReceptor, $ip, $fecha)
{
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('colsoftco4@gmail.com', 'Colsoftco - Seguridad');
        $mail->addAddress($correo, $nombreReceptor);

        $mail->isHTML(true);
        $mail->Subject = 'Alerta de seguridad: nuevo inicio de sesion en tu cuenta';

        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/colsoftco';
        $urlCambioPassword = $baseUrl . '/view/recuperar_contrasena/recuperar_contrasena.php';

        $mail->Body    = '
            Hola ' . htmlspecialchars($nombreReceptor) . ',<br><br>
            Detectamos un <b>nuevo inicio de sesión</b> en tu cuenta de COLSOFTCO
            mientras ya había una sesión activa en otro dispositivo o navegador.<br><br>
            <b>Fecha y hora:</b> ' . htmlspecialchars($fecha) . '<br>
            <b>Dirección IP:</b> ' . htmlspecialchars($ip) . '<br><br>
            Si fuiste tu, puedes ignorar este mensaje; la sesion anterior se cerró automaticamente.<br>
            Si <b>no reconoces</b> este ingreso, cambia tu contraseña de inmediato haciendo clic aquí:<br><br>
            <b><a href="' . $urlCambioPassword . '">Cambiar mi contraseña</a></b>
        ';

        $mail->send();
    } catch (Exception $e) {
        error_log('Error al enviar alerta de sesión duplicada: ' . $mail->ErrorInfo);
    }
}