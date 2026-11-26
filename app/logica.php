<?php

// El detalle técnico de los errores va al log del servidor, nunca a pantalla.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Bogota');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/permisos.php';

// Cargas protegidas para que nunca generen un error 500 si falta algún archivo auxiliar
foreach ([
    __DIR__ . '/../config/setting.php',
    __DIR__ . '/correo_plantilla.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/HistorialMovimientos.php',
] as $archivoOpcional) {
    if (file_exists($archivoOpcional)) {
        require_once $archivoOpcional;
    } else {
        error_log('Aviso en logica.php: no se encontró ' . $archivoOpcional);
    }
}

// ==============================================
// LOGIN DE ACCESO
// ==============================================
if (isset($_POST['login'])) {
    if (isset($_POST['documento']) && isset($_POST['password'])) {
        login([
            'documento' => trim((string) $_POST['documento']),
            'password'  => (string) $_POST['password'],
        ]);
    } else {
        $_SESSION['error'] = 'Ingrese sus credenciales';
        header("Location: ../view/login/login.php");
        exit();
    }
}

// ==============================================
// REGISTRO NUEVOS USUARIOS (SOLO ADMINISTRADOR + CSRF)
// ==============================================
if (isset($_POST['registro'])) {
    require_once __DIR__ . '/verificar_sesion.php';

    if (!es_admin()) {
        $_SESSION['mensaje'] = 'Sin permiso para registrar usuarios.';
        header("Location: ../view/login/login.php");
        exit();
    }

    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        $_SESSION['mensaje'] = 'Sesión inválida. Recarga la página e inténtalo de nuevo.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    $Email     = $_POST['email'] ?? '';
    $Documento = $_POST['documento'] ?? '';
    $Nombre    = $_POST['nombre'] ?? '';
    $Apellido  = $_POST['apellido'] ?? '';
    $Rol       = $_POST['rol'] ?? '';
    $Password  = $_POST['password'] ?? '';
    $Telefono  = $_POST['telefono'] ?? '';

    if (!in_array($Rol, ['administrador', 'bodeguero', 'operario'], true)) {
        $_SESSION['mensaje'] = 'Selecciona un rol válido.';
        header('Location: ../view/registro/registro.php');
        exit();
    }

    // Validar requisitos de la contraseña
    if (strlen($Password) < 8) {
        $_SESSION['mensaje'] = 'La contraseña debe tener mínimo 8 caracteres.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    if (!preg_match('/[A-Z]/', $Password)) {
        $_SESSION['mensaje'] = 'La contraseña debe tener al menos una letra mayúscula.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    if (!preg_match('/[a-z]/', $Password)) {
        $_SESSION['mensaje'] = 'La contraseña debe tener al menos una letra minúscula.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    if (!preg_match('/[0-9]/', $Password)) {
        $_SESSION['mensaje'] = 'La contraseña debe tener al menos un número.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    if (!preg_match('/[\W_]/', $Password)) {
        $_SESSION['mensaje'] = 'La contraseña debe tener al menos un carácter especial.';
        header("Location: ../view/registro/registro.php");
        exit();
    }

    // Subida segura de foto de perfil
    $rutaFotoBD = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        require_once __DIR__ . '/upload_seguro.php';
        try {
            $rutaFotoBD = guardarImagenSegura(
                $_FILES['foto'],
                __DIR__ . '/../public/imagenes/perfiles/',
                7 * 1024 * 1024,
                'usr'
            );
        } catch (\Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            header("Location: ../view/registro/registro.php");
            exit();
        }
    }

    // Registro en base de datos
    $nuevoUsuarioId = saveUser([
        'email'     => $Email,
        'documento' => $Documento,
        'nombre'    => $Nombre,
        'apellido'  => $Apellido,
        'rol'       => $Rol,
        'telefono'  => $Telefono,
        'foto'      => $rutaFotoBD,
        'password'  => password_hash($Password, PASSWORD_BCRYPT),
    ]);

    if ($nuevoUsuarioId) {
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
                        'foto'      => $rutaFotoBD,
                    ],
                    'usuario_id'     => $_SESSION['user_id'] ?? null,
                    'usuario_nombre' => $_SESSION['nombre'] ?? 'Sistema',
                ]);
            } catch (\Throwable $e) {
                error_log('Error al registrar historial en registro: ' . $e->getMessage());
            }
        }

        $_SESSION['mensaje'] = 'Usuario registrado exitosamente';
    } else {
        $_SESSION['mensaje'] = 'Error al registrar usuario';
    }

    header("Location: ../view/registro/registro.php");
    exit();
}

