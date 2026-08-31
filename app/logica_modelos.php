<?php
/**
 * API para gestión de modelos de colchón y sus recetas.
 * Todas las respuestas son JSON.
 */
require_once 'verificar_sesion.php';
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json');
require_once '../config/conexion.php';
require_once __DIR__ . '/HistorialMovimientos.php';

$conexion = new Conexion();
$db = $conexion->getConnection();

$accion = $_REQUEST['accion'] ?? '';

/* ══════════════════════════════════════════════
   LISTAR MATERIALES (para los selects dinámicos)
   ══════════════════════════════════════════════ */
if ($accion === 'listar_materiales') {
    $stmt = $db->prepare("
        SELECT mp.id_material, mp.nombre_material, um.nombre_unidad
        FROM materias_primas mp
        LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
        ORDER BY mp.nombre_material ASC
    ");
    $stmt->execute();
    echo json_encode(['ok' => true, 'materiales' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit();
}

/* ══════════════════════════════════════════════
   OBTENER RECETA de un modelo existente
   ══════════════════════════════════════════════ */
if ($accion === 'obtener_receta') {
    $idModelo = (int) ($_REQUEST['id_modelo'] ?? 0);

    if ($idModelo <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de modelo inválido.']);
        exit();
    }

    $stmt = $db->prepare("
        SELECT rc.id_material, rc.cantidad_requerida,
               mp.nombre_material, um.nombre_unidad
        FROM receta_colchon rc
        INNER JOIN materias_primas mp ON mp.id_material = rc.id_material
        LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
        WHERE rc.id_modelo = :id_modelo
        ORDER BY mp.nombre_material
    ");
    $stmt->execute([':id_modelo' => $idModelo]);
    $receta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'receta' => $receta]);
    exit();
}

/* ══════════════════════════════════════════════
   REGISTRAR MODELO + RECETA (transacción)
   ══════════════════════════════════════════════ */
if ($accion === 'registrar_modelo') {

    $nombre = trim($_POST['nombre_modelo'] ?? '');
    $serial = trim($_POST['serial'] ?? '');
    $recetaJson = $_POST['receta'] ?? '[]';
    $receta = json_decode($recetaJson, true);

    // Validaciones backend
    if ($nombre === '' || $serial === '') {
        echo json_encode(['ok' => false, 'error' => 'El nombre y serial del modelo son obligatorios.']);
        exit();
    }

    if (!is_array($receta) || count($receta) === 0) {
        echo json_encode(['ok' => false, 'error' => 'La receta es obligatoria. Agrega al menos un material.']);
        exit();
    }

    // Validar cada ingrediente
    foreach ($receta as $i => $ingrediente) {
        $idMat = (int) ($ingrediente['id_material'] ?? 0);
        $cant  = (float) ($ingrediente['cantidad'] ?? 0);
        if ($idMat <= 0 || $cant <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Ingrediente #' . ($i + 1) . ': material y cantidad deben ser válidos.']);
            exit();
        }
    }

    try {
        $db->beginTransaction();

        // 1. Insertar modelo
        $stmtModelo = $db->prepare("
            INSERT INTO modelos_colchon (nombre_modelo, serial)
            VALUES (:nombre, :serial)
        ");
        $stmtModelo->execute([':nombre' => $nombre, ':serial' => $serial]);
        $nuevoIdModelo = (int) $db->lastInsertId();

        // 2. Insertar cada ingrediente de la receta
        $stmtReceta = $db->prepare("
            INSERT INTO receta_colchon (id_modelo, id_material, cantidad_requerida)
            VALUES (:id_modelo, :id_material, :cantidad)
        ");

        foreach ($receta as $ingrediente) {
            $stmtReceta->execute([
                ':id_modelo'  => $nuevoIdModelo,
                ':id_material' => (int) $ingrediente['id_material'],
                ':cantidad'    => (float) $ingrediente['cantidad'],
            ]);
        }

        $db->commit();

        // Registrar en historial
        $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';
        (new HistorialMovimientos())->registrar([
            'modulo'       => 'modelos_colchon',
            'accion'       => 'crear',
            'id_registro'  => $nuevoIdModelo,
            'descripcion'  => "Se registró el modelo '{$nombre}' (serial: {$serial}) con " . count($receta) . " materiales en su receta.",
            'datos_nuevos' => ['nombre_modelo' => $nombre, 'serial' => $serial, 'receta' => $receta],
            'usuario_nombre' => $usuarioNombre,
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' => "Modelo '{$nombre}' registrado exitosamente con su receta.",
            'id_modelo' => $nuevoIdModelo,
        ]);

    } catch (\Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        // Check for duplicate serial
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            echo json_encode(['ok' => false, 'error' => 'Ya existe un modelo con ese serial.']);
        } else {
            error_log('Error registrar_modelo: ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Error al registrar el modelo.']);
        }
    }

    exit();
}

/* ══════════════════════════════════════════════
   ACTUALIZAR RECETA (transacción DELETE + INSERT)
   ══════════════════════════════════════════════ */
if ($accion === 'actualizar_receta') {

    $idModelo  = (int) ($_POST['id_modelo'] ?? 0);
    $recetaJson = $_POST['receta'] ?? '[]';
    $receta = json_decode($recetaJson, true);

    if ($idModelo <= 0) {
        echo json_encode(['ok' => false, 'error' => 'ID de modelo inválido.']);
        exit();
    }

    if (!is_array($receta) || count($receta) === 0) {
        echo json_encode(['ok' => false, 'error' => 'La receta es obligatoria. Agrega al menos un material.']);
        exit();
    }

    // Validar cada ingrediente
    foreach ($receta as $i => $ingrediente) {
        $idMat = (int) ($ingrediente['id_material'] ?? 0);
        $cant  = (float) ($ingrediente['cantidad'] ?? 0);
        if ($idMat <= 0 || $cant <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Ingrediente #' . ($i + 1) . ': material y cantidad deben ser válidos.']);
            exit();
        }
    }

    // Verificar que el modelo existe
    $stmtCheck = $db->prepare("SELECT nombre_modelo FROM modelos_colchon WHERE id_modelo = :id LIMIT 1");
    $stmtCheck->execute([':id' => $idModelo]);
    $modeloExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$modeloExistente) {
        echo json_encode(['ok' => false, 'error' => 'El modelo no existe.']);
        exit();
    }

    try {
        $db->beginTransaction();

        // 1. Eliminar receta anterior
        $stmtDel = $db->prepare("DELETE FROM receta_colchon WHERE id_modelo = :id_modelo");
        $stmtDel->execute([':id_modelo' => $idModelo]);

        // 2. Insertar nueva receta
        $stmtIns = $db->prepare("
            INSERT INTO receta_colchon (id_modelo, id_material, cantidad_requerida)
            VALUES (:id_modelo, :id_material, :cantidad)
        ");

        foreach ($receta as $ingrediente) {
            $stmtIns->execute([
                ':id_modelo'   => $idModelo,
                ':id_material' => (int) $ingrediente['id_material'],
                ':cantidad'    => (float) $ingrediente['cantidad'],
            ]);
        }

        $db->commit();

        // Registrar en historial
        $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';
        (new HistorialMovimientos())->registrar([
            'modulo'       => 'modelos_colchon',
            'accion'       => 'editar',
            'id_registro'  => $idModelo,
            'descripcion'  => "Se actualizó la receta del modelo '{$modeloExistente['nombre_modelo']}' con " . count($receta) . " materiales.",
            'datos_nuevos' => ['receta' => $receta],
            'usuario_nombre' => $usuarioNombre,
        ]);

        echo json_encode([
            'ok' => true,
            'mensaje' => "Receta del modelo '{$modeloExistente['nombre_modelo']}' actualizada exitosamente.",
        ]);

    } catch (\Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Error actualizar_receta: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'Error al actualizar la receta.']);
    }

    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
