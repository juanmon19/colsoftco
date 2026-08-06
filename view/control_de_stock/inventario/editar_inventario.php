<?php

require_once '../../../config/conexion.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';

session_start();

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
<<<<<<< HEAD
<meta charset="UTF-8">
<title>Editar Inventario</title>
<link rel="stylesheet" href="lista_inventario.css">
=======
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inventario</title>
    <link rel="stylesheet" href="lista_inventario.css">
>>>>>>> 20280bdb7681e20d508d82ed4fe0a4b948dbf84f
</head>
<body>

<body>

<<<<<<< HEAD
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
                    <input
                        type="text"
                        name="nombre_material"
                        class="form-control"
                        value="<?= $material['nombre_material'] ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>Stock Actual</label>
                    <input
                        type="number"
                        step="0.01"
                        name="stock_actual"
                        class="form-control"
                        value="<?= $material['stock_actual'] ?>"
                        required>
                </div>
=======
    <header class="header">
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Editar Materia Prima</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

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
>>>>>>> 20280bdb7681e20d508d82ed4fe0a4b948dbf84f

                <div class="form-group">
                    <label>Stock Mínimo</label>
                    <input
                        type="number"
                        step="0.01"
                        name="stock_minimo"
                        class="form-control"
                        value="<?= $material['stock_minimo'] ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>ID Unidad</label>
                    <input
                        type="number"
                        name="id_unidad"
                        class="form-control"
                        value="<?= $material['id_unidad'] ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>ID Proveedor</label>
                    <input
                        type="number"
                        name="id_proveedor"
                        class="form-control"
                        value="<?= $material['id_proveedor'] ?>"
                        required>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar Cambios
                </button>

                <a href="lista_inventario.php"
                   class="btn btn-secondary">
                   Cancelar
                </a>

            </form>

        </div>

    </div>
<<<<<<< HEAD

</div>

</body>
=======
>>>>>>> 20280bdb7681e20d508d82ed4fe0a4b948dbf84f

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