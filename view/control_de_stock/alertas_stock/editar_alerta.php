<?php

require_once __DIR__ . '/../../../app/verificar_sesion.php';
require_once '../../../app/logica_inventario.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';

$logica = new InventarioLogica();

$id = $_GET['id'];

$material = $logica->obtenerMaterial($id);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $stockMinimoAnterior = $material['stock_minimo'] ?? null;

    $logica->actualizarStockMinimo(
        $id,
        $_POST['stock_minimo']
    );

    (new HistorialMovimientos())->registrar([
        'modulo'           => 'alertas_stock',
        'accion'           => 'editar',
        'id_registro'      => $id,
        'descripcion'      => "Se actualizó el stock mínimo de '{$material['nombre_material']}'",
        'datos_anteriores' => ['stock_minimo' => $stockMinimoAnterior],
        'datos_nuevos'     => ['stock_minimo' => $_POST['stock_minimo']],
        'usuario_nombre'   => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
    ]);

    header("Location: lista_alertas.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Alerta de Stock</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo -->
    <link rel="stylesheet" href="../../../public/css/global.css">
    <link rel="stylesheet" href="../../../public/css/layout.css">
    <link rel="stylesheet" href="alertas.css">
    <?php include __DIR__ . '/../../partials/scripts_layout.php'; ?>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../../partials/sidebar.php'; ?>

        <div class="main">

            <?php
            $rolActual = 'Administrador';
            include __DIR__ . '/../../partials/topbar.php';
            ?>

            <main class="content">

    <div class="container">

        <div class="card">

            <div class="card-header">
                Actualizar Alerta
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="form-group">
                        <label>Material</label>
                        <input type="text"
                            class="form-control"
                            value="<?= $material['nombre_material'] ?>"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number"
                            name="stock_minimo"
                            class="form-control"
                            value="<?= $material['stock_minimo'] ?>"
                            required>
                    </div>

                    <div class="botones">

                        <a href="lista_alertas.php"
                            class="btn btn-volver">
                            ← Volver
                        </a>

                        <button type="submit"
                            class="btn btn-guardar">
                            Guardar Cambios
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../../partials/scripts_layout_footer.php'; ?>

</body>

</html>