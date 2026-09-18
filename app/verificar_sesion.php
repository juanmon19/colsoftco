<?php

date_default_timezone_set('America/Bogota');

// Configuración de seguridad de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

require_once __DIR__ . '/permisos.php';

// URL única de login (absoluta para no depender del cwd ni de la carpeta actual)
if (!function_exists('colsoftco_login_url')) {
    function colsoftco_login_url(): string {
        return '/colsoftco/view/login/login.php';
    }
}

// Verificar si el usuario inició sesión
if (!isset($_SESSION['documento'])) {
    header("Location: " . colsoftco_login_url());
    exit();
}

// ══ CONTROL DE SESIÓN ÚNICA ══
// Verifica que el token de sesión coincida con el de la BD.
// Si alguien más inició sesión con la misma cuenta, el token en BD
// habrá cambiado y esta sesión será invalidada.
if (isset($_SESSION['token_sesion'])) {
    require_once __DIR__ . '/../config/conexion.php';

    $__dbCheck = new Conexion();
    $__stmtCheck = $__dbCheck->getConnection()->prepare(
        "SELECT token_sesion FROM usuarios WHERE documento = :doc LIMIT 1"
    );
    $__stmtCheck->execute([':doc' => $_SESSION['documento']]);
    $__rowCheck = $__stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$__rowCheck || $__rowCheck['token_sesion'] !== $_SESSION['token_sesion']) {
        // Alguien más inició sesión con esta cuenta
        session_unset();
        session_destroy();
        echo '<script>
            alert("Alguien más ingresó a tu cuenta. Por favor, loguéate de nuevo.");
            window.location.href = "' . colsoftco_login_url() . '";
        </script>';
        exit();
    }
}

// ══ VERIFICAR CUENTA ACTIVA Y EXPIRACIÓN POR INACTIVIDAD ══
if (!defined('MINUTOS_INACTIVIDAD_MAX')) {
    define('MINUTOS_INACTIVIDAD_MAX', 30);
}

if (!isset($__dbCheck)) {
    $__dbCheck = new Conexion();
}
    $__stmtActivo = $__dbCheck->getConnection()->prepare(
        "SELECT id_usuario, nombre, apellido, email, rol, activo, ultima_actividad FROM usuarios WHERE documento = :doc LIMIT 1"
    );
    $__stmtActivo->execute([':doc' => $_SESSION['documento']]);
    $__rowActivo = $__stmtActivo->fetch(PDO::FETCH_ASSOC);
    // Refresca la sesión desde la BD para que cambios de nombre/rol hechos
    // por el admin apliquen sin re-login (evita ver otro nombre/rol viejo).
    if ($__rowActivo) {
        $_SESSION['user_id'] = (int) ($__rowActivo['id_usuario'] ?? $_SESSION['user_id'] ?? 0);
        $_SESSION['id_usuario'] = (int) $_SESSION['user_id'];
        unset($_SESSION['id'], $_SESSION['usuario_id']);
        $_SESSION['nombre'] = $__rowActivo['nombre'] ?? ($_SESSION['nombre'] ?? '');
        $_SESSION['apellido'] = $__rowActivo['apellido'] ?? ($_SESSION['apellido'] ?? '');
        $_SESSION['email'] = $__rowActivo['email'] ?? ($_SESSION['email'] ?? '');
        $_SESSION['rol'] = strtolower(trim($__rowActivo['rol'] ?? ($_SESSION['rol'] ?? '')));
    }

    if (!$__rowActivo || (int) $__rowActivo['activo'] !== 1) {
        session_unset();
        session_destroy();
        echo '<script>
            alert("Tu cuenta ha sido desactivada por el administrador. Contacta al administrador.");
            window.location.href = "' . colsoftco_login_url() . '";
        </script>';
        exit();
    }

    // Si la última actividad registrada supera el umbral, se cierra la sesión.
    if (!empty($__rowActivo['ultima_actividad'])) {
        $__minutosInactivo = (time() - strtotime($__rowActivo['ultima_actividad'])) / 60;

        if ($__minutosInactivo > MINUTOS_INACTIVIDAD_MAX) {
            session_unset();
            session_destroy();
            echo '<script>
                alert("Tu sesión expiró por inactividad. Por favor, inicia sesión de nuevo.");
                window.location.href = "' . colsoftco_login_url() . '";
            </script>';
            exit();
        }
    }

    // Actualizar última actividad
    $__stmtAct = $__dbCheck->getConnection()->prepare(
        "UPDATE usuarios SET ultima_actividad = NOW() WHERE documento = :doc"
    );
    $__stmtAct->execute([':doc' => $_SESSION['documento']]);

// ══ GUARDIA AUTOMÁTICA DE ROLES ══
// Todas las vistas que incluyen este archivo quedan protegidas según app/permisos.php.
// Así el rol NO cambia al navegar: si un bodeguero entra a una ruta de admin,
// se le devuelve a su panel en vez de mostrarle contenido de otro rol.
{
    $__script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $__base = basename($__script);
    // Rutas abiertas aun con sesión (cambio/recupero de clave, ver recibo público si aplica)
    $__abiertas = ['login.php', 'recuperar_contrasena.php', 'cambio_contrasena.php'];
    if ($__base !== '' && !in_array($__base, $__abiertas, true) && strpos($__script, '/view/') !== false) {
        $__rol = strtolower(trim($_SESSION['rol'] ?? ''));
        if (!puede_acceder($__base, $__rol)) {
            header("Location: /colsoftco/app/ir_panel.php?denegado=1");
            exit();
        }
    }
}

/**
 * Genera un token CSRF y lo almacena en la sesión.
 * (Reservado para uso futuro en formularios — actualmente sin llamadas).
 */
function generarTokenCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida que el token CSRF recibido coincida con el de la sesión.
 */
function validarTokenCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}