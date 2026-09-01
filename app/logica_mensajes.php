<?php
/**
 * API para mensajería interna.
 */
session_start();
header('Content-Type: application/json');
require_once '../config/conexion.php';

if (!isset($_SESSION['documento']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConnection();
$userId = (int) $_SESSION['user_id'];

$accion = $_REQUEST['accion'] ?? '';

/* ══ Listar usuarios disponibles (para el select de destinatario) ══ */
if ($accion === 'listar_usuarios') {
    $stmt = $db->prepare("
        SELECT id_usuario, nombre, apellido, rol
        FROM usuarios
        WHERE id_usuario != :id AND activo = 1
        ORDER BY nombre ASC
    ");
    $stmt->execute([':id' => $userId]);
    echo json_encode(['ok' => true, 'usuarios' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* ══ Bandeja de entrada ══ */
if ($accion === 'bandeja') {
    $stmt = $db->prepare("
        SELECT m.id_mensaje, m.asunto, m.contenido, m.leido, m.fecha_envio,
               u.nombre AS remitente_nombre, u.apellido AS remitente_apellido, u.rol AS remitente_rol
        FROM mensajes m
        INNER JOIN usuarios u ON u.id_usuario = m.id_remitente
        WHERE m.id_destinatario = :id
        ORDER BY m.fecha_envio DESC
        LIMIT 50
    ");
    $stmt->execute([':id' => $userId]);
    echo json_encode(['ok' => true, 'mensajes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* ══ Mensajes enviados ══ */
if ($accion === 'enviados') {
    $stmt = $db->prepare("
        SELECT m.id_mensaje, m.asunto, m.contenido, m.fecha_envio,
               u.nombre AS destinatario_nombre, u.apellido AS destinatario_apellido, u.rol AS destinatario_rol
        FROM mensajes m
        INNER JOIN usuarios u ON u.id_usuario = m.id_destinatario
        WHERE m.id_remitente = :id
        ORDER BY m.fecha_envio DESC
        LIMIT 50
    ");
    $stmt->execute([':id' => $userId]);
    echo json_encode(['ok' => true, 'mensajes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* ══ Enviar mensaje ══ */
if ($accion === 'enviar') {
    $destinatario = (int) ($_POST['id_destinatario'] ?? 0);
    $asunto = trim($_POST['asunto'] ?? '');
    $contenido = trim($_POST['contenido'] ?? '');

    if ($destinatario <= 0 || $asunto === '' || $contenido === '') {
        echo json_encode(['ok' => false, 'error' => 'Todos los campos son obligatorios.']);
        exit();
    }

    if ($destinatario === $userId) {
        echo json_encode(['ok' => false, 'error' => 'No puedes enviarte un mensaje a ti mismo.']);
        exit();
    }

    $stmt = $db->prepare("
        INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido)
        VALUES (:rem, :dest, :asunto, :contenido)
    ");
    $stmt->execute([
        ':rem' => $userId,
        ':dest' => $destinatario,
        ':asunto' => $asunto,
        ':contenido' => $contenido,
    ]);

    echo json_encode(['ok' => true, 'mensaje' => 'Mensaje enviado exitosamente.']);
    exit();
}

/* ══ Marcar como leído ══ */
if ($accion === 'leer') {
    $idMensaje = (int) ($_POST['id_mensaje'] ?? $_GET['id_mensaje'] ?? 0);

    if ($idMensaje <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
        exit();
    }

    $stmt = $db->prepare("
        UPDATE mensajes SET leido = 1
        WHERE id_mensaje = :id AND id_destinatario = :userId
    ");
    $stmt->execute([':id' => $idMensaje, ':userId' => $userId]);

    echo json_encode(['ok' => true]);
    exit();
}

/* ══ Contar no leídos (para badge) ══ */
if ($accion === 'no_leidos') {
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM mensajes
        WHERE id_destinatario = :id AND leido = 0
    ");
    $stmt->execute([':id' => $userId]);
    echo json_encode(['ok' => true, 'count' => (int) $stmt->fetchColumn()]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
