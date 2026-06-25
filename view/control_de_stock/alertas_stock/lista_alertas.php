<?php

require_once '../../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$alertas = $logica->obtenerAlertasStock();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Alertas de Stock</title>

    <link rel="stylesheet" href="alertas.css">
</head>

<body>

    <header>
        <h1>Alertas de Stock</h1>
    </header>

    <div class="container">

        <a href="../control_de_stock.php" class="btn-volver">
            ← Volver
        </a>

        <table>
           <table>

    <thead>
        <tr>
            <th>Material</th>
            <th>Stock Actual</th>
            <th>Stock Mínimo</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>

        <?php foreach ($alertas as $item): ?>

        <tr>

            <td><?= $item['nombre_material'] ?></td>

            <td><?= $item['stock_actual'] ?></td>

            <td><?= $item['stock_minimo'] ?></td>

            <td>
                <span class="alerta">
                    STOCK BAJO
                </span>
            </td>

            <td>

                <a href="editar_alerta.php?id=<?= $item['id_material'] ?>"
                   class="btn-editar">
                   Editar
                </a>

                <a href="eliminar_alerta.php?id=<?= $item['id_material'] ?>"
                   class="btn-eliminar">
                   Eliminar
                </a>

            </td>

        </tr>

        <?php endforeach; ?>

    </tbody>

</table>
          
</body>

</html>