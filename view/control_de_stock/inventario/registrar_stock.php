<?php

require_once '../../../config/conexion.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../../app/alerta_stock.php';

session_start();

$db = new Conexion();
$conn = $db->getConnection();

$unidades = $conn->query("SELECT * FROM unidades_medida")->fetchAll(PDO::FETCH_ASSOC);
$proveedores = $conn->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_material = $_POST['nombre_material'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_unidad = $_POST['id_unidad'];
    $id_proveedor = $_POST['id_proveedor'];

    $sql = "INSERT INTO materias_primas
            (
                nombre_material,
                stock_actual,
                stock_minimo,
                id_unidad,
                id_proveedor
            )
            VALUES
            (
                :nombre_material,
                :stock_actual,
                :stock_minimo,
                :id_unidad,
                :id_proveedor
            )";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':nombre_material' => $nombre_material,
        ':stock_actual' => $stock_actual,
        ':stock_minimo' => $stock_minimo,
        ':id_unidad' => $id_unidad,
        ':id_proveedor' => $id_proveedor
    ]);

    $idNuevoMaterial = $conn->lastInsertId();
    (new HistorialMovimientos())->registrar([
        'modulo'       => 'materia_prima',
        'accion'       => 'crear',
        'id_registro'  => $idNuevoMaterial,
        'descripcion'  => "Se registró la materia prima '{$nombre_material}' con stock inicial de {$stock_actual}",
        'datos_nuevos' => [
            'nombre_material' => $nombre_material,
            'stock_actual'    => $stock_actual,
            'stock_minimo'    => $stock_minimo,
            'id_unidad'       => $id_unidad,
            'id_proveedor'    => $id_proveedor,
        ],
        'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
    ]);

    // Si el material ya nace con stock por debajo del mínimo, avisa de inmediato
    (new AlertaStockLogica())->verificarStock($idNuevoMaterial);

    header("Location: lista_inventario.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Materia Prima</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo -->
    <link rel="stylesheet" href="../../../public/css/global.css">
    <link rel="stylesheet" href="../../../public/css/layout.css">
    <link rel="stylesheet" href="../controlstock.css">
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

    <div class="page-body">

        <div class="content" style="margin: 0 auto; max-width: 1000px; width: 100%;">

            <!-- ÚNICO BOTÓN DE VOLVER (Alineado a la izquierda sin estirarse) -->
            <div style="text-align: left; margin-bottom: 20px;">

                <a href="../control_de_stock.php" class="btn-volver">
                    ← Volver
                </a>
            </div>

            <div class="form-card">

                <div class="form-header">
                    <span class="form-header-bar"></span>
                    Registro stock
                </div>

                <div class="form-body">

                    <form method="POST">

                        <div class="form-grid">

                            <div class="form-group">
                                <label>Nombre del material</label>
                                <input type="text" name="nombre_material" required>
                            </div>

                            <div class="form-group">
                                <label>Stock actual</label>
                                <input type="number" name="stock_actual" step="0.01" required>
                            </div>

                            <div class="form-group">
                                <label>Unidad de medida</label>
                                <div class="select-wrap">
                                    <select name="id_unidad" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach ($unidades as $unidad): ?>
                                            <option value="<?= $unidad['id_unidad'] ?>">
                                                <?= $unidad['nombre_unidad'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Stock mínimo</label>
                                <input type="number" name="stock_minimo" step="0.01" required>
                            </div>

                            <div class="form-group full">
                                <label>Proveedor</label>
                                <div class="select-wrap">
                                    <select name="id_proveedor" required>
                                        <option value="">Seleccione</option>
                                        <?php foreach ($proveedores as $proveedor): ?>
                                            <option value="<?= $proveedor['id_proveedor'] ?>">
                                                <?= $proveedor['nombre_empresa'] ?>
                                                - <?= $proveedor['descripcion_empresa'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <div class="form-actions" style="margin-top: 20px;">
                            <a href="../control_de_stock.php" class="btn btn-outline" style="text-decoration: none;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Registrar
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../../partials/scripts_layout_footer.php'; ?>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>