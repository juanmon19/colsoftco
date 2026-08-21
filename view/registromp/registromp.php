<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

/* Evita que el navegador restaure esta página desde su caché al
   presionar "atrás", lo que mostraría el formulario o el listado de
   materias primas desactualizado (por ejemplo, una recién registrada
   o eliminada que no se refleje). */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$db = new Conexion();
$conn = $db->getConnection();

$unidades = $conn->query("SELECT * FROM unidades_medida")->fetchAll(PDO::FETCH_ASSOC);
$proveedores = $conn->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);

/*
 * CONEXIÓN DEL DESPLEGABLE DE PRODUCTOS
 * Se obtienen las materias primas directamente
 * desde la base de datos.
 */
$materias_primas = $conn->query("
    SELECT id_material, nombre_material
    FROM materias_primas
")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$mensajeTipo = '';

/* Mensaje que llega tras el redirect (ver más abajo) */
if (isset($_GET['msg'], $_GET['tipo'])) {
    $mensaje = $_GET['msg'];
    $mensajeTipo = $_GET['tipo'] === 'ok' ? 'ok' : 'error';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_material = trim($_POST['nombre_material'] ?? '');
    $stock_actual = $_POST['stock_actual'] ?? '';
    $id_unidad = $_POST['id_unidad'] ?? '';
    $id_proveedor = $_POST['id_proveedor'] ?? '';

    // El formulario ya no pide stock mínimo, pero la columna en la BD es
    // NOT NULL sin valor por defecto, así que se envía 0 automáticamente.
    $stock_minimo = 0;

    if ($nombre_material === '' || $stock_actual === '' || $id_unidad === '' || $id_proveedor === '') {
        $mensajeRedirect = 'Por favor complete todos los campos obligatorios.';
        $tipoRedirect = 'error';
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

            $mensajeRedirect = "Materia prima '{$nombre_material}' registrada correctamente.";
            $tipoRedirect = 'ok';
        } catch (Exception $e) {
            $mensajeRedirect = 'Error al registrar la materia prima: ' . $e->getMessage();
            $tipoRedirect = 'error';
        }
    }

    /* Redirigimos siempre (patrón POST-Redirect-GET), llevando el
       mensaje por la URL. Así el POST nunca queda como una entrada
       "viva" del historial: al recargar o volver con "atrás", el
       navegador siempre hace un GET nuevo, sin reenviar el formulario
       ni mostrar datos desactualizados. */
    header("Location: registromp.php?msg=" . urlencode($mensajeRedirect) . "&tipo=" . urlencode($tipoRedirect));
    exit;
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
                        <div class="mensaje mensaje-<?= htmlspecialchars($mensajeTipo) ?>" style="margin-bottom:16px;padding:10px 14px;border-radius:6px;font-weight:600;
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

                                        <?php foreach ($materias_primas as $material): ?>

                                            <option value="<?= htmlspecialchars($material['nombre_material']) ?>">
                                                <?= htmlspecialchars($material['nombre_material']) ?>
                                            </option>

                                        <?php endforeach; ?>

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
                                <label for="cantidad">Cantidad</label>
                                <input
                                    type="number"
                                    id="cantidad"
                                    name="stock_actual"
                                    min="0"
                                    step="0.01"
                                    placeholder="Ingrese la cantidad"
                                    required
                                />
                            </div>

                            <div class="form-group full">
                                <label>Proveedor</label>

                                <select name="id_proveedor" required>

                                    <option value="">Seleccione</option>

                                    <?php foreach ($proveedores as $proveedor): ?>

                                        <option value="<?= $proveedor['id_proveedor'] ?>">
                                            <?= htmlspecialchars($proveedor['nombre_empresa']) ?> -
                                            <?= htmlspecialchars($proveedor['descripcion_empresa']) ?>
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
            <span>
                © 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.
            </span>

            <span>
                Desarrollado por <strong>Equipo SENA</strong>
            </span>
        </div>
    </footer>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>

    <script
        src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js"
        defer>
    </script>

    <script src="../../public/js/app.js"></script>

    <script>
    /* Interceptamos el envío del formulario para que el POST nunca
       quede como una entrada propia en el historial del navegador.
       Enviamos por fetch y usamos location.replace() hacia la URL
       final (registromp.php con el mensaje), reemplazando la entrada
       actual en vez de crear una nueva. Así, al presionar "atrás", el
       navegador siempre pide una página nueva al servidor en vez de
       reenviar el formulario o mostrar un estado desactualizado. */
    const regForm = document.getElementById('regForm');
    const btnRegistrar = regForm ? regForm.querySelector('button[type="submit"]') : null;

    if (regForm) {
        regForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (btnRegistrar) btnRegistrar.disabled = true;

            const datos = new FormData(regForm);

            fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: datos
            })
            .then(function (respuesta) {
                if (!respuesta.ok) {
                    throw new Error('Respuesta no válida del servidor');
                }
                window.location.replace(respuesta.url || 'registromp.php');
            })
            .catch(function () {
                alert('Ocurrió un error al registrar la materia prima. Intenta de nuevo.');
                if (btnRegistrar) btnRegistrar.disabled = false;
            });
        });
    }

    /* Refuerzo: si el navegador restaura esta página desde su bfcache
       (por ejemplo al volver con "atrás"), forzamos una recarga real
       para que PHP vuelva a consultar el estado actual de la base de
       datos en vez de mostrar una copia desactualizada. */
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>

</body>

</html>