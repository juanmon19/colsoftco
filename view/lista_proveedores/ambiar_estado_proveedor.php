<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once "../../app/verificar_sesion.php";

/* Evita que el navegador muestre esta página desde su caché (bfcache)
   al presionar "atrás", lo que haría reaparecer la confirmación con
   datos desactualizados aunque el estado ya haya cambiado en la BD. */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$logica = new ProveedorLogica();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header("Location: lista_proveedores.php");
    exit();
}

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    header("Location: lista_proveedores.php");
    exit();
}

/* El estado hacia el que va a cambiar (lo contrario del actual) */
$nuevoEstado = $proveedor['estado'] === 'activo' ? 'inactivo' : 'activo';
$esDeshabilitar = $nuevoEstado === 'inactivo';

/* A qué pestaña de la lista regresar después de guardar */
$volverA = $_GET['volver'] ?? ($proveedor['estado'] ?? 'activo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->cambiarEstadoProveedor($id, $nuevoEstado);

    (new HistorialMovimientos())->registrar([
        'modulo'           => 'proveedores',
        'accion'           => $esDeshabilitar ? 'deshabilitar' : 'habilitar',
        'id_registro'      => $id,
        'descripcion'      => $esDeshabilitar
            ? "Se deshabilitó el proveedor '{$proveedor['nombre_empresa']}'"
            : "Se habilitó el proveedor '{$proveedor['nombre_empresa']}'",
        'datos_anteriores' => ['estado' => $proveedor['estado']],
        'datos_nuevos'     => ['estado' => $nuevoEstado],
        'usuario_nombre'   => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
    ]);

    header("Location: ../lista_proveedores/lista_proveedores.php?estado=" . urlencode($volverA));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<script>/* Aplica el tema guardado (claro/oscuro) antes de pintar */(function(){try{if(localStorage.getItem('colsoftco_tema')==='oscuro'){document.documentElement.setAttribute('data-tema','oscuro');}}catch(e){}})();</script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $esDeshabilitar ? 'Deshabilitar' : 'Habilitar' ?> Proveedor</title>

<!-- Orden estándar: global -> layout (shell) -> módulo -->
<link rel="stylesheet" href="../../public/css/global.css">
<link rel="stylesheet" href="../../public/css/layout.css">
<link rel="stylesheet" href="crud_proveedor.css">
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

    <div class="contenedor">

        <div class="acciones-superior">
            <a href="../lista_proveedores/lista_proveedores.php?estado=<?= urlencode($volverA) ?>" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <div class="card">

            <div class="card-header">
                <?= $esDeshabilitar ? 'Deshabilitar Proveedor' : 'Habilitar Proveedor' ?>
            </div>

            <div class="card-body">

                <h2>
                    <?= $esDeshabilitar
                        ? '¿Deseas deshabilitar este proveedor?'
                        : '¿Deseas habilitar nuevamente este proveedor?' ?>
                </h2>

                <?php if ($esDeshabilitar): ?>
                    <p style="color:#64748b; margin-top:8px;">
                        No se eliminará ningún dato. El proveedor pasará a la pestaña de
                        "Deshabilitados" y podrás habilitarlo de nuevo cuando quieras.
                    </p>
                <?php endif; ?>

                <br>

                <p>
                    <strong>Empresa:</strong>
                    <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                </p>

                <p>
                    <strong>NIT:</strong>
                    <?= htmlspecialchars($proveedor['nit']) ?>
                </p>

                <p>
                    <strong>Contacto:</strong>
                    <?= htmlspecialchars($proveedor['contacto_nombre']) ?>
                    <?= htmlspecialchars($proveedor['contacto_apellido']) ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?= htmlspecialchars($proveedor['email']) ?>
                </p>

                <p>
                    <strong>Teléfono:</strong>
                    <?= htmlspecialchars($proveedor['telefono']) ?>
                </p>

                <div class="botones">

                    <form method="POST" id="formEstado">
                        <button type="submit" class="btn btn-guardar" id="btnConfirmar">
                            <?= $esDeshabilitar ? 'Sí, deshabilitar' : 'Sí, habilitar' ?>
                        </button>
                    </form>

                    <a href="../lista_proveedores/lista_proveedores.php?estado=<?= urlencode($volverA) ?>"
                       class="btn btn-volver-secundario">
                        Cancelar
                    </a>

                </div>

            </div>

        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script>
    /* Interceptamos el envío del formulario para que la confirmación
       NUNCA quede como una entrada propia en el historial del navegador.
       En vez de una navegación normal (que crea una entrada nueva a la
       que "atrás" podría volver), enviamos el POST por fetch y luego
       usamos location.replace(): esto sustituye la entrada actual
       (la pregunta) por la lista de proveedores. Resultado: al presionar
       "atrás" desde la lista, se salta directo a la página anterior a
       la pregunta, sin volver a mostrarla jamás. */
    const destino = "../lista_proveedores/lista_proveedores.php?estado=<?= urlencode($volverA) ?>";
    const formEstado = document.getElementById('formEstado');
    const btnConfirmar = document.getElementById('btnConfirmar');

    formEstado.addEventListener('submit', function (e) {
        e.preventDefault();
        btnConfirmar.disabled = true;

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (respuesta) {
            if (!respuesta.ok) {
                throw new Error('Respuesta no válida del servidor');
            }
            window.location.replace(destino);
        })
        .catch(function () {
            alert('Ocurrió un error al guardar los cambios. Intenta de nuevo.');
            btnConfirmar.disabled = false;
        });
    });

    /* Refuerzo adicional: si de todos modos el navegador restaura esta
       página desde su bfcache, forzamos una recarga real. */
    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    </script>

    <script src="../../public/js/app.js"></script>
    
</body>
</html>