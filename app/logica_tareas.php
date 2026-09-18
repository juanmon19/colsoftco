<?php
/**
 * API de tareas individuales — COLSOFTCO
 * - Cada tarea tiene dueño (id_usuario).
 * - Los usuarios solo ven/crean/gestionan las SUYAS.
 * - Solo el administrador puede asignar tareas a otros usuarios
 *   y ver/gestionar las de todos.
 */

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/permisos.php';
require_once '../config/conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConnection();
$me = (int) ($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
$admin = es_admin();

/* ══ Migración: columna de dueño (id_usuario) ══ */
$hayDueno = true;
try {
    $col = $db->query("SHOW COLUMNS FROM tareas LIKE 'id_usuario'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE tareas ADD COLUMN id_usuario INT NULL, ADD INDEX idx_tareas_usuario (id_usuario)");
    }
} catch (Throwable $e) {
    $hayDueno = false; // BD sin migrar: se sigue en modo global (solo admin debería llegar aquí)
}

$accion = $_REQUEST['accion'] ?? '';

/* ══ Lista tareas: admin ve todas (con asignado), el resto solo las suyas ══ */
if ($accion === 'listar') {
    if ($admin) {
        $stmt = $db->query(
            "SELECT t.id_tarea, t.titulo, t.prioridad, t.fecha_vencimiento, t.estado,
                    t.id_usuario, CONCAT(u.nombre, ' ', u.apellido) AS asignado
             FROM tareas t
             LEFT JOIN usuarios u ON u.id_usuario = t.id_usuario
             ORDER BY FIELD(t.estado, 'pendiente', 'por-hacer', 'terminado'),
                      t.fecha_vencimiento ASC"
        );
        $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = $db->query(
            "SELECT id_usuario, nombre, apellido, rol FROM usuarios
             WHERE activo = 1 ORDER BY nombre ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if ($hayDueno) {
            $stmt = $db->prepare(
                "SELECT id_tarea, titulo, prioridad, fecha_vencimiento, estado, id_usuario
                 FROM tareas WHERE id_usuario = :me
                 ORDER BY FIELD(estado, 'pendiente', 'por-hacer', 'terminado'),
                          fecha_vencimiento ASC"
            );
            $stmt->execute([':me' => $me]);
        } else {
            $stmt = $db->query(
                "SELECT id_tarea, titulo, prioridad, fecha_vencimiento, estado
                 FROM tareas
                 ORDER BY FIELD(estado, 'pendiente', 'por-hacer', 'terminado'),
                          fecha_vencimiento ASC"
            );
        }
        $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = [];
    }
    echo json_encode(['ok' => true, 'tareas' => $tareas, 'es_admin' => $admin, 'usuarios' => $usuarios]);
    exit();
}

/* ══ Usuarios asignables (solo admin, para el modal) ══ */
if ($accion === 'usuarios') {
    if (!$admin) {
        echo json_encode(['ok' => false, 'error' => 'Sin permiso.']);
        exit();
    }
    $usuarios = $db->query(
        "SELECT id_usuario, nombre, apellido, rol FROM usuarios
         WHERE activo = 1 ORDER BY nombre ASC"
    )->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'usuarios' => $usuarios]);
    exit();
}

