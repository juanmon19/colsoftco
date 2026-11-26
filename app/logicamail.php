<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set('America/Bogota');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/permisos.php';

foreach ([
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../config/setting.php',
    __DIR__ . '/correo_plantilla.php',
] as $__depMail) {
    if (file_exists($__depMail)) {
        require_once $__depMail;
    }
}

// --- BLOQUE 1: SOLICITUD DE RESETEO ---
if (isset($_POST['send'])):
    if (!empty($_POST['email'])) {
        $Usuario = ConsultaUsuarioPorEmail($_POST['email']); 
        
        if (count($Usuario) > 0) {
            $token_ = bin2hex(random_bytes(32));

            // Se corrigió el punto y coma y ahora updateUser retorna true/false
            if (updateUser($token_, function_exists('tiempo_expiracion_token') ? tiempo_expiracion_token() : (time() + 1800), $Usuario[0]->id_usuario)) {
                if (!EnviarCorreoResetPassword($Usuario[0]->email, $Usuario[0]->nombre, $Usuario[0]->id_usuario, $token_)) {
                    $_SESSION['response'] = 'No se pudo enviar el correo. Contacta al administrador para revisar la configuración de correo.';
                    header('Location: ../view/recuperar_contrasena/recuperar_contrasena.php?message=error');
                    exit();
                }
                $_SESSION['response'] = 'Hemos enviado un correo con las instrucciones.';
                header("Location: ../view/login/login.php?message=ok");
                exit();
            } else {
                $_SESSION['response'] = 'Error interno al generar el token.';
                header("Location: ../view/recuperar_contrasena/recuperar_contrasena.php?message=error");
                exit();
            }
        } else {
            $_SESSION['response'] = 'No existe usuario';
            header("Location: ../view/recuperar_contrasena/recuperar_contrasena.php?message=no_found");
            exit();
        }
    } else {
        $_SESSION['response'] = 'Email incorrecto';
        header("Location: ../view/recuperar_contrasena/recuperar_contrasena.php?message=error");
        exit();
    }
endif;


// --- BLOQUE 2: GUARDAR NUEVA CONTRASEÑA ---
if (isset($_POST['save'])):

    if (!empty($_POST['id']) && !empty($_POST['password'])) {

        $Password = $_POST['password'];
        $ConfirmarPassword = $_POST['new_password'] ?? '';
        $id = $_POST['id'];
        $token = $_POST['token'] ?? '';

        // Verificar que el token siga siendo válido
        $Usuario = verificarToken($id, $token);

        if (!$Usuario) {
            $_SESSION['error'] = 'El enlace de recuperación ha expirado o no es válido.';
            header("Location: ../view/login/login.php");
    exit();
}

        // Verificar que las contraseñas coincidan
        if ($Password !== $ConfirmarPassword) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        // Validar requisitos de la contraseña
        if (strlen($Password) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener mínimo 8 caracteres.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[A-Z]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos una letra mayúscula.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[a-z]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos una letra minúscula.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[0-9]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos un número.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[\W_]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos un carácter especial.';
            header("Location: ../view/cambiocontrasena/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        $new_password = password_hash($Password, PASSWORD_BCRYPT);

        $Usuario = ConsultaUsuarioPorId($id);

        if (count($Usuario) > 0) {

            updateUserID($new_password, $id);

            // Invalidar el token después de usarlo
            invalidarToken($id);

            $_SESSION['response'] = 'Contraseña actualizada con éxito.';
            header("Location: ../view/login/login.php?message=success_password");
            exit();

        } else {

            $_SESSION['error'] = 'No existe el usuario';

        }

    } else {

        $_SESSION['error'] = 'Datos inválidos';

    }

    header("Location: ../view/recuperar_contrasena/recuperar_contrasena.php?message=error");
    exit();

endif;


// --- FUNCIONES ---

function ConsultaUsuarioPorEmail($email) {
    $conex = new Conexion;
    $conex->sql = "SELECT * FROM usuarios WHERE email = :email";
    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":email", $email);
        $conex->pps->execute();
        return $conex->pps->fetchAll(PDO::FETCH_OBJ);
    } catch (\Throwable $th) {
        // En producción, usa un archivo de log en vez de un echo
        error_log($th->getMessage());
        return [];
    } finally {
        $conex->closeDataBase();
    }
}

