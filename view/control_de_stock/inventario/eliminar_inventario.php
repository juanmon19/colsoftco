<?php

require_once '../../../app/logica_inventario.php';
require_once __DIR__ . '/../../../app/HistorialMovimientos.php';

session_start();

$logica = new InventarioLogica();

if (!isset($_GET['id'])) {
    header("Location: lista_inventario.php");
    exit;
}

$id = (int) $_GET['id'];

$material = $logica->obtenerMaterial($id);

if (!$material) {
    header("Location: lista_inventario.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verificar si la materia prima está siendo utilizada en una receta
    if ($logica->materialTieneReceta($id)) {
        header("Location: lista_inventario.php?error=material_en_receta");
        exit;
    }

    // Solo se elimina si no está relacionada con una receta
    $eliminado = $logica->eliminarMaterial($id);

    if (!$eliminado) {
        header("Location: lista_inventario.php?error=no_se_pudo_eliminar");
        exit;
    }

    // Registrar en historial
    (new HistorialMovimientos())->registrar([
        'modulo'           => 'materia_prima',
        'accion'           => 'eliminar',
        'id_registro'      => $id,
        'descripcion'      => "Se eliminó la materia prima '{$material['nombre_material']}'",
        'datos_anteriores' => $material,
        'usuario_nombre'   => trim(
            ($_SESSION['nombre'] ?? '') . ' ' .
                ($_SESSION['apellido'] ?? '')
        ) ?: 'Sistema',
    ]);

    header("Location: lista_inventario.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Materia Prima</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2ff;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== HEADER ===== */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #0A1F44;
            padding: 10px 15px;
            min-height: 70px;
            position: relative;
            z-index: 100;
            border-bottom: 4px solid #D4AF37;
            gap: 10px;
        }

        .header .logo {
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .header .logo img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 2px solid #d4af37;
            background: white;
        }

        .header-title {
            flex: 1;
            text-align: center;
            overflow: hidden;
        }

        .header-title h1 {
            margin: 0;
            color: white;
            font-size: 15px;
            font-weight: bold;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .btn-logout {
            background-color: #D4AF37;
            color: #0A1F44;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: 0.3s;
            flex-shrink: 0;
        }

        .btn-logout:hover {
            background-color: #fff;
        }

        .contenedor {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            flex: 1;
        }

        .volver {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .15);
        }

        .card-header {
            background: #0A1F44;
            color: white;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 4px solid #D4AF37;
        }

        .card-body {
            padding: 30px;
        }

        h2 {
            color: #0A1F44;
        }

        .info {
            margin-top: 25px;
            line-height: 2;
            font-size: 16px;
        }

        .acciones {
            margin-top: 30px;
        }

        .btn-eliminar {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 15px;
        }

        .btn-eliminar:hover {
            background: #b91c1c;
        }

        .btn-cancelar {
            display: inline-block;
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
            margin-left: 10px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0D1B3E;
            color: #ffffff;
            width: 100%;
            margin-top: auto;
            flex-shrink: 0;
        }

        .footer-divider {
            width: 100%;
            height: 3px;
            background:
                linear-gradient(90deg, #C9A227, #E8C84A, #C9A227);
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 1.5rem 3rem 1.2rem;
            border-bottom: 1px solid rgba(201, 162, 39, 0.3);
        }

        .footer-brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #C9A227;
            letter-spacing: 2px;
            margin: 0 0 4px;
        }

        .footer-brand-sub {
            font-size: 11px;
            letter-spacing: 3px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            margin: 0 0 12px;
        }

        .footer-brand-desc {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.6;
            max-width: 280px;
            margin: 0;
        }

        .footer-col-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #C9A227;
            border-bottom: 2px solid #C9A227;
            display: inline-block;
            padding-bottom: 6px;
            margin-bottom: 16px;
        }

        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 3rem;
            background: rgba(0, 0, 0, 0.25);
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-bottom span {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
        }

        .footer-bottom strong {
            color: #C9A227;
        }

        @media (max-width: 768px) {
            .footer-top {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                padding: 1.5rem;
            }

            .footer-bottom {
                justify-content: center;
                text-align: center;
                padding: 0.8rem 1.5rem;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>
        <div class="header-title">
            <h1>Eliminar Materia Prima</h1>
        </div>
        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="contenedor">
        <a href="lista_inventario.php" class="volver">
            ← Volver a Inventario
        </a>

        <div class="card">
            <div class="card-header">
                Eliminar Materia Prima
            </div>
            <div class="card-body">
                <h2>¿Desea eliminar esta materia prima?</h2>
                <div class="info">
                    <strong>Material:</strong> <?= htmlspecialchars($material['nombre_material']) ?><br>
                    <strong>Stock Actual:</strong> <?= $material['stock_actual'] ?><br>
                    <strong>Stock Mínimo:</strong> <?= $material['stock_minimo'] ?><br>
                    <strong>ID Unidad:</strong> <?= $material['id_unidad'] ?><br>
                    <strong>ID Proveedor:</strong> <?= $material['id_proveedor'] ?>
                </div>

                <div class="acciones">
                    <form method="POST" style="display:inline;">
                        <button type="submit" class="btn-eliminar">Eliminar definitivamente</button>
                    </form>
                    <a href="lista_inventario.php" class="btn-cancelar">Cancelar</a>
                </div>
            </div>
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