<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre_empresa = trim($_POST['nombre_empresa']);
    $contacto_nombre = trim($_POST['contacto_nombre']);
    $contacto_apellido = trim($_POST['contacto_apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $nit = trim($_POST['nit']);
    $direccion = trim($_POST['direccion']);
    $descripcion_empresa = trim($_POST['descripcion_empresa']);

try {

    // =====================================
    // VALIDAR IMAGEN (endurecido H4: MIME olfateado + getimagesize
    // + RE-ENCODE con GD; el polyglot con firma falsa no sobrevive)
    // =====================================

    require_once __DIR__ . '/../../app/upload_seguro.php';

    if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
        throw new Exception("Sesión inválida. Recarga la página.");
    }

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Debe seleccionar una imagen para el proveedor.");
    }

    $carpetaImagenes = __DIR__ . '/../../public/imagenes/proveedores/';

    // Máximo 5 MB, re-codificada a JPG/PNG/WEBP con nombre aleatorio.
    $nombreImagen = guardarImagenSegura($_FILES['imagen'], $carpetaImagenes, 5 * 1024 * 1024, 'proveedor');

    $rutaImagen = $carpetaImagenes . $nombreImagen;

    // =====================================
    // REGISTRAR PROVEEDOR
    // =====================================

    $logica = new ProveedorLogica();

    $resultado = $logica->registrarProveedor(
    $nombre_empresa,
    $contacto_nombre,
    $contacto_apellido,
    $telefono,
    $email,
    $nit,
    $direccion,
    $descripcion_empresa,
    $nombreImagen
);

    // Si falla el registro, eliminamos la imagen
    if (!$resultado) {
        borrarArchivoSeguro($carpetaImagenes, $nombreImagen);

        $mensaje = "Error al registrar proveedor";
    } else {

        $mensaje = "Proveedor registrado correctamente";

        (new HistorialMovimientos())->registrar([
            'modulo'       => 'proveedores',
            'accion'       => 'crear',
            'descripcion'  => "Se registró el proveedor '{$nombre_empresa}'",
            'datos_nuevos' => [
                'nombre_empresa'     => $nombre_empresa,
                'contacto_nombre'    => $contacto_nombre,
                'contacto_apellido'  => $contacto_apellido,
                'telefono'           => $telefono,
                'email'              => $email,
                'nit'                => $nit,
                'direccion'          => $direccion,
                'imagen'             => $nombreImagen
            ],
            'usuario_nombre' => trim(
                ($_SESSION['nombre'] ?? '') . ' ' .
                ($_SESSION['apellido'] ?? '')
            ) ?: 'Sistema',
        ]);
    }

} catch (Exception $e) {
    $mensaje = $e->getMessage();
}


    }

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../partials/seo.php'; ?>
    <title>Registro de Proveedores</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo -->
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="registrop.css">
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
<div class="contenedor-principal">

        <div class="acciones-superior">
            <a href="../lista_proveedores/lista_proveedores.php" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="card-formulario">

            <div class="card-header">
                <span class="barra-amarilla"></span>
                <h2>Registrar Nuevo Proveedor</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" id="formProveedor">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">

                <div class="field-group imagen-section">
                    <label>Logo / Imagen del Proveedor</label>

                    <div class="imagen-proveedor-row">

                        <div class="imagen-preview" id="imagenPreviewBox">
                            <span class="imagen-placeholder">Sin imagen</span>
                            <img id="imagenPreviewImg" src="" alt="Vista previa" style="display:none;">
                        </div>

                        <div class="imagen-input-wrapper">
                            <label for="imagen" class="imagen-file-label">
                                Seleccionar imagen
                            </label>
                            <input
                                type="file"
                                id="imagen"
                                name="imagen"
                                accept="image/jpeg,image/png,image/webp">
                            <span id="imagenFileName" class="imagen-file-name">Ningún archivo seleccionado</span>

                            <small>
                                Formatos permitidos: JPG, PNG o WEBP. Máximo 2 MB.
                            </small>
                        </div>

                    </div>
                </div>

                <div class="grid-form">

                    <div class="field-group">
                        <label>Nombre Empresa</label>
                        <input type="text" id="nombre_empresa" name="nombre_empresa" required>
                    </div>

                    <div class="field-group">
                        <label>NIT</label>
                        <input type="text" id="nit" name="nit" required>
                    </div>

                    <div class="field-group">
                        <label>Nombre Contacto</label>
                        <input type="text" id="contacto_nombre" name="contacto_nombre" required>
                    </div>

                    <div class="field-group">
                        <label>Apellido Contacto</label>
                        <input type="text" id="contacto_apellido" name="contacto_apellido" required>
                    </div>

                    <div class="field-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono" name="telefono" required>
                    </div>

                    <div class="field-group">
                        <label>Correo Electrónico</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                </div>

                <div class="field-group">
                    <label>Dirección</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>

                <div class="field-group">
                    <label>Descripción de la Empresa</label>
                    <textarea id="descripcion_empresa" name="descripcion_empresa" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-limpiar">
                        Limpiar
                    </button>

                    <button type="submit" class="btn-registrar">
                        Registrar
                    </button>
                </div>

            </form>

        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script src="../../public/js/validacionesProveedor.js"></script>

    <script>
        const imagenInput = document.getElementById('imagen');
        const imagenFileName = document.getElementById('imagenFileName');
        const imagenPreviewImg = document.getElementById('imagenPreviewImg');
        const imagenPlaceholder = document.querySelector('#imagenPreviewBox .imagen-placeholder');

        if (imagenInput) {
            imagenInput.addEventListener('change', () => {
                if (imagenInput.files.length) {
                    const archivo = imagenInput.files[0];
                    imagenFileName.textContent = archivo.name;

                    const lector = new FileReader();
                    lector.onload = (e) => {
                        imagenPreviewImg.src = e.target.result;
                        imagenPreviewImg.style.display = 'block';
                        imagenPlaceholder.style.display = 'none';
                    };
                    lector.readAsDataURL(archivo);
                } else {
                    imagenFileName.textContent = 'Ningún archivo seleccionado';
                    imagenPreviewImg.style.display = 'none';
                    imagenPlaceholder.style.display = 'block';
                }
            });
        }

        function mostrarError(id, mensaje) {
            const input = document.getElementById(id);
            input.style.border = "2px solid #dc3545";
            const errorExistente = input.parentNode.querySelector(".mensaje-error");

            if (errorExistente) {
                errorExistente.remove();
            }

            const error = document.createElement("small");
            error.className = "mensaje-error";
            error.textContent = mensaje;
            error.style.color = "#dc3545";
            error.style.display = "block";
            error.style.marginTop = "5px";
            error.style.fontSize = "13px";

            input.parentNode.appendChild(error);
        }

        
        function mostrarErrorImagen(mensaje) {
            const contenedor = document.querySelector(".imagen-input-wrapper");

            const errorExistente = contenedor.querySelector(".mensaje-error");

            if (errorExistente) {
                errorExistente.remove();
            }

            const error = document.createElement("small");
            error.className = "mensaje-error";
            error.textContent = mensaje;
            error.style.color = "#dc3545";
            error.style.display = "block";
            error.style.marginTop = "5px";
            error.style.fontSize = "13px";

            contenedor.appendChild(error);
        }

        function limpiarErrores() {
            document.querySelectorAll(".mensaje-error").forEach(e => e.remove());
            document.querySelectorAll("#formProveedor input, #formProveedor textarea")
                .forEach(campo => {
                    campo.style.border = "";
                });
        }

        const formulario = document.getElementById("formProveedor");

        formulario.addEventListener("submit", function(e) {
            const nombreEmpresa = document.getElementById("nombre_empresa").value;
            const nit = document.getElementById("nit").value;
            const nombre = document.getElementById("contacto_nombre").value;
            const apellido = document.getElementById("contacto_apellido").value;
            const telefono = document.getElementById("telefono").value;
            const correo = document.getElementById("email").value;
            const direccion = document.getElementById("direccion").value;
            const descripcion = document.getElementById("descripcion_empresa").value;

            limpiarErrores();
            
            if (imagenInput.files.length === 0) {
                mostrarErrorImagen("Debe seleccionar una imagen para el proveedor.");
                e.preventDefault();
                return;
            }

            if (!expresiones.empresa.test(nombreEmpresa)) {
                mostrarError("nombre_empresa", "Ingrese un nombre de empresa válido.");
                e.preventDefault();
                return;
            }
            if (!expresiones.nit.test(nit)) {
                mostrarError("nit", "Ingrese un NIT válido (8 a 12 dígitos).");
                e.preventDefault();
                return;
            }
            if (!expresiones.nombre.test(nombre)) {
                mostrarError("contacto_nombre", "Ingrese un nombre válido.");
                e.preventDefault();
                return;
            }
            if (!expresiones.apellido.test(apellido)) {
                mostrarError("contacto_apellido", "Ingrese un apellido válido.");
                e.preventDefault();
                return;
            }
            if (!expresiones.telefono.test(telefono)) {
                mostrarError("telefono", "Ingrese un teléfono válido de 10 dígitos.");
                e.preventDefault();
                return;
            }
            if (!expresiones.correo.test(correo)) {
                mostrarError("email", "Ingrese un correo electrónico válido.");
                e.preventDefault();
                return;
            }
            if (!expresiones.direccion.test(direccion)) {
                mostrarError("direccion", "Ingrese una dirección válida.");
                e.preventDefault();
                return;
            }
            if (descripcion.trim() !== "" && !expresiones.descripcion.test(descripcion)) {
                mostrarError("descripcion_empresa", "La descripción debe tener entre 10 y 300 caracteres.");
                e.preventDefault();
                return;
            }
        });
    </script>

    <script src="../../public/js/app.js" defer></script>

</body>

</html>