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
    // VALIDAR IMAGEN
    // =====================================

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Debe seleccionar una imagen para el proveedor.");
    }

    $imagen = $_FILES['imagen'];

    // Máximo 5 MB
    if ($imagen['size'] > 5 * 1024 * 1024) {
        throw new Exception("La imagen no puede superar los 5 MB.");
    }

    // Validar que realmente sea una imagen
    $tipoImagen = getimagesize($imagen['tmp_name']);

    if ($tipoImagen === false) {
        throw new Exception("El archivo seleccionado no es una imagen válida.");
    }

    // Validar extensión
    $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));

    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $extensionesPermitidas, true)) {
        throw new Exception("Solo se permiten imágenes JPG, PNG o WEBP.");
    }

    // =====================================
    // CREAR NOMBRE ÚNICO
    // =====================================

    $nombreImagen = 'proveedor_' . uniqid() . '.' . $extension;

    // =====================================
    // RUTA DE DESTINO
    // =====================================

    $carpetaImagenes = __DIR__ . '/../../public/imagenes/proveedores/';

    // Crear carpeta si no existe
    if (!is_dir($carpetaImagenes)) {
        mkdir($carpetaImagenes, 0755, true);
    }

    $rutaImagen = $carpetaImagenes . $nombreImagen;

    // =====================================
    // GUARDAR IMAGEN
    // =====================================

    if (!move_uploaded_file($imagen['tmp_name'], $rutaImagen)) {
        throw new Exception("No fue posible guardar la imagen.");
    }

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
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen);
        }

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
    <title>Registro de Proveedores</title>
    <link rel="stylesheet" href="registrop.css">
</head>

<body>

    <header>
        <div class="logo">
            <a href="../panel_admin/panel_admin.php">
                <img src="../../public/imagenes/logo.png" alt="Logo">
            </a>
        </div>

        <div class="header-title">
            <h1>REGISTRO DE PROVEEDORES</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

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

    <script src="../../public/js/app.js"></script>

</body>

</html>