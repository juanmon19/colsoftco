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
ORDER BY id_material ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link rel="stylesheet" href="lista_inventario.css">
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Inventario de Materias Primas</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>


    <div class="container">

        <a class="btn-volver" href="../control_de_stock.php">
            ← Volver
        </a>

        <div class="table-responsive">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Material</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
                
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

    </div>

    <footer>
        <div class="footer-divider"></div>
        <div class="footer-top">
            <div>
                <p class="footer-brand-name">COLSOFTCO</p>
                <p class="footer-brand-sub">Sistema de Gestión</p>
                <p class="footer-brand-desc">Sistema de gestión y administración de materias primas para Max&Flex. Eficiencia en inventarios y movimientos empresariales.</p>
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

</body>
</html>