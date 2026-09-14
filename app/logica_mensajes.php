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
    $destinatario = (int) ($_POST['id_destinatario'] ?? $_POST['otro'] ?? 0);
    $asunto = trim($_POST['asunto'] ?? 'Chat');
    $contenido = trim($_POST['contenido'] ?? '');
    $idPadre = (int) ($_POST['id_padre'] ?? 0) ?: null;

    if ($destinatario <= 0 || $contenido === '') {
        echo json_encode(['ok' => false, 'error' => 'Destinatario y contenido son obligatorios.']);
        exit();
    }

    if ($destinatario === $userId) {
        echo json_encode(['ok' => false, 'error' => 'No puedes enviarte un mensaje a ti mismo.']);
        exit();
    }

    // Validar que el padre pertenezca a la misma conversación 1-1
    // (placeholders únicos: Conexion usa EMULATE_PREPARES=false y no permite reutilizar :yo/:ot)
    if ($idPadre) {
        $chk = $db->prepare("SELECT COUNT(*) FROM mensajes WHERE id_mensaje = :p
            AND ((id_remitente = :yo1 AND id_destinatario = :ot1) OR (id_remitente = :ot2 AND id_destinatario = :yo2))");
        $chk->execute([':p' => $idPadre, ':yo1' => $userId, ':ot1' => $destinatario, ':ot2' => $destinatario, ':yo2' => $userId]);
        if (!(int) $chk->fetchColumn()) {
            echo json_encode(['ok' => false, 'error' => 'El mensaje citado no pertenece a esta conversación.']);
            exit();
        }
    }

    $conv = min($userId, $destinatario) . '-' . max($userId, $destinatario);
    try {
        $stmt = $db->prepare("
            INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido, id_padre, id_conversacion)
            VALUES (:rem, :dest, :asunto, :contenido, :padre, :conv)
        ");
        $stmt->execute([
            ':rem' => $userId, ':dest' => $destinatario, ':asunto' => $asunto,
            ':contenido' => $contenido, ':padre' => $idPadre, ':conv' => $conv,
        ]);
    } catch (PDOException $e) {
        // Compatibilidad: BD sin migrar (sin id_padre/id_conversacion)
        $stmt = $db->prepare("
            INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido)
            VALUES (:rem, :dest, :asunto, :contenido)
        ");
        $stmt->execute([':rem' => $userId, ':dest' => $destinatario, ':asunto' => $asunto, ':contenido' => $contenido]);
    }

    echo json_encode(['ok' => true, 'mensaje' => 'Mensaje enviado exitosamente.']);
    exit();
}

/* ══ Hilo 1-1 ordenado (chat directo) ══ */
if ($accion === 'hilo') {
    $otro = (int) ($_GET['otro'] ?? $_POST['otro'] ?? 0);
    if ($otro <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Conversación inválida.']);
        exit();
    }
    try {
        $stmt = $db->prepare("
            SELECT m.id_mensaje, m.id_remitente, m.id_destinatario, m.asunto, m.contenido,
                   m.leido, m.fecha_envio, m.id_padre,
                   u.nombre, u.apellido, p.contenido AS padre_contenido
            FROM mensajes m
            INNER JOIN usuarios u ON u.id_usuario = m.id_remitente
            LEFT JOIN mensajes p ON p.id_mensaje = m.id_padre
            WHERE (m.id_remitente = :yo1 AND m.id_destinatario = :ot1)
               OR (m.id_remitente = :ot2 AND m.id_destinatario = :yo2)
            ORDER BY m.fecha_envio ASC LIMIT 200
        ");
        $stmt->execute([':yo1' => $userId, ':ot1' => $otro, ':ot2' => $otro, ':yo2' => $userId]);
    } catch (PDOException $e) {
        $stmt = $db->prepare("
            SELECT m.id_mensaje, m.id_remitente, m.id_destinatario, m.asunto, m.contenido,
                   m.leido, m.fecha_envio, NULL AS id_padre,
                   u.nombre, u.apellido, NULL AS padre_contenido
            FROM mensajes m
            INNER JOIN usuarios u ON u.id_usuario = m.id_remitente
            WHERE (m.id_remitente = :yo1 AND m.id_destinatario = :ot1)
               OR (m.id_remitente = :ot2 AND m.id_destinatario = :yo2)
            ORDER BY m.fecha_envio ASC LIMIT 200
        ");
        $stmt->execute([':yo1' => $userId, ':ot1' => $otro, ':ot2' => $otro, ':yo2' => $userId]);
    }
    echo json_encode(['ok' => true, 'mensajes' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* Alias: responder = enviar con id_padre */
if ($accion === 'responder') {
    $_POST['id_destinatario'] = $_POST['otro'] ?? $_POST['id_destinatario'] ?? 0;
    $accion = 'enviar';
    // re-despacha al bloque enviar re-ejecutando este archivo de forma simple:
    // (duplicamos validación mínima para no reescribir el flujo)
    $destinatario = (int) ($_POST['id_destinatario'] ?? 0);
    $contenido = trim($_POST['contenido'] ?? '');
    $idPadre = (int) ($_POST['id_padre'] ?? 0) ?: null;
    if ($destinatario <= 0 || $contenido === '' || !$idPadre) {
        echo json_encode(['ok' => false, 'error' => 'Faltan datos para responder.']);
        exit();
    }
    $_POST['asunto'] = $_POST['asunto'] ?? 'RE: chat';
    // cae al bloque enviar de arriba en la próxima petición; aquí insertamos directo:
    $chk = $db->prepare("SELECT COUNT(*) FROM mensajes WHERE id_mensaje = :p
        AND ((id_remitente = :yo1 AND id_destinatario = :ot1) OR (id_remitente = :ot2 AND id_destinatario = :yo2))");
    $chk->execute([':p' => $idPadre, ':yo1' => $userId, ':ot1' => $destinatario, ':ot2' => $destinatario, ':yo2' => $userId]);
    if (!(int) $chk->fetchColumn()) {
        echo json_encode(['ok' => false, 'error' => 'El mensaje citado no pertenece a esta conversación.']);
        exit();
    }
    $conv = min($userId, $destinatario) . '-' . max($userId, $destinatario);
    try {
        $stmt = $db->prepare("INSERT INTO mensajes (id_remitente,id_destinatario,asunto,contenido,id_padre,id_conversacion)
            VALUES (:yo,:ot,:as,:tx,:p,:c)");
        $stmt->execute([':yo'=>$userId,':ot'=>$destinatario,':as'=>trim($_POST['asunto']),':tx'=>$contenido,':p'=>$idPadre,':c'=>$conv]);
    } catch (PDOException $e) {
        $stmt = $db->prepare("INSERT INTO mensajes (id_remitente,id_destinatario,asunto,contenido) VALUES (:yo,:ot,:as,:tx)");
        $stmt->execute([':yo'=>$userId,':ot'=>$destinatario,':as'=>trim($_POST['asunto']),':tx'=>$contenido]);
    }
    echo json_encode(['ok' => true, 'mensaje' => 'Respuesta enviada.']);
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
