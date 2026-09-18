<?php

require_once __DIR__ . '/../../../app/verificar_sesion.php';
require_once '../../../app/logica_inventario.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';

$logica = new InventarioLogica();

if (!isset($_GET['id'])) {
    header("Location: lista_alertas.php");
    exit;
}

$id = $_GET['id'];

/* Obtener material para mostrarlo */
$material = $logica->obtenerMaterial($id);

if (!$material) {
    header("Location: lista_alertas.php");
    exit;
}

/* Eliminar cuando se confirme */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $logica->eliminarAlerta($id);

    (new HistorialMovimientos())->registrar([
        'modulo'           => 'alertas_stock',
        'accion'           => 'eliminar',
        'id_registro'      => $id,
        'descripcion'      => "Se desactivó la alerta de stock mínimo de '{$material['nombre_material']}'",
        'datos_anteriores' => $material,
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
    <title>Eliminar Alerta de Stock</title>
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

    <a href="lista_alertas.php" class="btn-volver">
        ← Volver
    </a>

    <div class="card">

        <div class="card-header">
            Confirmar Eliminación
        </div>

        <div class="card-body">

            <p class="mensaje-eliminar">
                ¿Está seguro de eliminar la alerta del material:
                <strong>
                    <?= htmlspecialchars($material['nombre_material']) ?>
                </strong>?
            </p>

            <form method="POST">

                <div class="botones">

                    <a href="lista_alertas.php"
                       class="btn btn-cancelar">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-eliminar">
                        Eliminar
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