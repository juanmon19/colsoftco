<?php
/**
 * API para gestión de usuarios (solo administrador).
 */
session_start();
header('Content-Type: application/json');
require_once '../config/conexion.php';

if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

// Solo administradores pueden gestionar usuarios
if (($_SESSION['rol'] ?? '') !== 'administrador') {
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado.']);
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConnection();

$accion = $_REQUEST['accion'] ?? '';

/* ══ Listar todos los usuarios ══ */
if ($accion === 'listar') {
    $stmt = $db->query("
        SELECT id_usuario, documento, nombre, apellido, email, telefono, rol, foto, activo,
               ultima_actividad, token_sesion
        FROM usuarios
        ORDER BY FIELD(rol, 'administrador', 'bodeguero', 'operario'), nombre ASC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Determinar si están online (actividad en últimos 5 minutos)
    foreach ($usuarios as &$u) {
        $u['online'] = false;
        if ($u['ultima_actividad'] && $u['token_sesion']) {
            $ultima = strtotime($u['ultima_actividad']);
            $u['online'] = (time() - $ultima) < 300; // 5 minutos
        }
        unset($u['token_sesion']); // No exponer el token
        unset($u['password_hash']);
    }

    echo json_encode(['ok' => true, 'usuarios' => $usuarios]);
    exit();
}

/* ══ Cambiar estado activo/inactivo ══ */
if ($accion === 'cambiar_estado') {
    $id = (int) ($_POST['id_usuario'] ?? 0);
    $activo = (int) ($_POST['activo'] ?? -1);

    if ($id <= 0 || !in_array($activo, [0, 1], true)) {
        echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos.']);
        exit();
    }

    // No permitir desactivarse a sí mismo
    if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
        echo json_encode(['ok' => false, 'error' => 'No puedes desactivar tu propia cuenta.']);
        exit();
    }

    $stmt = $db->prepare("UPDATE usuarios SET activo = :activo WHERE id_usuario = :id");
    $stmt->execute([':activo' => $activo, ':id' => $id]);

    // Si se desactiva, limpiar token para forzar cierre de sesión
    if ($activo === 0) {
        $stmtToken = $db->prepare("UPDATE usuarios SET token_sesion = NULL WHERE id_usuario = :id");
        $stmtToken->execute([':id' => $id]);
    }

    echo json_encode(['ok' => true]);
    exit();
}

/* ══ Obtener usuarios conectados (para polling) ══ */
if ($accion === 'conectados') {
    $stmt = $db->query("
        SELECT id_usuario, nombre, apellido, rol, foto, ultima_actividad
        FROM usuarios
        WHERE token_sesion IS NOT NULL
          AND ultima_actividad >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
          AND activo = 1
        ORDER BY ultima_actividad DESC
    ");
    echo json_encode(['ok' => true, 'conectados' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
