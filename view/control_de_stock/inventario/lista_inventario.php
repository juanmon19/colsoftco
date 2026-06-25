<?php

require_once('../../../config/conexion.php');

$db = new Conexion();
$conn = $db->getConnection();

$sql = "
SELECT
    id_material,
    nombre_material,
    stock_actual,
    stock_minimo
FROM materias_primas
ORDER BY nombre_material ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Inventario</title>
    <link rel="stylesheet" href="lista_inventario.css">
</head>

<body>

    <header>
        <h1>Inventario de Materias Primas</h1>
    </header>

    <div class="container">

        <a class="btn-volver" href="../control_de_stock.php">
    ← Volver
</a>

        <table>

            <tr>
                <th>ID</th>
                <th>Material</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <td>
            </td>
            <?php foreach ($materiales as $m): ?>

                <tr>

                    <td><?= $m['id_material'] ?></td>

                    <td><?= htmlspecialchars($m['nombre_material']) ?></td>

                    <td><?= $m['stock_actual'] ?></td>

                    <td><?= $m['stock_minimo'] ?></td>

                    <td>
                        <?php if ($m['stock_actual'] <= $m['stock_minimo']): ?>
                            <span class="alerta">STOCK BAJO</span>
                        <?php else: ?>
                            <span class="normal">DISPONIBLE</span>
                        <?php endif; ?>
                    </td>

                    <td class="acciones">

                        <a class="btn-editar" href="editar_inventario.php?id=<?= $m['id_material'] ?>">
                            Editar
                        </a>

                        <a href="eliminar_inventario.php?id=<?= $m['id_material'] ?>" class="btn-eliminar">
                            Eliminar
                        </a>
                    </td>


                </tr>

            <?php endforeach; ?>

        </table>

    </div>

</body>

</html>