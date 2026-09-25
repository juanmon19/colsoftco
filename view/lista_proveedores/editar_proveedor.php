<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once "../../app/verificar_sesion.php";

$logica = new ProveedorLogica();

// H2: el id viaja al nombre de archivo → entero estricto (adiós traversal).
$id = (int) ($_GET['id'] ?? 0);

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $imagen = $proveedor['imagen'] ?? null;

    // =====================================
    // CAMBIAR IMAGEN SI SE SELECCIONÓ UNA
    // =====================================

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

        require_once __DIR__ . '/../../app/upload_seguro.php';

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            die("Sesión inválida. Recarga la página.");
        }

        // Carpeta donde se guardan las imágenes
        $carpetaImagenes = __DIR__ . '/../../public/imagenes/proveedores/';

        // Re-codificada, nombre aleatorio, máximo 2 MB (H4: el contenido
        // nunca se valida solo por extensión).
        try {
            $nuevoNombre = guardarImagenSegura($_FILES['imagen'], $carpetaImagenes, 2 * 1024 * 1024, 'proveedor_' . $id);
        } catch (RuntimeException $e) {
            die(htmlspecialchars($e->getMessage()));
        }

        // Eliminar imagen anterior (basename interno impide salidas del dir)
        if (!empty($imagen)) {
            borrarArchivoSeguro($carpetaImagenes, $imagen);
        }

        $imagen = $nuevoNombre;
    }

    // =====================================
    // ACTUALIZAR PROVEEDOR
    // =====================================

    $logica->actualizarProveedor(
        $id,
        $_POST['nombre_empresa'],
        $_POST['contacto_nombre'],
        $_POST['contacto_apellido'],
        $_POST['telefono'],
        $_POST['email'],
        $_POST['nit'],
        $_POST['direccion'],
        $_POST['descripcion_empresa'],
        $imagen
    );

    // =====================================
    // HISTORIAL
    // =====================================

    (new HistorialMovimientos())->registrar([
        'modulo'          => 'proveedores',
        'accion'          => 'editar',
        'id_registro'     => $id,
        'descripcion'     => "Se actualizó el proveedor '{$_POST['nombre_empresa']}'",
        'datos_anteriores' => $proveedor,
        'datos_nuevos'    => $_POST,
        'usuario_nombre'  => trim(
            ($_SESSION['nombre'] ?? '') . ' ' .
                ($_SESSION['apellido'] ?? '')
        ) ?: 'Sistema',
    ]);

    header("Location: ../lista_proveedores/lista_proveedores.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../partials/seo.php'; ?>
    <title>Editar Proveedor</title>

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
            <a href="../lista_proveedores/lista_proveedores.php" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <div class="card">

            <div class="card-header">
                Editar Proveedor
            </div>

            <div class="card-body">

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">

                    <div class="form-grid">

                        <div class="campo full imagen-section">
                            <label>Imagen del Proveedor</label>

                            <div class="imagen-proveedor-row">

                                <div class="imagen-preview">
                                    <?php if (!empty($proveedor['imagen'])): ?>
                                        <img
                                            src="../../public/imagenes/proveedores/<?php echo htmlspecialchars($proveedor['imagen']); ?>"
                                            alt="Imagen del proveedor">
                                    <?php else: ?>
                                        <span class="imagen-placeholder">Sin imagen</span>
                                    <?php endif; ?>
                                </div>

                                <div class="imagen-input-wrapper">
                                    <label for="imagenInput" class="imagen-file-label">
                                        Seleccionar imagen
                                    </label>
                                    <input
                                        id="imagenInput"
                                        type="file"
                                        name="imagen"
                                        accept=".jpg,.jpeg,.png,.webp">
                                    <span id="imagenFileName" class="imagen-file-name">Ningún archivo seleccionado</span>

                                    <small>
                                        Selecciona una nueva imagen solamente si deseas reemplazar la actual.
                                        Máximo 2 MB.
                                    </small>
                                </div>

                            </div>
                        </div>

                        <div class="campo">
                            <label>Nombre Empresa</label>
                            <input
                                type="text"
                                name="nombre_empresa"
                                value="<?= htmlspecialchars($proveedor['nombre_empresa']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>NIT</label>
                            <input
                                type="text"
                                name="nit"
                                value="<?= htmlspecialchars($proveedor['nit']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Nombre Contacto</label>
                            <input
                                type="text"
                                name="contacto_nombre"
                                value="<?= htmlspecialchars($proveedor['contacto_nombre']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Apellido Contacto</label>
                            <input
                                type="text"
                                name="contacto_apellido"
                                value="<?= htmlspecialchars($proveedor['contacto_apellido']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Teléfono</label>
                            <input
                                type="text"
                                name="telefono"
                                value="<?= htmlspecialchars($proveedor['telefono']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Correo Electrónico</label>
                            <input
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($proveedor['email']) ?>"
                                required>
                        </div>

                        <div class="campo full">
                            <label>Dirección</label>
                            <input
                                type="text"
                                name="direccion"
                                value="<?= htmlspecialchars($proveedor['direccion']) ?>"
                                required>
                        </div>

                        <div class="campo full">
                            <label>Descripción Empresa</label>
                            <textarea
                                name="descripcion_empresa"
                                required><?= htmlspecialchars($proveedor['descripcion_empresa']) ?></textarea>
                        </div>

                    </div>

                    <div class="botones">

                        <button type="submit" class="btn btn-guardar">
                            Guardar Cambios
                        </button>

                        <a href="../lista_proveedores/lista_proveedores.php"
                            class="btn btn-volver-secundario">
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script>
        const imagenInput = document.getElementById('imagenInput');
        const imagenFileName = document.getElementById('imagenFileName');

        if (imagenInput && imagenFileName) {
            imagenInput.addEventListener('change', () => {
                imagenFileName.textContent = imagenInput.files.length ?
                    imagenInput.files[0].name :
                    'Ningún archivo seleccionado';
            });
        }
    </script>

    <script src="../../public/js/app.js" defer></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js" defer></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>