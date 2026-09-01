<?php

// Configuración de seguridad de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    session_start();
}

// Verificar si el usuario inició sesión
if (!isset($_SESSION['documento'])) {
    header("Location: ../login/login.php");
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
            window.location.href = "' . (strpos($_SERVER['SCRIPT_NAME'], '/app/') !== false ? '../view/login/login.php' : '../login/login.php') . '";
        </script>';
        exit();
    }
}

// ══ VERIFICAR CUENTA ACTIVA ══
if (isset($_SESSION['documento'])) {
    if (!isset($__dbCheck)) {
        require_once __DIR__ . '/../config/conexion.php';
        $__dbCheck = new Conexion();
    }
    $__stmtActivo = $__dbCheck->getConnection()->prepare(
        "SELECT activo FROM usuarios WHERE documento = :doc LIMIT 1"
    );
    $__stmtActivo->execute([':doc' => $_SESSION['documento']]);
    $__rowActivo = $__stmtActivo->fetch(PDO::FETCH_ASSOC);

    if (!$__rowActivo || (int) $__rowActivo['activo'] !== 1) {
        session_unset();
        session_destroy();
        echo '<script>
            alert("Tu cuenta ha sido desactivada por el administrador. Contacta al administrador.");
            window.location.href = "' . (strpos($_SERVER['SCRIPT_NAME'], '/app/') !== false ? '../view/login/login.php' : '../login/login.php') . '";
        </script>';
        exit();
    }

    // Actualizar última actividad
    $__stmtAct = $__dbCheck->getConnection()->prepare(
        "UPDATE usuarios SET ultima_actividad = NOW() WHERE documento = :doc"
    );
    $__stmtAct->execute([':doc' => $_SESSION['documento']]);
}

/**
 * Genera un token CSRF y lo almacena en la sesión.
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