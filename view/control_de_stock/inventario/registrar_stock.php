<?php

require_once '../../../config/conexion.php';

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

    header("Location: lista_inventario.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registrar Materia Prima</title>
    <link rel="stylesheet" href="../controlstock.css">
</head>

<body>

    <header>
        <h1>Registrar Materia Prima</h1>
    </header>

    <div class="container">

        <a href="../control_de_stock.php" class="btn-volver">
            ← Volver
        </a>

        <div class="form-card">

            <div class="form-header">
                <span class="form-header-bar"></span>
                Registro de Materia Prima
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
                            <input type="number" name="stock_actual" required>
                        </div>

                        <div class="form-group">
                            <label>Unidad de medida</label>
                            <select name="id_unidad" required>
                                <option value="">Seleccione</option>

                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= $unidad['id_unidad'] ?>">
                                        <?= $unidad['nombre_unidad'] ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="form-group">
                            <label>Stock mínimo</label>
                            <input type="number" name="stock_minimo" required>
                        </div>
                        <div class="form-group full">
                            <label>Proveedor</label>

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
                        </select>
                    </div>

                    <div class="form-actions">

                        <a href="../control_de_stock.php" class="btn btn-outline">
                            Cancelar
                        </a>

                        <button type="submit" class="btn btn-primary">
                            Registrar
                        </button>

                    </div>

            </div>

            </form>

        </div>

    </div>

    </div>

</body>

</html>