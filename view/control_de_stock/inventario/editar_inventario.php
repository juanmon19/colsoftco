<?php

require_once '../../../config/conexion.php';

$db = new Conexion();
$conn = $db->getConnection();

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_material = $_POST['nombre_material'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];
    $id_unidad = $_POST['id_unidad'];
    $id_proveedor = $_POST['id_proveedor'];

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

    header("Location: lista_inventario.php");
    exit();
}

$sql = "SELECT * FROM materias_primas WHERE id_material = :id";

$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);

$material = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Inventario</title>
    <link rel="stylesheet" href="lista_inventario.css">
</head>

<body>

    <body>

        <div class="contenedor">

            <a href="../../inventario_materia_prima/inventario_materia_prima.php" class="volver">
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
                                value="<?= $material['nombre_material'] ?>" required>
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
                            <label>ID Unidad</label>
                            <input type="number" name="id_unidad" class="form-control"
                                value="<?= $material['id_unidad'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>ID Proveedor</label>
                            <input type="number" name="id_proveedor" class="form-control"
                                value="<?= $material['id_proveedor'] ?>" required>
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

    </body>

</html>