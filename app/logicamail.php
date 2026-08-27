<?php

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require '../config/setting.php';
require '../config/conexion.php';

// --- BLOQUE 1: SOLICITUD DE RESETEO ---
if (isset($_POST['send'])):
    if (!empty($_POST['email'])) {
        $Usuario = ConsultaUsuarioPorEmail($_POST['email']); 
        
        if (count($Usuario) > 0) {
            $token_ = bin2hex(random_bytes(32));

            // Se corrigió el punto y coma y ahora updateUser retorna true/false
            if (updateUser($token_, TIEMPO_VIDA, $Usuario[0]->id_usuario)) {
                EnviarCorreoResetPassword($Usuario[0]->email, $Usuario[0]->usuario, $Usuario[0]->id_usuario, $token_);
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
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        // Validar requisitos de la contraseña
        if (strlen($Password) < 8) {
            $_SESSION['error'] = 'La contraseña debe tener mínimo 8 caracteres.';
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[A-Z]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos una letra mayúscula.';
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[a-z]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos una letra minúscula.';
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[0-9]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos un número.';
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
            exit();
        }

        if (!preg_match('/[\W_]/', $Password)) {
            $_SESSION['error'] = 'La contraseña debe tener al menos un carácter especial.';
            header("Location: ../view/cambiocontraseña/cambio_contrasena.php?id=" . $id . "&token=" . urlencode($token));
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
    }
}

function EnviarCorreoResetPassword($Correo, $NombreReceptor, $userid, $token_User) {
    $mail = new PHPMailer(true);
    try {
        // Para producción, cambia a SMTP::DEBUG_OFF para no romper la experiencia de usuario
        $mail->SMTPDebug = SMTP::DEBUG_OFF; 
        $mail->isSMTP();
        $mail->Host       = HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('colsoftco4@gmail.com', 'Colsoftco');
        $mail->addAddress($Correo, $NombreReceptor);

        $mail->isHTML(true);
        $mail->Subject = 'Reseteo de password';
        
        // Uso de & en lugar de && para los parámetros URL estándar
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost/colsoftco';
        $mail->Body    = 'Usted ha solicitado un cambio de contraseña. <br><br> <b><a href="' . $baseUrl . '/view/cambiocontraseña/cambio_contrasena.php?id=' . urlencode($userid) . '&token=' . urlencode($token_User) . '">Cambiar Contraseña</a></b>';

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
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