<?php

header('Content-Type: application/json');
require_once "../../app/verificar_sesion.php";
require_once "../../config/conexion.php";

$conexion = new Conexion();
$db = $conexion->getConnection();


$idUsuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;

if (!$idUsuario) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$accion = $_REQUEST['accion'] ?? '';

/* Carpeta donde se guardan las fotos de perfil */
$carpetaFotos = __DIR__ . '/../public/imagenes/perfiles/';
if (!is_dir($carpetaFotos)) {
    mkdir($carpetaFotos, 0755, true);
}

/* ══ Obtener los datos actuales del usuario logueado ══ */
if ($accion === 'obtener') {
    $stmt = $db->prepare(
        "SELECT id_usuario, email, documento, nombre, apellido, rol, foto, telefono
         FROM usuarios WHERE id_usuario = :id"
    );
    $stmt->execute([':id' => $idUsuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado.']);
        exit();
    }

    echo json_encode(['ok' => true, 'usuario' => $usuario]);
    exit();
}

/* ══ Actualizar nombre, apellido, email y/o teléfono ══ */
if ($accion === 'actualizar') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $apellido  = trim($_POST['apellido'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');

    if ($nombre === '' || $apellido === '' || $email === '') {
        echo json_encode(['ok' => false, 'error' => 'Nombre, apellido y correo son obligatorios.']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'El correo electrónico no es válido.']);
        exit();
    }

    /* Evita duplicar el correo con otro usuario */
    $stmtCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id");
    $stmtCheck->execute([':email' => $email, ':id' => $idUsuario]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['ok' => false, 'error' => 'Ese correo ya está en uso por otro usuario.']);
        exit();
    }

    $stmt = $db->prepare(
        "UPDATE usuarios
         SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono
         WHERE id_usuario = :id"
    );
    $stmt->execute([
        ':nombre'   => $nombre,
        ':apellido' => $apellido,
        ':email'    => $email,
        ':telefono' => $telefono !== '' ? $telefono : null,
        ':id'       => $idUsuario,
    ]);

    /* Si tu verificar_sesion.php guarda el nombre en sesión, lo refrescamos */
    $_SESSION['nombre']   = $nombre;
    $_SESSION['apellido'] = $apellido;

    echo json_encode(['ok' => true]);
    exit();
}

/* ══ Subir / cambiar la foto de perfil ══ */
if ($accion === 'foto') {
    if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['ok' => false, 'error' => 'No se recibió ninguna imagen.']);
        exit();
    }

    $archivo = $_FILES['foto'];
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tipo = mime_content_type($archivo['tmp_name']);

    if (!isset($permitidos[$tipo])) {
        echo json_encode(['ok' => false, 'error' => 'Formato no permitido. Usa JPG, PNG o WEBP.']);
        exit();
    }

    if ($archivo['size'] > 3 * 1024 * 1024) {
        echo json_encode(['ok' => false, 'error' => 'La imagen no puede pesar más de 3MB.']);
        exit();
    }

    $extension  = $permitidos[$tipo];
    $nombreArchivo = 'usuario_' . $idUsuario . '_' . time() . '.' . $extension;
    $rutaDestino   = $carpetaFotos . $nombreArchivo;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        echo json_encode(['ok' => false, 'error' => 'No se pudo guardar la imagen en el servidor.']);
        exit();
    }

    /* Borra la foto anterior si existía, para no acumular archivos */
    $stmtAnterior = $db->prepare("SELECT foto FROM usuarios WHERE id_usuario = :id");
    $stmtAnterior->execute([':id' => $idUsuario]);
    $fotoAnterior = $stmtAnterior->fetchColumn();
    if ($fotoAnterior && is_file($carpetaFotos . $fotoAnterior)) {
        @unlink($carpetaFotos . $fotoAnterior);
    }

    $stmt = $db->prepare("UPDATE usuarios SET foto = :foto WHERE id_usuario = :id");
    $stmt->execute([':foto' => $nombreArchivo, ':id' => $idUsuario]);

    echo json_encode([
        'ok'   => true,
        'foto' => $nombreArchivo,
        'url'  => '../../public/imagenes/perfiles/' . $nombreArchivo,
    ]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