/* ══ Sugerencias de títulos ya usados antes, para el autocompletado ══ */
if ($accion === 'sugerencias') {
    $stmt = $db->query("SELECT DISTINCT titulo FROM tareas ORDER BY titulo ASC LIMIT 20");
    echo json_encode(['ok' => true, 'sugerencias' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
    exit();
}

/* ══ Registrar una tarea nueva (dueño = yo; admin puede asignarla a otro) ══ */
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

    // Solo el admin elige dueño; el resto siempre crean para sí mismos.
    $dueno = $me;
    if ($admin && isset($_POST['id_usuario']) && (int) $_POST['id_usuario'] > 0) {
        $chk = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = :id AND activo = 1");
        $chk->execute([':id' => (int) $_POST['id_usuario']]);
        if ((int) $chk->fetchColumn() > 0) {
            $dueno = (int) $_POST['id_usuario'];
        }
    }

    if ($hayDueno) {
        $stmt = $db->prepare(
            "INSERT INTO tareas (titulo, prioridad, fecha_vencimiento, estado, id_usuario)
             VALUES (:titulo, :prioridad, :vencimiento, 'pendiente', :dueno)"
        );
        $stmt->execute([
            ':titulo'      => $titulo,
            ':prioridad'   => $prioridad,
            ':vencimiento' => $vencimiento !== '' ? $vencimiento : null,
            ':dueno'       => $dueno,
        ]);
    } else {
        $stmt = $db->prepare(
            "INSERT INTO tareas (titulo, prioridad, fecha_vencimiento, estado)
             VALUES (:titulo, :prioridad, :vencimiento, 'pendiente')"
        );
        $stmt->execute([
            ':titulo'      => $titulo,
            ':prioridad'   => $prioridad,
            ':vencimiento' => $vencimiento !== '' ? $vencimiento : null,
        ]);
    }

    echo json_encode(['ok' => true, 'id_tarea' => $db->lastInsertId()]);
    exit();
}

/* ══ Dueño de una tarea (para validar ownership) ══ */
function duenoDeTarea($db, int $id): ?int {
    try {
        $stmt = $db->prepare("SELECT id_usuario FROM tareas WHERE id_tarea = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null; // no existe
        }
        return isset($row['id_usuario']) ? (int) $row['id_usuario'] : -1; // -1 = sin columna
    } catch (Throwable $e) {
        return -1;
    }
}

/* ══ Actualizar estado / prioridad / reasignar (admin) ══ */
if ($accion === 'actualizar') {
    $id        = (int) ($_POST['id_tarea'] ?? 0);
    $estado    = $_POST['estado'] ?? null;
    $prioridad = $_POST['prioridad'] ?? null;
    $reasignar = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : null;

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no válida.']);
        exit();
    }

    $dueno = duenoDeTarea($db, $id);
    if ($dueno === null) {
        echo json_encode(['ok' => false, 'error' => 'La tarea no existe.']);
        exit();
    }
    // Sin columna migrada no se puede validar dueño: solo admin.
    if (!$admin && ($dueno === -1 || $dueno !== $me)) {
        echo json_encode(['ok' => false, 'error' => 'Solo puedes gestionar tus propias tareas.']);
        exit();
    }

    $campos = [];
    $params = [':id' => $id];

    if ($estado !== null && in_array($estado, ['pendiente', 'por-hacer', 'terminado'], true)) {
        $campos[] = 'estado = :estado';
        $params[':estado'] = $estado;

        // Módulo estadisticas: sella fecha_cierre al terminar (NULL al reabrir).
        // Silencioso si la migración aún no se aplicó.
        try {
            $col = $db->query("SHOW COLUMNS FROM tareas LIKE 'fecha_cierre'")->fetch();
            if ($col) {
                $campos[] = $estado === 'terminado' ? 'fecha_cierre = NOW()' : 'fecha_cierre = NULL';
            }
        } catch (Throwable $e) {
            // sin columna: se actualiza solo el estado
        }
    }

    if ($prioridad !== null && in_array($prioridad, ['low', 'medium', 'high'], true)) {
        $campos[] = 'prioridad = :prioridad';
        $params[':prioridad'] = $prioridad;
    }

    // Reasignar dueño: solo admin (0 = dejar sin asignar).
    if ($reasignar !== null) {
        if (!$admin) {
            echo json_encode(['ok' => false, 'error' => 'Solo el administrador puede reasignar tareas.']);
            exit();
        }
        if ($hayDueno) {
            if ($reasignar === 0) {
                $campos[] = 'id_usuario = NULL';
            } elseif ($reasignar > 0) {
                $chk = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE id_usuario = :id AND activo = 1");
                $chk->execute([':id' => $reasignar]);
                if ((int) $chk->fetchColumn() > 0) {
                    $campos[] = 'id_usuario = :dueno';
                    $params[':dueno'] = $reasignar;
                }
            }
        }
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

/* ══ Eliminar una tarea (dueño o admin) ══ */
if ($accion === 'eliminar') {
    $id = (int) ($_POST['id_tarea'] ?? $_GET['id_tarea'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Tarea no válida.']);
        exit();
    }

    $dueno = duenoDeTarea($db, $id);
    if ($dueno === null) {
        echo json_encode(['ok' => false, 'error' => 'La tarea no existe.']);
        exit();
    }
    if (!$admin && ($dueno === -1 || $dueno !== $me)) {
        echo json_encode(['ok' => false, 'error' => 'Solo puedes eliminar tus propias tareas.']);
        exit();
    }

    $stmt = $db->prepare("DELETE FROM tareas WHERE id_tarea = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['ok' => true]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
