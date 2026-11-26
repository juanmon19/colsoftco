<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

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

require_once __DIR__ . '/../../app/registro_materia_prima.php';
require_once __DIR__ . '/../../app/alerta_stock.php';

$proveedorOrigen = filter_input(INPUT_GET, 'proveedor', FILTER_VALIDATE_INT) ?: 0;
$desdeStock = ($_GET['origen'] ?? '') === 'stock';
$puedeRegistrar = es_admin() || es_bodeguero();
$mensaje = $_SESSION['alta_material_ok'] ?? '';
unset($_SESSION['alta_material_ok']);
$mensajeTipo = 'ok';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensajeTipo = 'error';
    if (!$puedeRegistrar || !validarTokenCSRF(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '')) {
        http_response_code(403);
        $mensaje = 'No se pudo autorizar el registro. Revisa tu sesión y vuelve a intentarlo.';
    } else {
        try {
            $idNuevo = registrarMateriaPrima($conn, $_POST);
            try {
                (new AlertaStockLogica())->verificarStock($idNuevo);
            } catch (Throwable $e) {
                error_log('Alerta de stock inicial: ' . $e->getMessage());
            }
            if ($proveedorOrigen && (string) $proveedorOrigen === ($_POST['id_proveedor'] ?? '')) {
                $_SESSION['catalogo_proveedor_ok'] = 'Materia prima registrada y vinculada al proveedor.';
                header('Location: ../lista_proveedores/contactar_proveedor.php?id=' . $proveedorOrigen, true, 303);
            } elseif ($desdeStock) {
                header('Location: ../control_de_stock/inventario/lista_inventario.php', true, 303);
            } else {
                $_SESSION['alta_material_ok'] = 'Materia prima registrada correctamente.';
                header('Location: registromp.php', true, 303);
            }
            exit;
        } catch (DomainException $e) {
            $mensaje = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Registro de materia prima: ' . $e->getMessage());
            $mensaje = 'No se pudo guardar la materia prima. Vuelve a intentarlo.';
        }
    }
}
$unidades = $conn->query('SELECT id_unidad, nombre_unidad, sigla FROM unidades_medida ORDER BY nombre_unidad')->fetchAll();
$proveedores = $conn->query("SELECT id_proveedor, nombre_empresa FROM proveedores WHERE estado = 'activo' ORDER BY nombre_empresa")->fetchAll();
$valor = static fn($campo, $defecto = '') => is_string($_POST[$campo] ?? null) ? $_POST[$campo] : $defecto;
$esc = static fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$unidadActual = $valor('id_unidad', $unidades ? '' : 'nueva');
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
                    Registrar materia prima
                </div>

                <div class="form-body">
                    <?php if ($mensaje): ?>
                        <div class="mensaje mensaje-<?= htmlspecialchars($mensajeTipo) ?>" style="margin-bottom:16px;padding:10px 14px;border-radius:6px;font-weight:600;
                            <?= $mensajeTipo === 'ok' ? 'background:#e5f7ec;color:#1e7e42;' : 'background:#fdecea;color:#c0392b;' ?>">
                            <?= htmlspecialchars($mensaje) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$puedeRegistrar): ?>
                    <p>Tu rol tiene acceso de consulta. <a href="../inventario_materia_prima/inventario_materia_prima.php">Ver inventario de materias primas</a></p>
                    <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= $esc(generarTokenCSRF()) ?>">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label for="producto">Nombre de la materia prima</label>
                                <input id="producto" type="text" name="nombre_material" maxlength="100" value="<?= $esc($valor('nombre_material')) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="unidad">Unidad de medida</label>
                                <select id="unidad" name="id_unidad" required>
                                    <?php if ($unidades): ?><option value="">Selecciona una unidad</option><?php endif; ?>
                                    <?php foreach ($unidades as $u): ?>
                                    <option value="<?= (int) $u['id_unidad'] ?>" <?= $unidadActual === (string) $u['id_unidad'] ? 'selected' : '' ?>><?= $esc($u['nombre_unidad'] . ' (' . $u['sigla'] . ')') ?></option>
                                    <?php endforeach; ?>
                                    <option value="nueva" <?= $unidadActual === 'nueva' ? 'selected' : '' ?>>Crear una unidad</option>
                                </select>
                            </div>
                            <div class="form-group" data-unidad-nueva>
                                <label for="nombreUnidad">Nombre de la nueva unidad</label>
                                <input id="nombreUnidad" type="text" name="nombre_unidad" maxlength="20" placeholder="Por ejemplo, kilogramo" value="<?= $esc($valor('nombre_unidad')) ?>">
                            </div>
                            <div class="form-group" data-unidad-nueva>
                                <label for="sigla">Sigla de la nueva unidad</label>
                                <input id="sigla" type="text" name="sigla" maxlength="5" placeholder="Por ejemplo, kg" value="<?= $esc($valor('sigla')) ?>">
                            </div>
                            <div class="form-group">
                                <label for="cantidad">Cantidad inicial</label>
                                <input id="cantidad" type="number" name="stock_actual" min="0" step="0.01" value="<?= $esc($valor('stock_actual')) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="minimo">Stock mínimo</label>
                                <input id="minimo" type="number" name="stock_minimo" min="0" step="0.01" value="<?= $esc($valor('stock_minimo','0')) ?>" required>
                            </div>
                            <div class="form-group full">
                                <label for="proveedor">Proveedor (opcional)</label>
                                <select id="proveedor" name="id_proveedor">
                                    <option value="">Sin proveedor asignado</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= (int) $prov['id_proveedor'] ?>" <?= $valor('id_proveedor', (string) $proveedorOrigen) === (string) $prov['id_proveedor'] ? 'selected' : '' ?>><?= $esc($prov['nombre_empresa']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <hr class="form-divider">
                        <div class="form-actions">
                            <button type="reset" class="btn btn-outline">Limpiar</button>
                            <button type="submit" class="btn btn-primary">Registrar</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>

    <script>
    const unidad = document.getElementById('unidad');
    if (unidad) {
        function mostrarCamposUnidad() {
            const nueva = unidad.value === 'nueva';
            document.querySelectorAll('[data-unidad-nueva]').forEach(grupo => {
                grupo.style.display = nueva ? '' : 'none';
                grupo.querySelector('input').required = nueva;
            });
        }
        unidad.addEventListener('change', mostrarCamposUnidad);
        unidad.form.addEventListener('reset', () => setTimeout(mostrarCamposUnidad, 0));
        mostrarCamposUnidad();
    }
    </script>

</body>

</html>
