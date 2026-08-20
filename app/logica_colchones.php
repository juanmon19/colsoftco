<?php

header('Content-Type: application/json');
require_once '../config/conexion.php';
require_once __DIR__ . '/HistorialMovimientos.php';

session_start();

$conexion = new Conexion();
$db = $conexion->getConnection();

/**
 * Elige un icono ilustrativo según palabras clave en el nombre
 * del material. Es puramente decorativo, no afecta el cálculo.
 */
function iconoMaterial(string $nombre): string
{
    $nombre = mb_strtolower($nombre);

    if (str_contains($nombre, 'espuma'))  return '🧊';
    if (str_contains($nombre, 'tela'))    return '🧵';
    if (str_contains($nombre, 'resorte')) return '🌀';
    if (str_contains($nombre, 'pegante')) return '🧴';
    if (str_contains($nombre, 'hilo'))    return '🧶';
    if (str_contains($nombre, 'fieltro')) return '🧻';
    if (str_contains($nombre, 'empaque')) return '📦';
    if (str_contains($nombre, 'borde'))   return '📏';

    return '🔹';
}

/**
 * Trae la receta REAL de un modelo desde la base de datos
 * (tabla receta_colchon, unida a materias_primas y unidades_medida)
 * y la compara contra el stock actual disponible.
 */
function evaluarProduccion(PDO $db, int $idModelo, float $cantidad): array
{
    if ($cantidad <= 0) {
        return ['ok' => false, 'error' => 'La cantidad debe ser mayor a 0.'];
    }

    $stmtModelo = $db->prepare(
        "SELECT nombre_modelo FROM modelos_colchon WHERE id_modelo = :id_modelo"
    );
    $stmtModelo->execute([':id_modelo' => $idModelo]);
    $modelo = $stmtModelo->fetch(PDO::FETCH_ASSOC);

    if (!$modelo) {
        return ['ok' => false, 'error' => 'Modelo de colchón no encontrado.'];
    }

    $sql = "SELECT
                rc.id_material,
                rc.cantidad_requerida,
                mp.nombre_material,
                mp.stock_actual,
                um.nombre_unidad
            FROM receta_colchon rc
            INNER JOIN materias_primas mp ON mp.id_material = rc.id_material
            LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
            WHERE rc.id_modelo = :id_modelo";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id_modelo' => $idModelo]);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$filas) {
        return [
            'ok'    => false,
            'error' => 'Este modelo todavía no tiene una receta registrada en receta_colchon.',
        ];
    }

    $detalle = [];
    $produccionPosible = true;

    foreach ($filas as $fila) {

        $requerida  = (float) $fila['cantidad_requerida'] * $cantidad;
        $disponible = (float) $fila['stock_actual'];
        $suficiente = $disponible >= $requerida;

        if (!$suficiente) {
            $produccionPosible = false;
        }

        $detalle[] = [
            'id_material'         => $fila['id_material'],
            'nombre_material'     => $fila['nombre_material'],
            'icono'               => iconoMaterial($fila['nombre_material']),
            'unidad'              => $fila['nombre_unidad'] ?? '',
            'cantidad_requerida'  => $requerida,
            'cantidad_disponible' => $disponible,
            'suficiente'          => $suficiente,
        ];
    }

    return [
        'ok'                 => true,
        'id_modelo'          => $idModelo,
        'nombre_producto'    => $modelo['nombre_modelo'],
        'cantidad'           => $cantidad,
        'produccion_posible' => $produccionPosible,
        'materiales'         => $detalle,
    ];
}

$accion   = $_REQUEST['accion']    ?? '';
$idModelo = isset($_REQUEST['id_modelo']) ? (int) $_REQUEST['id_modelo'] : 0;
$cantidad = isset($_REQUEST['cantidad']) ? (float) $_REQUEST['cantidad'] : 0;

/* ══ Solo calcula y compara, no toca la base de datos ══ */
if ($accion === 'calcular') {
    echo json_encode(evaluarProduccion($db, $idModelo, $cantidad));
    exit();
}

/* ══ Descuenta materia prima y aumenta el producto terminado ══ */
if ($accion === 'fabricar') {

    $resultado = evaluarProduccion($db, $idModelo, $cantidad);

    if (!$resultado['ok'] || !$resultado['produccion_posible']) {
        echo json_encode(['ok' => false, 'error' => 'No hay suficiente materia prima para fabricar.']);
        exit();
    }

    try {
        $db->beginTransaction();

        foreach ($resultado['materiales'] as $mat) {
            $stmt = $db->prepare(
                "UPDATE materias_primas
                 SET stock_actual = stock_actual - :requerida
                 WHERE id_material = :id_material"
            );
            $stmt->execute([
                ':requerida'   => $mat['cantidad_requerida'],
                ':id_material' => $mat['id_material'],
            ]);
        }

        /* La tabla productos_terminados no tiene columna id_modelo,
           el producto existente se identifica por nombre_producto
           (mismo patrón que usa registro_producto_terminado.php). */
        $stmt = $db->prepare(
            "SELECT id_producto FROM productos_terminados WHERE nombre_producto = :nombre LIMIT 1"
        );
        $stmt->execute([':nombre' => $resultado['nombre_producto']]);
        $existente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $stmt = $db->prepare(
                "UPDATE productos_terminados
         SET stock_actual = stock_actual + :cantidad
         WHERE id_producto = :id"
            );
            $stmt->execute([
                ':cantidad' => $resultado['cantidad'],
                ':id'       => $existente['id_producto'],
            ]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO productos_terminados (nombre_producto, stock_actual)
         VALUES (:nombre, :cantidad)"
            );
            $stmt->execute([
                ':nombre'    => $resultado['nombre_producto'],
                ':cantidad'  => $resultado['cantidad'],
            ]);
        }

        $db->commit();

        // Registrar en el historial: salida de cada materia prima consumida
        $historial = new HistorialMovimientos();
        $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';

        foreach ($resultado['materiales'] as $mat) {
            $historial->registrar([
                'modulo'      => 'produccion',
                'accion'      => 'salida',
                'id_registro' => $mat['id_material'],
                'descripcion' => "Salida de {$mat['cantidad_requerida']} {$mat['unidad']} de '{$mat['nombre_material']}' "
                    . "para fabricar {$resultado['cantidad']} unidades de '{$resultado['nombre_producto']}'",
                'datos_anteriores' => ['stock_actual' => $mat['cantidad_disponible']],
                'datos_nuevos'     => ['stock_actual' => $mat['cantidad_disponible'] - $mat['cantidad_requerida']],
                'usuario_nombre'   => $usuarioNombre,
            ]);
        }

        // Registrar en el historial: entrada del producto terminado
        $historial->registrar([
            'modulo'         => 'produccion',
            'accion'         => 'entrada',
            'id_registro'    => $resultado['id_modelo'],
            'descripcion'    => "Se fabricaron {$resultado['cantidad']} unidades de '{$resultado['nombre_producto']}'",
            'datos_nuevos'   => ['unidades_fabricadas' => $resultado['cantidad']],
            'usuario_nombre' => $usuarioNombre,
        ]);

        echo json_encode([
            'ok'      => true,
            'mensaje' => "Se fabricaron {$resultado['cantidad']} unidades de {$resultado['nombre_producto']} correctamente.",
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['ok' => false, 'error' => 'Error al fabricar: ' . $e->getMessage()]);
    }

    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);