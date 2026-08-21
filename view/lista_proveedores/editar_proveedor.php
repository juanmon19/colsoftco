<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once "../../app/verificar_sesion.php";

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;

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

        $archivo = $_FILES['imagen'];

        // Extensiones permitidas
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

        $extension = strtolower(
            pathinfo($archivo['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $extensionesPermitidas)) {
            die("Formato de imagen no permitido.");
        }

        // Validar tamaño máximo: 2 MB
        if ($archivo['size'] > 2 * 1024 * 1024) {
            die("La imagen no puede superar los 2 MB.");
        }

        // Carpeta donde se guardan las imágenes
        $carpetaImagenes = __DIR__ . '/../../public/imagenes/proveedores/';

        // Crear carpeta si no existe
        if (!is_dir($carpetaImagenes)) {
            mkdir($carpetaImagenes, 0777, true);
        }

        // Nombre único
        $nuevoNombre = 'proveedor_' . $id . '_' . time() . '.' . $extension;

        $rutaDestino = $carpetaImagenes . $nuevoNombre;

        // Mover imagen
        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            die("No se pudo guardar la nueva imagen.");
        }

        // Eliminar imagen anterior
        if (!empty($imagen)) {

            $imagenAnterior = $carpetaImagenes . $imagen;

            if (file_exists($imagenAnterior)) {
                unlink($imagenAnterior);
            }
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
    <title>Editar Proveedor</title>

    <link rel="stylesheet" href="crud_proveedor.css">

</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../lista_proveedores/lista_proveedores.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Editar Proveedor</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

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

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>