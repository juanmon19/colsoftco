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

// URL única de login (se adapta automáticamente a XAMPP /colsoftco y a InfinityFree /)
if (!function_exists('colsoftco_login_url')) {
    function colsoftco_login_url(): string {
        return colsoftco_base_path() . '/view/login/login.php';
    }
}

// Verificar si el usuario inició sesión
if (!isset($_SESSION['documento'])) {
    header("Location: " . colsoftco_login_url());
    exit();
}

// ══ CONTROL DE SESIÓN ÚNICA ══
$__dbCheck = null;
$__connCheck = null;
try {
    require_once __DIR__ . '/../config/conexion.php';
    $__dbCheck = new Conexion();
    $__connCheck = $__dbCheck->getConnection();
} catch (\Throwable $__e) {
    error_log('Error de conexión en verificar_sesion.php: ' . $__e->getMessage());
}

if ($__connCheck && isset($_SESSION['token_sesion'])) {
    try {
        $__stmtCheck = $__connCheck->prepare(
            "SELECT token_sesion FROM usuarios WHERE documento = :doc LIMIT 1"
        );
        $__stmtCheck->execute([':doc' => $_SESSION['documento']]);
        $__rowCheck = $__stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$__rowCheck || $__rowCheck['token_sesion'] !== $_SESSION['token_sesion']) {
            session_unset();
            session_destroy();
            header("Location: " . colsoftco_login_url() . "?sesion=reemplazada", true, 303);
            exit();
        }
    } catch (\Throwable $__e) {
        error_log('Error validando token_sesion en verificar_sesion.php: ' . $__e->getMessage());
    }
}

// ══ VERIFICAR CUENTA ACTIVA Y EXPIRACIÓN POR INACTIVIDAD ══
if (!defined('MINUTOS_INACTIVIDAD_MAX')) {
    define('MINUTOS_INACTIVIDAD_MAX', 30);
}

if ($__connCheck) {
    try {
        $__stmtActivo = $__connCheck->prepare(
            "SELECT id_usuario, nombre, apellido, email, rol, activo, ultima_actividad FROM usuarios WHERE documento = :doc LIMIT 1"
        );
        $__stmtActivo->execute([':doc' => $_SESSION['documento']]);
        $__rowActivo = $__stmtActivo->fetch(PDO::FETCH_ASSOC);

        // Refresca la sesión desde la BD para que cambios de nombre/rol apliquen sin re-login
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
            header("Location: " . colsoftco_login_url() . "?sesion=desactivada", true, 303);
            exit();
        }

        // Si la última actividad registrada supera el umbral, se cierra la sesión.
        if (!empty($__rowActivo['ultima_actividad'])) {
            $__tsActividad = strtotime($__rowActivo['ultima_actividad']);
            if ($__tsActividad !== false) {
                $__minutosInactivo = (time() - $__tsActividad) / 60;
                if ($__minutosInactivo > MINUTOS_INACTIVIDAD_MAX) {
                    session_unset();
                    session_destroy();
                    header("Location: " . colsoftco_login_url() . "?sesion=inactiva", true, 303);
                    exit();
                }
            }
        }

        // Actualizar última actividad en hora de Bogotá (evita desfases con NOW() de MySQL en InfinityFree)
        $__ahoraBogota = date('Y-m-d H:i:s');
        $__stmtAct = $__connCheck->prepare(
            "UPDATE usuarios SET ultima_actividad = :ahora WHERE documento = :doc"
        );
        $__stmtAct->execute([
            ':ahora' => $__ahoraBogota,
            ':doc'   => $_SESSION['documento'],
        ]);
    } catch (\Throwable $__e) {
        error_log('Error verificando actividad en verificar_sesion.php: ' . $__e->getMessage());
    } finally {
        if ($__dbCheck && method_exists($__dbCheck, 'closeDataBase')) {
            $__dbCheck->closeDataBase();
        }
    }
}

// ══ GUARDIA AUTOMÁTICA DE ROLES ══
{
    $__script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $__base = basename($__script);
    $__abiertas = ['login.php', 'recuperar_contrasena.php', 'cambio_contrasena.php'];
    if ($__base !== '' && !in_array($__base, $__abiertas, true) && strpos($__script, '/view/') !== false) {
        $__rol = strtolower(trim($_SESSION['rol'] ?? ''));
        if (!puede_acceder($__base, $__rol)) {
            header("Location: " . colsoftco_base_path() . "/app/ir_panel.php?denegado=1");
            exit();
        }
    }
}
