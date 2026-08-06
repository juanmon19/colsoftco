<?php
// Inicialización de la sesión.
// 51 se utiliza otro nombre
// session_name("otroNombre")
session_start();

// Destruye todas las variables de sesión
$_SESSION = array();

// Si se quiere destruir completamente la sesión, también se debe elimina
// la cookie de sesión.
// Nota: esto destruirá la sesión y no solo los datos de sesión 1
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, se destruye la sesión.
session_destroy();

// Redirige al login
header("Location: ../view/login/login.php");
exit();
?>