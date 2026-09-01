<?php
// Inicialización de la sesión.
// 51 se utiliza otro nombre
// session_name("otroNombre")
session_start();

// ══ LIMPIAR TOKEN DE SESIÓN ÚNICA ══
// Se hace antes de destruir la sesión para que el próximo login
// normal (sin nadie más conectado) no dispare una alerta falsa.
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/conexion.php';

    try {
        $conex = new Conexion();
        $conex->sql = "UPDATE usuarios SET token_sesion = NULL WHERE id_usuario = :id";
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":id", $_SESSION['user_id']);
        $conex->pps->execute();
        $conex->closeDataBase();
    } catch (\Throwable $th) {
        error_log('Error al limpiar token_sesion en logout: ' . $th->getMessage());
    }
}

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