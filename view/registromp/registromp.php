<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$db = new Conexion();
$conn = $db->getConnection();

$unidades = $conn->query("SELECT * FROM unidades_medida")->fetchAll(PDO::FETCH_ASSOC);
$proveedores = $conn->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$mensajeTipo = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_material = trim($_POST['nombre_material'] ?? '');
    $stock_actual = $_POST['stock_actual'] ?? '';
    $stock_minimo = $_POST['stock_minimo'] ?? '';
    $id_unidad = $_POST['id_unidad'] ?? '';
    $id_proveedor = $_POST['id_proveedor'] ?? '';

    if ($nombre_material === '' || $stock_actual === '' || $stock_minimo === '' || $id_unidad === '' || $id_proveedor === '') {
        $mensaje = 'Por favor complete todos los campos obligatorios.';
        $mensajeTipo = 'error';
    } else {
        try {
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

            (new HistorialMovimientos())->registrar([
                'modulo'       => 'materia_prima',
                'accion'       => 'crear',
                'id_registro'  => $conn->lastInsertId(),
                'descripcion'  => "Se registró la materia prima '{$nombre_material}' con stock inicial de {$stock_actual}",
                'datos_nuevos' => [
                    'nombre_material' => $nombre_material,
                    'stock_actual'    => $stock_actual,
                    'stock_minimo'    => $stock_minimo,
                    'id_unidad'       => $id_unidad,
                    'id_proveedor'    => $id_proveedor,
                ],
                'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
            ]);

            $mensaje = "Materia prima '{$nombre_material}' registrada correctamente.";
            $mensajeTipo = 'ok';
        } catch (Exception $e) {
            $mensaje = 'Error al registrar la materia prima: ' . $e->getMessage();
            $mensajeTipo = 'error';
        }
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro Materias Primas</title>
    <link href="registromp.css" rel="stylesheet" />
</head>

<body>
    <header>
        <div class="logo">
            <a class="logo" href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Registro de Materias Primas</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="page-body">
        <main class="content" style="margin: 0 auto; max-width: 1000px; width: 100%">
            <div class="form-card">
                <div class="form-header">
                    <span class="form-header-bar"></span>
                    Registra Producto
                </div>

                <div class="form-body">
                    <?php if ($mensaje): ?>
                        <div class="mensaje mensaje-<?= $mensajeTipo ?>" style="margin-bottom:16px;padding:10px 14px;border-radius:6px;font-weight:600;
                            <?= $mensajeTipo === 'ok' ? 'background:#e5f7ec;color:#1e7e42;' : 'background:#fdecea;color:#c0392b;' ?>">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <form id="regForm" method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="producto">Producto</label>
                                <div class="select-wrap">
                                    <select id="producto" name="nombre_material" required>
                                        <option value="">-- Seleccione --</option>
                                        <option>Espuma de poliuretano</option>
                                        <option>Tela Jacquard</option>
                                        <option>Resortes Bonell</option>
                                        <option>Espuma</option>
                                        <option>Fieltro aislante</option>
                                        <option>Pegante industrial</option>
                                        <option>Hilo de costura</option>
                                        <option>Espuma viscoelástica</option>
                                        <option>Tela antideslizante</option>
                                        <option>Borde perimetral</option>
                                        <option>Empaque plástico</option>
                                        <option>Tela</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="unidad">Unidad de medida</label>
                                <div class="select-wrap">
                                    <select id="unidad" name="id_unidad" required>
                                        <option value="">-- Seleccione --</option>
                                        <?php foreach ($unidades as $unidad): ?>
                                            <option value="<?= $unidad['id_unidad'] ?>">
                                                <?= htmlspecialchars($unidad['nombre_unidad']) ?> (<?= htmlspecialchars($unidad['sigla']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cantidad">Cantidad (stock inicial)</label>
                                <input type="number" id="cantidad" name="stock_actual" min="0" step="0.01" placeholder="Ingrese la cantidad" required />
                            </div>

                            <div class="form-group">
                                <label for="stock_minimo">Stock mínimo</label>
                                <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="0.01" placeholder="Cantidad mínima antes de alertar" required />
                            </div>

                            <div class="form-group full">
                                <label>Proveedor</label>
                                <select name="id_proveedor" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($proveedores as $proveedor): ?>
                                        <option value="<?= $proveedor['id_proveedor'] ?>">
                                            <?= htmlspecialchars($proveedor['nombre_empresa']) ?> - <?= htmlspecialchars($proveedor['descripcion_empresa']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="form-divider" />

                        <div class="form-actions">
                            <button type="reset" class="btn btn-outline">Limpiar</button>
                            <button type="submit" class="btn btn-primary">Registrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
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

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script src="../../public/js/app.js"></script>
</body>

</html>