// ==============================================
// FUNCIONES DE BASE DE DATOS Y AUTENTICACIÓN
// ==============================================
function saveUser(array $datos)
{
    $Conex = null;
    try {
        $Conex = new Conexion();
        $MiConexion = $Conex->getConnection();
        if (!$MiConexion) {
            throw new \RuntimeException('No se pudo obtener la conexión a la base de datos.');
        }

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
        error_log('Error de Base de Datos en saveUser(): ' . $th->getMessage());
        return false;
    } finally {
        if ($Conex && method_exists($Conex, 'closeDataBase')) {
            $Conex->closeDataBase();
        }
    }
}

function obtenerIdDispositivo(): string
{
    $deviceId = $_COOKIE['colsoftco_dev'] ?? '';
    if (!is_string($deviceId) || !preg_match('/^[a-f0-9]{16}$/', $deviceId)) {
        $deviceId = bin2hex(random_bytes(8));
    }

    setcookie('colsoftco_dev', $deviceId, [
        'expires'  => time() + (365 * 24 * 60 * 60),
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    return $deviceId;
}

function login(array $credenciales)
{
    try {
        $Conex = new Conexion();
        $Usuario = ConsultaUsuario($Conex, ['documento' => $credenciales['documento']]);
    } catch (\Throwable $th) {
        error_log('Error crítico en login(): ' . $th->getMessage());
        $_SESSION['error'] = 'No se pudo conectar con la base de datos. Intenta de nuevo.';
        header("Location: ../view/login/login.php");
        exit();
    }

    if ($Usuario) {
        if (isset($Usuario[0]['activo']) && (int) $Usuario[0]['activo'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta ha sido desactivada. Contacta al administrador.';
            header("Location: ../view/login/login.php");
            exit();
        }

        $UsuarioEmail     = $Usuario[0]['email'];
        $UsuarioDocumento = $Usuario[0]['documento'];
        $HashPassword     = $Usuario[0]['password_hash'];

        if ($UsuarioEmail === $credenciales['documento'] || $UsuarioDocumento === $credenciales['documento']) {
            if (password_verify($credenciales['password'], $HashPassword)) {
                $ipIngreso      = $_SERVER['REMOTE_ADDR'] ?? 'IP desconocida';
                $fechaIngreso   = date('d/m/Y H:i:s');
                $nombreCompleto = trim($Usuario[0]['nombre'] . ' ' . $Usuario[0]['apellido']);

                // Identificador persistente de este dispositivo (16 caracteres hex)
                $deviceId = obtenerIdDispositivo();

                // Solo es sesión duplicada si había una sesión ACTIVA (<= 30 min)
                // abierta desde OTRO dispositivo o navegador diferente.
                $tokenAnterior   = (string) ($Usuario[0]['token_sesion'] ?? '');
                $ultimaActividad = $Usuario[0]['ultima_actividad'] ?? null;
                $minutosMax      = defined('MINUTOS_INACTIVIDAD_MAX') ? (int) MINUTOS_INACTIVIDAD_MAX : 30;

                $sesionAunActiva = false;
                if ($tokenAnterior !== '' && !empty($ultimaActividad)) {
                    $tsUltima = strtotime($ultimaActividad);
                    if ($tsUltima !== false && ((time() - $tsUltima) / 60) <= $minutosMax) {
                        $sesionAunActiva = true;
                    }
                }

                $mismoDispositivo = ($tokenAnterior !== '' && strlen($tokenAnterior) === 64)
                    && hash_equals(substr($tokenAnterior, 0, 16), $deviceId);

                $esSesionDuplicada = $sesionAunActiva && !$mismoDispositivo;

                if (class_exists('HistorialMovimientos')) {
                    try {
                        if ($esSesionDuplicada) {
                            (new HistorialMovimientos())->registrar([
                                'modulo'         => 'Seguridad',
                                'accion'         => 'sesion_duplicada',
                                'id_registro'    => $Usuario[0]['id_usuario'],
                                'descripcion'    => "Se detectó un nuevo inicio de sesión desde otro dispositivo para {$nombreCompleto} (Doc: {$Usuario[0]['documento']}) mientras ya existía una sesión activa. IP: {$ipIngreso}.",
                                'usuario_id'     => $Usuario[0]['id_usuario'],
                                'usuario_nombre' => $nombreCompleto,
                            ]);
                        } else {
                            (new HistorialMovimientos())->registrar([
                                'modulo'         => 'Seguridad',
                                'accion'         => 'inicio_sesion',
                                'id_registro'    => $Usuario[0]['id_usuario'],
                                'descripcion'    => "Inicio de sesión de {$nombreCompleto} (Doc: {$Usuario[0]['documento']}). IP: {$ipIngreso}.",
                                'usuario_id'     => $Usuario[0]['id_usuario'],
                                'usuario_nombre' => $nombreCompleto,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        error_log('Error al registrar evento de inicio de sesión: ' . $e->getMessage());
                    }
                }

                // Token de 64 chars: 16 chars del dispositivo + 48 chars aleatorios
                $nuevoTokenSesion = $deviceId . bin2hex(random_bytes(24));
                guardarTokenSesion($Usuario[0]['id_usuario'], $nuevoTokenSesion);
                refrescarUltimaActividad($Usuario[0]['id_usuario']);

                $_SESSION = [];
                session_regenerate_id(true);
                $_SESSION['token_sesion'] = $nuevoTokenSesion;
                $_SESSION['user_id']      = (int) $Usuario[0]['id_usuario'];
                $_SESSION['id_usuario']   = (int) $Usuario[0]['id_usuario'];
                $_SESSION['rol']          = strtolower(trim($Usuario[0]['rol']));
                $_SESSION['nombre']       = $Usuario[0]['nombre'];
                $_SESSION['apellido']     = $Usuario[0]['apellido'];
                $_SESSION['documento']    = $Usuario[0]['documento'];
                $_SESSION['email']        = $Usuario[0]['email'];

                EnviarAlertaInicioSesion(
                    $_SESSION['email'],
                    $nombreCompleto,
                    $ipIngreso,
                    $fechaIngreso,
                    $esSesionDuplicada
                );

                $Rol = $_SESSION['rol'];

                if ($Rol === 'administrador') {
                    header("Location: ../view/panel_admin/panel_admin.php");
                } elseif ($Rol === 'bodeguero') {
                    header("Location: ../view/panel_bodeguero/panel_bodeguero.php");
                } elseif ($Rol === 'operario') {
                    header("Location: ../view/panel_operario/panel_operario.php");
                } else {
                    $_SESSION['error'] = 'Rol de usuario no reconocido.';
                    header("Location: ../view/login/login.php");
                }
                exit();
            } else {
                $_SESSION['error'] = 'Contraseña Incorrecta';
                header("Location: ../view/login/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Error en el documento';
            header("Location: ../view/login/login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Error, no existe ese usuario';
        header("Location: ../view/login/login.php");
        exit();
    }
}

function ConsultaUsuario($conexion, array $dataConsulta)
{
    $consulta = "SELECT * FROM usuarios WHERE documento = :documento OR email = :email";

    try {
        $conn = $conexion->getConnection();
        if (!$conn) {
            throw new \RuntimeException('Conexión nula en ConsultaUsuario.');
        }
        $conexion->pps = $conn->prepare($consulta);
        $conexion->pps->bindParam(":documento", $dataConsulta['documento']);
        $conexion->pps->bindParam(":email", $dataConsulta['documento']);
        $conexion->pps->execute();

        return $conexion->pps->fetchAll();
    } catch (\Throwable $e) {
        error_log('Error en ConsultaUsuario: ' . $e->getMessage());
        return [];
    } finally {
        if ($conexion && method_exists($conexion, 'closeDataBase')) {
            $conexion->closeDataBase();
        }
    }
}

// ==============================================
// CONTROL DE SESIÓN ÚNICA
// ==============================================
function guardarTokenSesion($idUsuario, $token)
{
    $conex = null;
    try {
        $conex = new Conexion();
        $conn = $conex->getConnection();
        if (!$conn) {
            return false;
        }
        $conex->sql = "UPDATE usuarios SET token_sesion = :token WHERE id_usuario = :id";
        $conex->pps = $conn->prepare($conex->sql);
        $conex->pps->bindParam(":token", $token);
        $conex->pps->bindParam(":id", $idUsuario);
        return $conex->pps->execute();
    } catch (\Throwable $th) {
        error_log('Error al guardar token_sesion: ' . $th->getMessage());
        return false;
    } finally {
        if ($conex && method_exists($conex, 'closeDataBase')) {
            $conex->closeDataBase();
        }
    }
}

function refrescarUltimaActividad($idUsuario)
{
    $conex = null;
    try {
        $conex = new Conexion();
        $conn = $conex->getConnection();
        if (!$conn) {
            return false;
        }
        $ahora = date('Y-m-d H:i:s');
        $conex->sql = "UPDATE usuarios SET ultima_actividad = :ahora WHERE id_usuario = :id";
        $conex->pps = $conn->prepare($conex->sql);
        $conex->pps->bindParam(":ahora", $ahora);
        $conex->pps->bindParam(":id", $idUsuario);
        return $conex->pps->execute();
    } catch (\Throwable $th) {
        error_log('Error al refrescar ultima_actividad: ' . $th->getMessage());
        return false;
    } finally {
        if ($conex && method_exists($conex, 'closeDataBase')) {
            $conex->closeDataBase();
        }
    }
}

// Notifica al titular después de cada autenticación exitosa.
function EnviarAlertaInicioSesion($correo, $nombreReceptor, $ip, $fecha, bool $habiaSesion)
{
    if (
        !class_exists(PHPMailer::class) ||
        !function_exists('prepararCorreo') ||
        !defined('HOST') ||
        !defined('USERNAME') ||
        !defined('PASSWORD') ||
        USERNAME === '' ||
        PASSWORD === ''
    ) {
        return;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->Timeout    = 8;

        $mail->setFrom(USERNAME, 'Colsoftco - Seguridad');
        $mail->addAddress($correo, $nombreReceptor);

        if (defined('BASE_URL') && BASE_URL !== '') {
            $base = rtrim(BASE_URL, '/');
        } else {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $scheme . '://' . $host . colsoftco_base_path();
        }

        prepararCorreo(
            $mail,
            'Nuevo inicio de sesión en COLSOFTCO',
            "Hola, {$nombreReceptor}.",
            $habiaSesion
                ? 'Se inició sesión en tu cuenta desde otro dispositivo y se reemplazó la sesión anterior.'
                : 'Se inició sesión en tu cuenta de COLSOFTCO.',
            ['Fecha y hora' => $fecha, 'Dirección IP' => $ip],
            'Si reconoces este ingreso, puedes seguir usando tu cuenta. Si no lo reconoces, solicita un cambio de contraseña.',
            'Cambiar mi contraseña',
            $base . '/view/recuperar_contrasena/recuperar_contrasena.php'
        );

        $mail->send();
    } catch (\Throwable $e) {
        error_log('Error al enviar el aviso de inicio de sesión: ' . $e->getMessage());
    }
}