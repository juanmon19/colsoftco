<?php

require_once __DIR__ . '/../../../app/verificar_sesion.php';
require_once '../../../config/conexion.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../../app/alerta_stock.php';

$db = new Conexion();
$conn = $db->getConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header("Location: lista_inventario.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!stockValido($_POST['stock_minimo'] ?? null) || !stockValido($_POST['stock_actual'] ?? null)) {
        http_response_code(422);
        exit('El stock debe ser un número mayor o igual a cero con hasta dos decimales.');
    }

    // Guardamos el estado ANTES de actualizar, para el historial
    $stmtAntes = $conn->prepare("SELECT * FROM materias_primas WHERE id_material = :id");
    $stmtAntes->execute([':id' => $id]);
    $materialAntes = $stmtAntes->fetch(PDO::FETCH_ASSOC);

    $nombre_material = $_POST['nombre_material'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_unidad = $_POST['id_unidad'];
    $id_proveedor = ($_POST['id_proveedor'] ?? '') === '' ? null : $_POST['id_proveedor'];

    $sql = "UPDATE materias_primas
            SET
                nombre_material = :nombre_material,
                stock_actual = :stock_actual,
                stock_minimo = :stock_minimo,
                id_unidad = :id_unidad,
                id_proveedor = :id_proveedor
            WHERE id_material = :id";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':nombre_material' => $nombre_material,
        ':stock_actual' => $stock_actual,
        ':stock_minimo' => $stock_minimo,
        ':id_unidad' => $id_unidad,
        ':id_proveedor' => $id_proveedor,
        ':id' => $id
    ]);

    (new HistorialMovimientos())->registrar([
        'modulo'           => 'materia_prima',
        'accion'           => 'editar',
        'id_registro'      => $id,
        'descripcion'      => "Se actualizó la materia prima '{$nombre_material}'",
        'datos_anteriores' => $materialAntes ?: null,
        'datos_nuevos'     => [
            'nombre_material' => $nombre_material,
            'stock_actual'    => $stock_actual,
            'stock_minimo'    => $stock_minimo,
            'id_unidad'       => $id_unidad,
            'id_proveedor'    => $id_proveedor,
        ],
        'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
    ]);

    // Revisa si con este cambio el material queda en (o sale de) alerta de stock mínimo
    (new AlertaStockLogica())->verificarStock($id);

    header("Location: lista_inventario.php");
    exit();
}

$sql = "SELECT * FROM materias_primas WHERE id_material = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);

$material = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$material) {
    http_response_code(404);
    exit('Materia prima no encontrada.');
}
$unidades = $conn->query('SELECT id_unidad, nombre_unidad, sigla FROM unidades_medida ORDER BY nombre_unidad')->fetchAll();
$stmt = $conn->prepare("SELECT id_proveedor, nombre_empresa FROM proveedores WHERE estado = 'activo' OR id_proveedor = ? ORDER BY nombre_empresa");
$stmt->execute([$material['id_proveedor']]);
$proveedores = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../../partials/seo.php'; ?>
    <title>Editar Materia Prima</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo -->
    <link rel="stylesheet" href="../../../public/css/global.css">
    <link rel="stylesheet" href="../../../public/css/layout.css">
    <link rel="stylesheet" href="lista_inventario.css">
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

    <div class="contenedor">

        <a href="lista_inventario.php" class="volver">
            ← Volver al Inventario
        </a>

        <div class="card">

            <div class="card-header">
                Editar Materia Prima
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="form-group">
                        <label>Nombre Material</label>
                        <input type="text" name="nombre_material" class="form-control"
                            value="<?= htmlspecialchars($material['nombre_material']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Stock Actual</label>
                        <input type="number" step="0.01" name="stock_actual" class="form-control"
                            value="<?= $material['stock_actual'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number" step="0.01" name="stock_minimo" class="form-control"
                            value="<?= $material['stock_minimo'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="unidad">Unidad de medida</label>
                        <select id="unidad" name="id_unidad" class="form-control" required>
                            <?php foreach ($unidades as $u): ?>
                            <option value="<?= (int) $u['id_unidad'] ?>" <?= $material['id_unidad'] == $u['id_unidad'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre_unidad'] . ' (' . $u['sigla'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="proveedor">Proveedor (opcional)</label>
                        <select id="proveedor" name="id_proveedor" class="form-control">
                            <option value="">Sin proveedor asignado</option>
                            <?php foreach ($proveedores as $prov): ?>
                            <option value="<?= (int) $prov['id_proveedor'] ?>" <?= $material['id_proveedor'] == $prov['id_proveedor'] ? 'selected' : '' ?>><?= htmlspecialchars($prov['nombre_empresa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
                    </button>

                    <a href="lista_inventario.php" class="btn btn-secondary">
                        Cancelar
                    </a>

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