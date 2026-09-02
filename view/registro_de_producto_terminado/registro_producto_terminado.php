<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$db = new Conexion();
$conn = $db->getConnection();

$mensaje = '';
$mensajeTipo = '';

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
| REGISTRAR PRODUCTO TERMINADO
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ahora recibimos el ID del modelo seleccionado en el desplegable
    $id_modelo = $_POST['id_modelo'] ?? '';
    $cantidad = $_POST['cantidad'] ?? '';

    /*
     * Validación
     */
    if (
        $id_modelo === '' ||
        $cantidad === '' ||
        !is_numeric($cantidad) ||
        $cantidad < 0
    ) {
        $mensaje = 'Por favor seleccione un producto y una cantidad válida.';
        $mensajeTipo = 'error';
    } else {

        try {

            /*
             * Obtener el nombre del modelo seleccionado
             */
            $stmt = $conn->prepare("
                SELECT id_modelo, nombre_modelo
                FROM modelos_colchon
                WHERE id_modelo = :id_modelo
                LIMIT 1
            ");

            $stmt->execute([
                ':id_modelo' => $id_modelo
            ]);

            $modeloSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$modeloSeleccionado) {
                throw new Exception('El producto seleccionado no existe.');
            }

            $nombre_producto = $modeloSeleccionado['nombre_modelo'];


            /*
             * Iniciar transacción
             */
            $conn->beginTransaction();


            /*
             * Mismo patrón "upsert" que usa
             * app/logica_colchones.php al fabricar:
             *
             * Si el producto ya existe:
             *     se suma al stock.
             *
             * Si no existe:
             *     se crea.
             */
            $stmt = $conn->prepare(
                "SELECT id_producto, stock_actual
                 FROM productos_terminados
                 WHERE nombre_producto = :nombre
                 LIMIT 1"
            );

            $stmt->execute([
                ':nombre' => $nombre_producto
            ]);

            $existente = $stmt->fetch(PDO::FETCH_ASSOC);


            /*
             * SI EL PRODUCTO YA EXISTE
             */
            if ($existente) {

                $stmt = $conn->prepare(
                    "UPDATE productos_terminados
                     SET stock_actual = stock_actual + :cantidad
                     WHERE id_producto = :id"
                );

                $stmt->execute([
                    ':cantidad' => $cantidad,
                    ':id' => $existente['id_producto'],
                ]);

                $idProducto = $existente['id_producto'];


                /*
             * SI EL PRODUCTO NO EXISTE
             */
            } else {

                $stmt = $conn->prepare(
                    "INSERT INTO productos_terminados
                     (nombre_producto, stock_actual)
                     VALUES (:nombre, :cantidad)"
                );

                $stmt->execute([
                    ':nombre' => $nombre_producto,
                    ':cantidad' => $cantidad,
                ]);

                $idProducto = $conn->lastInsertId();
            }


            /*
             * Confirmar transacción
             */
            $conn->commit();


            /*
             * Registrar movimiento en historial
             */
            (new HistorialMovimientos())->registrar([
                'modulo'       => 'producto_terminado',
                'accion'       => 'crear',
                'id_registro'  => $idProducto,

                'descripcion'  =>
                "Se registró producción de {$cantidad} unidades de '{$nombre_producto}'",

                'datos_nuevos' => [
                    'nombre_producto' => $nombre_producto,
                    'cantidad'        => $cantidad,
                ],

                'usuario_nombre' =>
                trim(
                    ($_SESSION['nombre'] ?? '') .
                        ' ' .
                        ($_SESSION['apellido'] ?? '')
                ) ?: 'Sistema',
            ]);


            /*
             * Mensaje de éxito
             */
            $mensaje =
                "Producto '{$nombre_producto}' registrado correctamente.";

            $mensajeTipo = 'ok';
        } catch (Exception $e) {

            /*
             * Si hubo una transacción activa,
             * revertir los cambios.
             */
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $mensaje =
                'Error al registrar el producto: ' .
                $e->getMessage();

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
            $rolActual = 'Administrador';
            include __DIR__ . '/../partials/topbar.php';
            ?>

            <main class="content">
<!-- ── BODY ── -->

    <div class="page-body">


        <!-- CONTENT -->

        <main
            class="content"
            style="margin: 0 auto; max-width: 1000px; width: 100%;">


            <!-- FORMULARIO REGISTRAR PRODUCTO -->

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
                                margin-bottom:16px;
                                padding:10px 14px;
                                border-radius:6px;
                                font-weight:600;

                                <?= $mensajeTipo === 'ok'
                                    ? 'background:#e5f7ec;color:#1e7e42;'
                                    : 'background:#fdecea;color:#c0392b;'
                                ?>
                            ">

                            <?= htmlspecialchars($mensaje) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        id="regForm"
                        method="POST">

                        <div class="form-grid">


                            <!-- PRODUCTO -->

                            <div class="form-group full">

                                <label for="input-producto">
                                    Producto
                                </label>


                                <select
                                    id="input-producto"
                                    name="id_modelo"
                                    required>

                                    <option value="">
                                        Seleccione un producto
                                    </option>


                                    <?php foreach ($modelos as $modelo): ?>

                                        <option
                                            value="<?= htmlspecialchars($modelo['id_modelo']) ?>">

                                            <?= htmlspecialchars($modelo['nombre_modelo']) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- CANTIDAD -->

                            <div class="form-group full">

                                <label for="cantidad">
                                    Cantidad
                                </label>

                                <input
                                    type="number"
                                    id="cantidad"
                                    name="cantidad"
                                    placeholder="Ingrese la cantidad"
                                    min="0"
                                    step="0.01"
                                    required />

                            </div>


                            <hr class="form-divider" />


                            <!-- BOTONES -->

                            <div class="form-actions">

                                <button
                                    type="reset"
                                    class="btn btn-outline">
                                    Limpiar
                                </button>


                                <button
                                    type="submit"
                                    class="btn btn-primary">
                                    Registrar
                                </button>

                            </div>


                        </div>

                    </form>

                </div>

            </div>

        </main>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>

    <script
        src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js"
        defer></script>

    <script src="../../public/js/app.js"></script>

</body>

</html>