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