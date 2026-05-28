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
                header("Location: ../view/login/login.html?message=ok");
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
    // ¡ATENCIÓN! Aquí deberías validar también el $_POST['token'] antes de cambiar nada
    if (!empty($_POST['id']) && !empty($_POST['password'])) {
        $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $id = $_POST['id'];
        
        $Usuario = ConsultaUsuarioPorId($id); 
        if (count($Usuario) > 0) {
            updateUserID($new_password, $id);
            $_SESSION['response'] = 'Contraseña actualizada con éxito.';
            header("Location: ../view/login/login.html?message=success_password");
            exit();
        } else {
            $_SESSION['response'] = 'No existe el usuario';
        }
    } else {
        $_SESSION['response'] = 'Datos inválidos';
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
        $mail->Body    = 'Usted ha solicitado un cambio de contraseña. <br><br> <b><a href="http://localhost/colsoftco/view/cambiocontraseña/cambio_contrasena.php?id='.$userid.'&token='.$token_User.'">Cambiar Contraseña</a></b>';

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
    }
}