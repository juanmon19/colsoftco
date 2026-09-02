<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

$conexion = new Conexion();
$dbConn = $conexion->getConnection();

$historial = $dbConn->query(
    "SELECT h.id, m.nombre_modelo, h.cantidad, h.fecha_fabricacion, h.usuario
     FROM historial_produccion h
     INNER JOIN modelos_colchon m ON m.id_modelo = h.id_modelo
     ORDER BY h.fecha_fabricacion DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Fabricación</title>
    <link href="historial_fabricacion.css" rel="stylesheet">
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
<div class="contenedor-historial">

        <div class="panel panel-historial">
            <div class="panel-header">Producciones registradas</div>

            <div class="panel-body">

                <?php if (count($historial) === 0): ?>

                    <p class="placeholder">
                        Aún no se ha registrado ninguna fabricación.
                    </p>

                <?php else: ?>

                    <table class="tabla-historial">
                        <thead>
                            <tr>
                                <th>Recibo</th>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $registro): ?>
                                <tr>
                                    <td class="celda-id">
                                        #<?= str_pad($registro['id'], 6, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($registro['fecha_fabricacion'])) ?>
                                    </td>
                                    <td class="celda-producto">
                                        <?= htmlspecialchars($registro['nombre_modelo']) ?>
                                    </td>
                                    <td>
                                        <?= (int) $registro['cantidad'] ?> uds.
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($registro['usuario']) ?>
                                    </td>
                                    <td>
                                        <a
                                            href="ver_recibo.php?id=<?= (int) $registro['id'] ?>"
                                            target="_blank"
                                            rel="noopener"
                                            class="btn-ver-pdf">
                                            Ver / Descargar PDF
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>

            </div>
        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script src="../../public/js/app.js"></script>

</body>

</html>