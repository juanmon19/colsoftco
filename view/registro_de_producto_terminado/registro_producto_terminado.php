<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$db = new Conexion();
$conn = $db->getConnection();

$mensaje = '';
$mensajeTipo = '';
$faltantesLista = [];

/*
|--------------------------------------------------------------------------
| OBTENER MODELOS PARA EL DESPLEGABLE
|--------------------------------------------------------------------------
*/
$modelos = $conn->query("
    SELECT id_modelo, nombre_modelo
    FROM modelos_colchon
    ORDER BY nombre_modelo
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| REGISTRAR PRODUCTO TERMINADO (CON VALIDACIÓN DE RECETA Y STOCK)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_modelo = isset($_POST['id_modelo']) ? (int) $_POST['id_modelo'] : 0;
    $cantidad  = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 0;

    if ($id_modelo <= 0 || $cantidad <= 0) {
        $mensaje = 'Por favor seleccione un producto y una cantidad válida mayor a 0.';
        $mensajeTipo = 'error';
    } else {

        try {
            $conn->beginTransaction();

            // 1. Verificar que el modelo exista
            $stmt = $conn->prepare("
                SELECT id_modelo, nombre_modelo
                FROM modelos_colchon
                WHERE id_modelo = :id_modelo
                LIMIT 1
            ");
            $stmt->execute([':id_modelo' => $id_modelo]);
            $modeloSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$modeloSeleccionado) {
                throw new Exception('El producto seleccionado no existe.');
            }

            $nombre_producto = $modeloSeleccionado['nombre_modelo'];

            // 2. Consultar la receta del modelo y bloquear filas de materias primas
            $sqlReceta = "SELECT
                    rc.id_material,
                    rc.cantidad_requerida,
                    mp.nombre_material,
                    mp.stock_actual,
                    um.nombre_unidad
                FROM receta_colchon rc
                INNER JOIN materias_primas mp ON mp.id_material = rc.id_material
                LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
                WHERE rc.id_modelo = :id_modelo AND mp.estado = 'activo'
                FOR UPDATE";

            $stmtReceta = $conn->prepare($sqlReceta);
            $stmtReceta->execute([':id_modelo' => $id_modelo]);
            $filasReceta = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

            if (!$filasReceta) {
                throw new Exception("El modelo '{$nombre_producto}' no tiene una receta de materiales registrada.");
            }

            // 3. Validar si hay suficiente materia prima para toda la producción
            $materialesADescontar = [];

            foreach ($filasReceta as $fila) {
                $requerida  = (float) $fila['cantidad_requerida'] * $cantidad;
                $disponible = (float) $fila['stock_actual'];
                $unidad     = $fila['nombre_unidad'] ?? 'und';

                if ($disponible < $requerida) {
                    $faltantesLista[] = [
                        'material'   => $fila['nombre_material'],
                        'requerida'  => rtrim(rtrim(number_format($requerida, 2, ',', '.'), '0'), ','),
                        'disponible' => rtrim(rtrim(number_format($disponible, 2, ',', '.'), '0'), ','),
                        'unidad'     => $unidad,
                    ];
                } else {
                    $materialesADescontar[] = [
                        'id_material'         => $fila['id_material'],
                        'nombre_material'     => $fila['nombre_material'],
                        'unidad'              => $unidad,
                        'cantidad_requerida'  => $requerida,
                        'cantidad_disponible' => $disponible,
                    ];
                }
            }

            if (!empty($faltantesLista)) {
                $cantFmt = number_format($cantidad, 0, ',', '.');
                throw new Exception("Materia prima insuficiente para fabricar {$cantFmt} unidades de '{$nombre_producto}':");
            }

            // 4. Descontar cada materia prima del inventario
            foreach ($materialesADescontar as $mat) {
                $stmtDesc = $conn->prepare("
                    UPDATE materias_primas
                    SET stock_actual = stock_actual - :req_val
                    WHERE id_material = :id_material AND stock_actual >= :req_check
                ");
                $stmtDesc->execute([
                    ':req_val'     => $mat['cantidad_requerida'],
                    ':id_material' => $mat['id_material'],
                    ':req_check'   => $mat['cantidad_requerida'],
                ]);

                if ($stmtDesc->rowCount() === 0) {
                    throw new Exception("Stock insuficiente de '{$mat['nombre_material']}' al procesar el descuento.");
                }
            }

            // 5. Sumar al inventario de productos terminados (Upsert)
            $stmt = $conn->prepare(
                "SELECT id_producto, stock_actual
                 FROM productos_terminados
                 WHERE nombre_producto = :nombre
                 LIMIT 1"
            );
            $stmt->execute([':nombre' => $nombre_producto]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $stmt = $conn->prepare(
                    "UPDATE productos_terminados
                     SET stock_actual = stock_actual + :cantidad
                     WHERE id_producto = :id"
                );
                $stmt->execute([
                    ':cantidad' => $cantidad,
                    ':id'       => $existente['id_producto'],
                ]);
                $idProducto = $existente['id_producto'];
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO productos_terminados
                     (nombre_producto, stock_actual)
                     VALUES (:nombre, :cantidad)"
                );
                $stmt->execute([
                    ':nombre'   => $nombre_producto,
                    ':cantidad' => $cantidad,
                ]);
                $idProducto = $conn->lastInsertId();
            }

            $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';

            // 6. Registrar en historial_produccion
            $stmtHistProd = $conn->prepare(
                "INSERT INTO historial_produccion (id_modelo, cantidad, fecha_fabricacion, usuario)
                 VALUES (:id_modelo, :cantidad, NOW(), :usuario)"
            );
            $stmtHistProd->execute([
                ':id_modelo' => $id_modelo,
                ':cantidad'  => $cantidad,
                ':usuario'   => $usuarioNombre,
            ]);

            $conn->commit();

            // 7. Registrar salidas de materia prima y entrada de producto en HistorialMovimientos
            $historial = new HistorialMovimientos();

            foreach ($materialesADescontar as $mat) {
                $historial->registrar([
                    'modulo'           => 'materia_prima',
                    'accion'           => 'salida',
                    'id_registro'      => $mat['id_material'],
                    'descripcion'      => "Salida de {$mat['cantidad_requerida']} {$mat['unidad']} de '{$mat['nombre_material']}' para fabricar {$cantidad} unidades de '{$nombre_producto}'",
                    'datos_anteriores' => ['stock_actual' => $mat['cantidad_disponible']],
                    'datos_nuevos'     => ['stock_actual' => $mat['cantidad_disponible'] - $mat['cantidad_requerida']],
                    'usuario_nombre'   => $usuarioNombre,
                ]);
            }

            $historial->registrar([
                'modulo'         => 'producto_terminado',
                'accion'         => 'crear',
                'id_registro'    => $idProducto,
                'descripcion'    => "Se registró producción de {$cantidad} unidades de '{$nombre_producto}'",
                'datos_nuevos'   => [
                    'nombre_producto' => $nombre_producto,
                    'cantidad'        => $cantidad,
                ],
                'usuario_nombre' => $usuarioNombre,
            ]);

            $mensaje = "Producto '{$nombre_producto}' registrado correctamente ({$cantidad} unidades) y materia prima descontada.";
            $mensajeTipo = 'ok';

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $mensaje = $e->getMessage();
            $mensajeTipo = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include __DIR__ . '/../partials/seo.php'; ?>

    <title>Registro de productos terminados</title>

    <link href="registro_producto_terminado.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>


<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php
            $rolActual = $_SESSION['rol'] ?? 'Administrador';
            include __DIR__ . '/../partials/topbar.php';
            ?>

            <main class="content">
    <div class="page-body">

        <div
            class="content regpt-contenido"
            style="margin: 0 auto; max-width: 1000px; width: 100%;">

            <div class="form-card">

                <div class="form-header">
                    <span class="form-header-bar"></span>
                    Registra Producto
                </div>

                <div class="form-body">

                    <?php if ($mensaje): ?>
                        <div
                            class="mensaje mensaje-<?= $mensajeTipo ?>"
                            style="
                                margin-bottom:20px;
                                padding:14px 18px;
                                border-radius:8px;
                                font-size:0.95rem;
                                line-height:1.5;
                                <?= $mensajeTipo === 'ok'
                                    ? 'background:#e5f7ec;color:#1e7e42;border:1px solid #b7ebc6;border-left:4px solid #1e7e42;font-weight:600;'
                                    : 'background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-left:4px solid #dc2626;'
                                ?>
                            ">
                            <div style="font-weight:700; <?= !empty($faltantesLista) ? 'margin-bottom:8px;' : '' ?>">
                                <?= $mensajeTipo === 'ok' ? '✓ ' : '⚠ ' ?><?= htmlspecialchars($mensaje) ?>
                            </div>

                            <?php if (!empty($faltantesLista)): ?>
                                <ul style="margin:0; padding-left:20px; font-size:0.88rem; color:#7f1d1d;">
                                    <?php foreach ($faltantesLista as $item): ?>
                                        <li style="margin-bottom:4px;">
                                            <strong><?= htmlspecialchars($item['material']) ?>:</strong>
                                            requiere <?= htmlspecialchars($item['requerida']) ?> <?= htmlspecialchars($item['unidad']) ?>
                                            <span style="color:#b91c1c;">(disponible: <?= htmlspecialchars($item['disponible']) ?> <?= htmlspecialchars($item['unidad']) ?>)</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form id="regForm" method="POST">
                        <div class="form-grid">

                            <!-- PRODUCTO -->
                            <div class="form-group full">
                                <label for="input-producto">Producto</label>
                                <select id="input-producto" name="id_modelo" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($modelos as $modelo): ?>
                                        <option value="<?= htmlspecialchars($modelo['id_modelo']) ?>">
                                            <?= htmlspecialchars($modelo['nombre_modelo']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- CANTIDAD -->
                            <div class="form-group full">
                                <label for="cantidad">Cantidad</label>
                                <input
                                    type="number"
                                    id="cantidad"
                                    name="cantidad"
                                    placeholder="Ingrese la cantidad"
                                    min="1"
                                    step="1"
                                    required />
                            </div>

                            <hr class="form-divider" />

                            <!-- BOTONES -->
                            <div class="form-actions">
                                <button type="reset" class="btn btn-outline">Limpiar</button>
                                <button type="submit" class="btn btn-primary">Registrar</button>
                            </div>

                        </div>
                    </form>

                </div>

            </div>

        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>

</body>

</html>
