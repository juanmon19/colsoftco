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
    SELECT m.id_material, m.nombre_material, m.id_unidad, m.id_proveedor,
           m.estado, u.sigla, u.nombre_unidad, p.nombre_empresa
    FROM materias_primas m
    LEFT JOIN unidades_medida u ON u.id_unidad = m.id_unidad
    LEFT JOIN proveedores p ON p.id_proveedor = m.id_proveedor
    ORDER BY m.nombre_material ASC
")->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$mensajeTipo = '';

/* Mensaje que llega tras el redirect (ver más abajo) */
if (isset($_GET['msg'], $_GET['tipo'])) {
    $mensaje = $_GET['msg'];
    $mensajeTipo = $_GET['tipo'] === 'ok' ? 'ok' : 'error';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // El producto llega por ID de catálogo: nombre y unidad se toman de la
    // BD (canónicos) y se IGNORA lo que mande el cliente en id_unidad.
    // Así una tela siempre queda en metros aunque manipulen el formulario.
    $idMaterialPost = (int) ($_POST['id_material'] ?? 0);
    $stock_actual = $_POST['stock_actual'] ?? '';
    $id_proveedor = (int) ($_POST['id_proveedor'] ?? 0);

    $stmtMat = $conn->prepare(
        "SELECT nombre_material, id_unidad FROM materias_primas WHERE id_material = :id LIMIT 1"
    );
    $stmtMat->execute([':id' => $idMaterialPost]);
    $matPost = $stmtMat->fetch(PDO::FETCH_ASSOC);

    $stmtProv = $conn->prepare(
        "SELECT COUNT(*) FROM proveedores WHERE id_proveedor = :id AND estado = 'activo'"
    );
    $stmtProv->execute([':id' => $id_proveedor]);
    $provOk = ((int) $stmtProv->fetchColumn()) > 0;

    // El formulario ya no pide stock mínimo, pero la columna en la BD es
    // NOT NULL sin valor por defecto, así que se envía 0 automáticamente.
    $stock_minimo = 0;

    if (!$matPost || !$provOk || $stock_actual === '' || !is_numeric($stock_actual) || (float) $stock_actual < 0) {
        $mensajeRedirect = 'Datos inválidos: verifica producto, cantidad y proveedor.';
        $tipoRedirect = 'error';
    } else {
        $nombre_material = $matPost['nombre_material'];
        $id_unidad = (int) $matPost['id_unidad'];
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
    <?php include __DIR__ . '/../partials/seo.php'; ?>
    <title>Registro Materias Primas</title>
    <link href="registromp.css" rel="stylesheet" />
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
<div class="page-body">
        <!-- FIX anidado: era <main> dentro de <main> (HTML inválido). Ahora es <div>. -->
        <div class="content registromp-contenido" style="margin: 0 auto; max-width: 1000px; width: 100%">
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

                                    <select id="producto" name="id_material" required>

                                        <option value="">-- Seleccione --</option>

                                        <?php foreach ($materias_primas as $material): ?>

                                            <option value="<?= (int) $material['id_material'] ?>"
                                                data-unidad="<?= (int) $material['id_unidad'] ?>"
                                                data-unidad-txt="<?= htmlspecialchars(($material['nombre_unidad'] ?? '') . ' (' . ($material['sigla'] ?? '') . ')') ?>"
                                                data-prov="<?= (int) $material['id_proveedor'] ?>">
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
                                        <option value="">-- Seleccione un producto --</option>

                                        <?php foreach ($unidades as $unidad): ?>

                                            <option value="<?= $unidad['id_unidad'] ?>">
                                                <?= htmlspecialchars($unidad['nombre_unidad']) ?> (<?= htmlspecialchars($unidad['sigla']) ?>)
                                            </option>

                                        <?php endforeach; ?>

                                    </select>
                                    <small id="avisoUnidad" class="aviso-unidad" style="display:none"></small>
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
                                <label for="proveedor">Proveedor</label>

                                <select id="proveedor" name="id_proveedor" required>

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
        </div>
    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js" defer></script>

    <script
        src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js"
        defer>
    </script>

    <script src="../../public/js/app.js" defer></script>

    <script>
    /* Interceptamos el envío del formulario para que el POST nunca
       quede como una entrada propia en el historial del navegador.
       Enviamos por fetch y usamos location.replace() hacia la URL
       final (registromp.php con el mensaje), reemplazando la entrada
       actual en vez de crear una nueva. Así, al presionar "atrás", el
       navegador siempre pide una página nueva al servidor en vez de
       reenviar el formulario o mostrar un estado desactualizado. */
    /* Selects enlazados: el proveedor filtra los productos de su catálogo
       (si solo suministra telas, no aparecen resortes) y el producto FIJA
       su unidad canónica (la tela solo permite metros). El servidor vuelve
       a validar todo, así que manipular el HTML no sirve. */
    const selProducto = document.getElementById('producto');
    const selUnidad = document.getElementById('unidad');
    const selProveedor = document.getElementById('proveedor');
    const avisoUnidad = document.getElementById('avisoUnidad');
    const todasUnidades = selUnidad ? Array.from(selUnidad.options).map(o => ({ v: o.value, t: o.text })) : [];

    function opcionesProducto() {
        return selProducto ? Array.from(selProducto.options).filter(o => o.value !== '') : [];
    }

    function filtrarProductos() {
        if (!selProducto || !selProveedor) return;
        const prov = selProveedor.value;
        let visibles = 0;
        opcionesProducto().forEach(o => {
            const ok = prov === '' || o.dataset.prov === prov;
            o.hidden = !ok;
            o.disabled = !ok; // Safari ignora hidden en <option>
            if (ok) visibles++;
        });
        // Si el producto elegido ya no lo surte ese proveedor, se resetea.
        const actual = selProducto.selectedOptions[0];
        if (actual && actual.value !== '' && actual.hidden) {
            selProducto.value = '';
            fijarUnidad();
        }
    }

    function fijarUnidad() {
        if (!selProducto || !selUnidad) return;
        const opt = selProducto.selectedOptions[0];
        if (!opt || opt.value === '') {
            selUnidad.innerHTML = '';
            todasUnidades.forEach(u => selUnidad.add(new Option(u.t, u.v)));
            selUnidad.value = '';
            if (avisoUnidad) avisoUnidad.style.display = 'none';
            return;
        }
        // Solo la unidad del producto: no se puede elegir otra.
        selUnidad.innerHTML = '';
        selUnidad.add(new Option(opt.dataset.unidadTxt + ' — fija del producto', opt.dataset.unidad));
        selUnidad.value = opt.dataset.unidad;
        if (avisoUnidad) {
            avisoUnidad.textContent = 'Unidad fijada por el producto (' + opt.dataset.unidadTxt + ').';
            avisoUnidad.style.display = 'block';
        }
        // Si el producto es de un solo proveedor y no hay proveedor elegido, se sugiere.
        if (selProveedor && selProveedor.value === '' && opt.dataset.prov) {
            selProveedor.value = opt.dataset.prov;
            filtrarProductos();
        }
    }

    if (selProducto && selUnidad && selProveedor) {
        selProveedor.addEventListener('change', filtrarProductos);
        selProducto.addEventListener('change', fijarUnidad);
    }

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