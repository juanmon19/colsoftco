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
</head>

<body>

    <header>
        <a class="logo" href="../receta_de_colchones/receta_colchones.php">
            <img src="../../public/imagenes/logo.png" alt="logo">
        </a>
        <span class="header-title">Historial de Fabricación</span>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

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

    <footer>
        <div class="footer-divider"></div>

        <div class="footer-top">
            <div>
                <p class="footer-brand-name">COLSOFTCO</p>
                <p class="footer-brand-sub">Sistema de Gestión</p>
                <p class="footer-brand-desc">
                    Sistema de gestión y administración de materias primas para Max&Flex.
                    Eficiencia en inventarios y movimientos empresariales.
                </p>
            </div>

            <div>
                <p class="footer-col-title">Contacto</p>
                <div class="footer-contact-item">📍 Bogotá, Colombia</div>
                <div class="footer-contact-item">✉ contacto@colsoftco.com</div>
                <div class="footer-contact-item">📞 +57 (1) 234-5678</div>
                <div class="footer-contact-item">🕐 Lun – Vie: 8:00 am – 6:00 pm</div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.</span>
            <span>Desarrollado por <strong>Equipo SENA</strong></span>
        </div>
    </footer>

    <script src="../../public/js/app.js"></script>

</body>

</html>
