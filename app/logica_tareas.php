<?php

header('Content-Type: application/json');
session_start();
require_once '../config/conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConnection();

$accion = $_REQUEST['accion'] ?? '';

/* ══ Lista todas las tareas, pendientes primero ══ */
if ($accion === 'listar') {
    $stmt = $db->query(
        "SELECT id_tarea, titulo, prioridad, fecha_vencimiento, estado
         FROM tareas
         ORDER BY FIELD(estado, 'pendiente', 'por-hacer', 'terminado'),
                  fecha_vencimiento ASC"
    );
    echo json_encode(['ok' => true, 'tareas' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* ══ Sugerencias de títulos ya usados antes, para el autocompletado ══ */
if ($accion === 'sugerencias') {
    $stmt = $db->query("SELECT DISTINCT titulo FROM tareas ORDER BY titulo ASC LIMIT 20");
    echo json_encode(['ok' => true, 'sugerencias' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    exit();
}

/* ══ Registrar una tarea nueva ══ */
if ($accion === 'crear') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $prioridad   = $_POST['prioridad'] ?? 'medium';
    $vencimiento = $_POST['fecha_vencimiento'] ?? '';

    if ($titulo === '') {
        echo json_encode(['ok' => false, 'error' => 'El título de la tarea es obligatorio.']);
        exit();
    }

    if (!in_array($prioridad, ['low', 'medium', 'high'], true)) {
        $prioridad = 'medium';
    }

    // Validar que la fecha no sea pasada
    if ($vencimiento !== '' && $vencimiento < date('Y-m-d')) {
        echo json_encode(['ok' => false, 'error' => 'No se puede asignar una fecha de vencimiento en el pasado.']);
        exit();
    }

    $stmt = $db->prepare(
        "INSERT INTO tareas (titulo, prioridad, fecha_vencimiento, estado)
         VALUES (:titulo, :prioridad, :vencimiento, 'pendiente')"
    );
    $stmt->execute([
        ':titulo'      => $titulo,
        ':prioridad'   => $prioridad,
        ':vencimiento' => $vencimiento !== '' ? $vencimiento : null,
    ]);

    echo json_encode(['ok' => true, 'id_tarea' => $db->lastInsertId()]);
    exit();
}

/* ══ Actualizar estado y/o prioridad de una tarea ══ */
if ($accion === 'actualizar') {
    $id        = (int) ($_POST['id_tarea'] ?? 0);
    $estado    = $_POST['estado'] ?? null;
    $prioridad = $_POST['prioridad'] ?? null;

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no válida.']);
        exit();
    }

    $campos = [];
    $params = [':id' => $id];

    if ($estado !== null && in_array($estado, ['pendiente', 'por-hacer', 'terminado'], true)) {
        $campos[] = 'estado = :estado';
        $params[':estado'] = $estado;
    }

    if ($prioridad !== null && in_array($prioridad, ['low', 'medium', 'high'], true)) {
        $campos[] = 'prioridad = :prioridad';
        $params[':prioridad'] = $prioridad;
    }

    if (!$campos) {
        echo json_encode(['ok' => false, 'error' => 'No hay cambios para guardar.']);
        exit();
    }

    $sql = "UPDATE tareas SET " . implode(', ', $campos) . " WHERE id_tarea = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['ok' => true]);
    exit();
}

/* ══ Eliminar una tarea ══ */
if ($accion === 'eliminar') {
    $id = (int) ($_POST['id_tarea'] ?? $_GET['id_tarea'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no válida.']);
        exit();
    }

    $stmt = $db->prepare("DELETE FROM tareas WHERE id_tarea = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['ok' => true]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
