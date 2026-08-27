<?php
require_once 'verificar_sesion.php';
date_default_timezone_set('America/Bogota');
header('Content-Type: application/json');
require_once '../config/conexion.php';
require_once __DIR__ . '/HistorialMovimientos.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// 1. Verificación de Seguridad
if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

// 2. Conexión a la Base de Datos
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
 * y la compara contra el stock actual disponible.
 */
function evaluarProduccion(PDO $db, int $idModelo, int $cantidad): array
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
$cantidad = isset($_REQUEST['cantidad']) ? (int) $_REQUEST['cantidad'] : 0;

/* ══ Solo calcula y compara, no toca la base de datos ══ */
if ($accion === 'calcular') {
    echo json_encode(evaluarProduccion($db, $idModelo, $cantidad));
    exit();
}

/* ══ Descuenta materia prima y aumenta el producto terminado ══ */
if ($accion === 'fabricar') {

    try {
        $db->beginTransaction();

        // Evaluar dentro de la transacción para evitar race conditions
        if ($cantidad <= 0) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'error' => 'La cantidad debe ser mayor a 0.']);
            exit();
        }

        $stmtModelo = $db->prepare(
            "SELECT nombre_modelo FROM modelos_colchon WHERE id_modelo = :id_modelo"
        );
        $stmtModelo->execute([':id_modelo' => $idModelo]);
        $modelo = $stmtModelo->fetch(PDO::FETCH_ASSOC);

        if (!$modelo) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'error' => 'Modelo de colchón no encontrado.']);
            exit();
        }

        // Obtener receta con bloqueo de filas (FOR UPDATE)
        $sqlReceta = "SELECT
                rc.id_material,
                rc.cantidad_requerida,
                mp.nombre_material,
                mp.stock_actual,
                um.nombre_unidad
            FROM receta_colchon rc
            INNER JOIN materias_primas mp ON mp.id_material = rc.id_material
            LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
            WHERE rc.id_modelo = :id_modelo
            FOR UPDATE";

        $stmtReceta = $db->prepare($sqlReceta);
        $stmtReceta->execute([':id_modelo' => $idModelo]);
        $filas = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

        if (!$filas) {
            $db->rollBack();
            echo json_encode(['ok' => false, 'error' => 'Este modelo no tiene receta registrada.']);
            exit();
        }

        // Verificar stock suficiente (ahora con filas bloqueadas)
        $materiales = [];
        foreach ($filas as $fila) {
            $requerida  = (float) $fila['cantidad_requerida'] * $cantidad;
            $disponible = (float) $fila['stock_actual'];

            if ($disponible < $requerida) {
                $db->rollBack();
                echo json_encode(['ok' => false, 'error' => 'No hay suficiente materia prima para fabricar.']);
                exit();
            }

            $materiales[] = [
                'id_material'         => $fila['id_material'],
                'nombre_material'     => $fila['nombre_material'],
                'unidad'              => $fila['nombre_unidad'] ?? '',
                'cantidad_requerida'  => $requerida,
                'cantidad_disponible' => $disponible,
            ];
        }

        $resultado = [
            'ok'              => true,
            'id_modelo'       => $idModelo,
            'nombre_producto' => $modelo['nombre_modelo'],
            'cantidad'        => $cantidad,
            'materiales'      => $materiales,
        ];

        // Descontar materia prima de forma segura con parámetros únicos
        foreach ($resultado['materiales'] as $mat) {
            $stmt = $db->prepare(
                "UPDATE materias_primas
                 SET stock_actual = stock_actual - :req_val
                 WHERE id_material = :id_material AND stock_actual >= :req_check"
            );
            $stmt->execute([
                ':req_val'     => $mat['cantidad_requerida'],
                ':id_material' => $mat['id_material'],
                ':req_check'   => $mat['cantidad_requerida'],
            ]);
            
            // Validar que la base de datos realmente haya descontado el material
            if ($stmt->rowCount() === 0) {
                $db->rollBack();
                echo json_encode(['ok' => false, 'error' => 'Error de concurrencia: Stock insuficiente en el último milisegundo.']);
                exit();
            }
        }

        // Aumentar inventario de producto terminado
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

        // Registrar la fabricación en historial_produccion
        $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';

        $stmtHistorialProd = $db->prepare(
            "INSERT INTO historial_produccion (id_modelo, cantidad, fecha_fabricacion, usuario)
             VALUES (:id_modelo, :cantidad, NOW(), :usuario)"
        );
        $stmtHistorialProd->execute([
            ':id_modelo' => $resultado['id_modelo'],
            ':cantidad'  => $resultado['cantidad'],
            ':usuario'   => $usuarioNombre,
        ]);
        $numeroRecibo = (int) $db->lastInsertId();

        /* =======================================================
           REGISTROS EN EL HISTORIAL DE MOVIMIENTOS
           ======================================================= */
        $historial = new HistorialMovimientos();

        // 1. Salidas de Materia Prima
        foreach ($resultado['materiales'] as $mat) {
            $historial->registrar([
                'modulo'      => 'materia_prima',
                'accion'      => 'salida',
                'id_registro' => $mat['id_material'],
                'descripcion' => "Salida de {$mat['cantidad_requerida']} {$mat['unidad']} de '{$mat['nombre_material']}' "
                    . "para fabricar {$resultado['cantidad']} unidades de '{$resultado['nombre_producto']}'",
                'datos_anteriores' => ['stock_actual' => $mat['cantidad_disponible']],
                'datos_nuevos'     => ['stock_actual' => $mat['cantidad_disponible'] - $mat['cantidad_requerida']],
                'usuario_nombre'   => $usuarioNombre,
            ]);
        }

        // 2. Entrada de Producto Terminado
        $historial->registrar([
            'modulo'         => 'producto_terminado',
            'accion'         => 'crear',
            'id_registro'    => $resultado['id_modelo'],
            'descripcion'    => "Se fabricaron {$resultado['cantidad']} unidades de '{$resultado['nombre_producto']}'",
            'datos_nuevos'   => ['unidades_fabricadas' => $resultado['cantidad'], 'nombre_producto' => $resultado['nombre_producto']],
            'usuario_nombre' => $usuarioNombre,
        ]);

        // 👇 COMMIT FINAL GUARDANDO TODO DE FORMA SEGURA 👇
        $db->commit();

        /* =======================================================
           GENERACIÓN DEL NUEVO RECIBO PDF CORPORATIVO
           ======================================================= */
        $carpetaRecibos = __DIR__ . '/../public/recibos/';
        if (!is_dir($carpetaRecibos)) {
            mkdir($carpetaRecibos, 0755, true);
        }

        $nombreArchivo = 'recibo_' . str_pad($numeroRecibo, 6, '0', STR_PAD_LEFT) . '.pdf';
        $rutaCompleta  = $carpetaRecibos . $nombreArchivo;

        // Convertir Logo a Base64
        $rutaLogo = __DIR__ . '/../public/imagenes/logo.png';
        $base64Logo = '';
        if (file_exists($rutaLogo)) {
            $tipoContenido = pathinfo($rutaLogo, PATHINFO_EXTENSION);
            $datosImagen = file_get_contents($rutaLogo);
            $base64Logo = 'data:image/' . $tipoContenido . ';base64,' . base64_encode($datosImagen);
        }

        $fechaActual = date('d/m/Y H:i');
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Helvetica, Arial, sans-serif; color: #333; margin: 15px; }
                .recibo-num { text-align: right; font-size: 11px; color: #666; margin-bottom: -15px; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .header img { width: 75px; margin-bottom: 8px; }
                .header h1 { color: #0A1F44; font-size: 18px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
                .header h3 { color: #D4AF37; font-size: 11px; margin: 5px 0 0 0; font-weight: normal; }
                .divider { border: none; border-top: 2px solid #D4AF37; margin: 15px 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                td { padding: 10px 8px; border-bottom: 1px solid #e2e6f0; font-size: 13px; }
                td.label { font-weight: bold; color: #0A1F44; width: 45%; }
                td.value { text-align: right; color: #333; }
                .mensaje-exito { background: #f4f6fb; border-left: 4px solid #0A1F44; padding: 12px; margin-top: 25px; border-radius: 4px; }
                .mensaje-exito strong { color: #0A1F44; display: block; margin-bottom: 5px; font-size: 13px; }
                .mensaje-exito p { margin: 0; font-size: 11px; color: #555; line-height: 1.4; }
                .footer { position: fixed; bottom: -15px; left: 0; right: 0; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 8px; }
            </style>
        </head>
        <body>
            <div class="recibo-num">Recibo No.: <?= str_pad($numeroRecibo, 6, '0', STR_PAD_LEFT) ?></div>
            
            <div class="header">
                <?php if($base64Logo): ?>
                    <img src="<?= $base64Logo ?>" alt="Logo ColSoft">
                <?php endif; ?>
                <h1>Comprobante de Producción</h1>
                <h3>COLSOFTCO - Sistema de Gestión Max&Flex</h3>
            </div>

            <hr class="divider">

            <table>
                <tr>
                    <td class="label">Producto fabricado:</td>
                    <td class="value"><?= htmlspecialchars($resultado['nombre_producto']) ?></td>
                </tr>
                <tr>
                    <td class="label">Cantidad producida:</td>
                    <td class="value"><?= htmlspecialchars($resultado['cantidad']) ?> unidades</td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td class="value"><?= $fechaActual ?></td>
                </tr>
            </table>

            <div class="mensaje-exito">
                <strong>Producción registrada exitosamente</strong>
                <p>El inventario de materia prima fue descontado y el stock de producto terminado fue actualizado correctamente en el sistema.</p>
            </div>

            <div class="footer">
                Generado automáticamente por el sistema COLSOFTCO • Página 1
            </div>
        </body>
        </html>
        <?php
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait'); 
        $dompdf->render();

        // Guardar el archivo PDF generado en la carpeta del servidor
        file_put_contents($rutaCompleta, $dompdf->output());

        // Devolver la respuesta JSON exitosa con la ruta al archivo
        echo json_encode([
            'ok'            => true,
            'mensaje'       => "Se fabricaron {$resultado['cantidad']} unidades de {$resultado['nombre_producto']} correctamente.",
            'recibo_pdf'    => '../../public/recibos/' . $nombreArchivo,
            'numero_recibo' => $numeroRecibo,
        ]);

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        echo json_encode([
            'ok' => false, 
            'error' => 'Fallo técnico: ' . $e->getMessage() . ' (Línea ' . $e->getLine() . ')'
        ]);
    }

    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);