function ConsultaUsuarioPorId($id) {
    $conex = new Conexion;
    $conex->sql = "SELECT * FROM usuarios WHERE id_usuario = :id";
    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":id", $id);
        $conex->pps->execute();
        return $conex->pps->fetchAll(PDO::FETCH_OBJ);
    } catch (\Throwable $th) {
        error_log($th->getMessage());
        return [];
    } finally {
        $conex->closeDataBase();
    }
}

function updateUser($token, $tiempo_vida, $user_id) {
    $conex = new Conexion();
    $Valor = "1";
    $conex->sql = "UPDATE usuarios SET request_password = :request_password, token_password = :token_password, expired_session = :expired_session WHERE id_usuario = :id_usuario";

    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":request_password", $Valor);
        $conex->pps->bindParam(":token_password", $token);
        $conex->pps->bindParam(":expired_session", $tiempo_vida);
        $conex->pps->bindParam(":id_usuario", $user_id);
        
        return $conex->pps->execute(); // Retorna true si fue exitoso
    } catch (\Throwable $th) {
        error_log($th->getMessage());
        return false;
    } finally {
        if (method_exists($conex, 'closeDataBase')) {
            $conex->closeDataBase();
        }
    }
}

function updateUserID($new_password, $user_id) {
    $conex = new Conexion();
    $conex->sql = "UPDATE usuarios SET password_hash = :password WHERE id_usuario = :id_usuario";
    try {
        $conex->pps = $conex->getConnection()->prepare($conex->sql);
        $conex->pps->bindParam(":password", $new_password);
        $conex->pps->bindParam(":id_usuario", $user_id);
        $conex->pps->execute();
    } catch (\Throwable $th) {
        error_log($th->getMessage());
    } finally {
        if (method_exists($conex, 'closeDataBase')) {
            $conex->closeDataBase();
        }
    }
}

function EnviarCorreoResetPassword($Correo, $NombreReceptor, $userid, $token_User): bool {
    if (
        !class_exists(PHPMailer::class) ||
        !function_exists('prepararCorreo') ||
        !defined('HOST') ||
        !defined('USERNAME') ||
        !defined('PASSWORD') ||
        USERNAME === '' ||
        PASSWORD === ''
    ) {
        error_log('COLSOFTCO: falta PHPMailer, plantilla o credenciales SMTP.');
        return false;
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

        $mail->setFrom(USERNAME, 'Colsoftco');
        $mail->addAddress($Correo, $NombreReceptor);

        if (defined('BASE_URL') && BASE_URL !== '') {
            $base = rtrim(BASE_URL, '/');
        } else {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $base = $scheme . '://' . $host . colsoftco_base_path();
        }

        $url = $base . '/view/cambiocontrasena/cambio_contrasena.php?' . http_build_query([
            'id' => $userid, 'token' => $token_User,
        ]);
        prepararCorreo($mail, 'Restablece tu contraseña de COLSOFTCO', "Hola, {$NombreReceptor}.",
            'Recibimos una solicitud para cambiar la contraseña de tu cuenta.',
            nota: 'Si tú solicitaste el cambio, usa el botón antes de que venza el enlace. Si no lo solicitaste, puedes ignorar este correo.',
            boton: 'Crear una nueva contraseña', url: $url);

        return $mail->send();
    } catch (\Throwable $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}

function verificarToken($id, $token)
{
    $conex = new Conexion();

    $sql = "SELECT * FROM usuarios
            WHERE id_usuario = :id
            AND token_password = :token
            AND expired_session > :tiempo";

    try {
        $pps = $conex->getConnection()->prepare($sql);

        $tiempo = time();

        $pps->bindParam(':id', $id);
        $pps->bindParam(':token', $token);
        $pps->bindParam(':tiempo', $tiempo);

        $pps->execute();

        return $pps->fetch(PDO::FETCH_OBJ);

    } catch (\Throwable $th) {

        error_log($th->getMessage());
        return false;

    } finally {

        $conex->closeDataBase();
    }
}

function invalidarToken($id)
{
    $conex = new Conexion();

    $sql = "UPDATE usuarios
            SET token_password = NULL,
                expired_session = 0,
                request_password = 0
            WHERE id_usuario = :id";

    try {

        $pps = $conex->getConnection()->prepare($sql);

        $pps->bindParam(':id', $id);

        return $pps->execute();

    } catch (\Throwable $th) {

        error_log($th->getMessage());
        return false;

    } finally {

        $conex->closeDataBase();
    }
}