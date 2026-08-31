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