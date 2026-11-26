<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

require_once __DIR__ . '/../../app/registro_materia_prima.php';

$conn = (new Conexion())->getConnection();
$materiales = $conn->query("SELECT id_material, nombre_material FROM materias_primas
    WHERE estado = 'activo' ORDER BY nombre_material, id_material")->fetchAll();
$unidades = $conn->query('SELECT id_unidad, nombre_unidad, sigla FROM unidades_medida ORDER BY nombre_unidad')->fetchAll();
$campo = static fn($clave) => htmlspecialchars(is_string($_POST[$clave] ?? null) ? $_POST[$clave] : '', ENT_QUOTES, 'UTF-8');
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

        $conn->beginTransaction();


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
            $nombreImagen,
            $idNuevo
        );

        if (!$resultado) {
            throw new RuntimeException('Error al registrar el proveedor.');
        }

        if (!$materiales && isset($_POST['nombre_material'])) {
            registrarMateriaPrima($conn, [
                'nombre_material' => $_POST['nombre_material'],
                'stock_actual' => '0',
                'id_unidad' => $_POST['id_unidad'] ?? '',
                'nombre_unidad' => $_POST['nombre_unidad'] ?? '',
                'sigla' => $_POST['sigla'] ?? '',
                'id_proveedor' => (string) $idNuevo,
            ]);
        } else {
            $id = $_POST['id_material'] ?? '';
            if (!is_string($id) || !ctype_digit($id) || !$logica->vincularMateriaPrima((int) $id, $idNuevo)) {
                throw new DomainException('Selecciona una materia prima activa del inventario.');
            }
        }

        $auditado = (new HistorialMovimientos())->registrar([
            'modulo'       => 'proveedores',
            'id_registro'  => $idNuevo,
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
        if (!$auditado) {
            throw new RuntimeException('No se pudo guardar el historial.');
        }
        $conn->commit();
        $_SESSION['catalogo_proveedor_ok'] = 'Proveedor registrado con su materia prima.';
        header('Location: contactar_proveedor.php?id=' . $idNuevo, true, 303);
        exit;
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        if (isset($nombreImagen)) {
            borrarArchivoSeguro($carpetaImagenes, $nombreImagen);
        }
        if ($e instanceof PDOException) {
            error_log('Registro de proveedor: ' . $e->getMessage());
            $mensaje = 'No se pudo guardar el proveedor. Revisa si el NIT ya está registrado.';
        } else {
            $mensaje = $e->getMessage();
        }
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
                        <input type="text" id="nombre_empresa" name="nombre_empresa" value="<?= $campo('nombre_empresa') ?>" required>
                    </div>

                    <div class="field-group">
                        <label>NIT</label>
                        <input type="text" id="nit" name="nit" value="<?= $campo('nit') ?>" required>
                    </div>

                    <div class="field-group">
                        <label>Nombre Contacto</label>
                        <input type="text" id="contacto_nombre" name="contacto_nombre" value="<?= $campo('contacto_nombre') ?>" required>
                    </div>

                    <div class="field-group">
                        <label>Apellido Contacto</label>
                        <input type="text" id="contacto_apellido" name="contacto_apellido" value="<?= $campo('contacto_apellido') ?>" required>
                    </div>

                    <div class="field-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono" name="telefono" value="<?= $campo('telefono') ?>" required>
                    </div>

                    <div class="field-group">
                        <label>Correo Electrónico</label>
                        <input type="email" id="email" name="email" value="<?= $campo('email') ?>" required>
                    </div>

                </div>

                <div class="field-group">
                    <label>Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?= $campo('direccion') ?>" required>
                </div>

                <div class="field-group">
                    <label>Descripción de la Empresa</label>
                    <textarea id="descripcion_empresa" name="descripcion_empresa" rows="4"><?= $campo('descripcion_empresa') ?></textarea>
                </div>

                <fieldset class="materiales-proveedor">
                    <legend>Materias primas que suministra</legend>
                    <?php if ($materiales): ?>
                        <div class="field-group">
                            <label for="id_material">Materia prima</label>
                            <select id="id_material" name="id_material" required>
                                <option value="">Selecciona una materia prima</option>
                                <?php foreach ($materiales as $mp): ?>
                                <option value="<?= (int) $mp['id_material'] ?>" <?= $campo('id_material') === (string) $mp['id_material'] ? 'selected' : '' ?>><?= htmlspecialchars($mp['nombre_material']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <p class="ayuda-materiales">Escribe la materia prima que suministra este proveedor. Se registrará con stock inicial cero.</p>
                        <div class="grid-form">
                            <div class="field-group">
                                <label for="nombre_material">Nombre de la materia prima</label>
                                <input type="text" id="nombre_material" name="nombre_material" maxlength="100" value="<?= $campo('nombre_material') ?>" required>
                            </div>
                            <div class="field-group">
                                <label for="id_unidad">Unidad de medida</label>
                                <select name="id_unidad" id="id_unidad" required>
                                    <?php foreach ($unidades as $u): ?>
                                    <option value="<?= (int) $u['id_unidad'] ?>" <?= $campo('id_unidad') === (string) $u['id_unidad'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nombre_unidad'] . ' (' . $u['sigla'] . ')') ?></option>
                                    <?php endforeach; ?>
                                    <option value="nueva" <?= $campo('id_unidad') === 'nueva' ? 'selected' : '' ?>>Crear unidad de medida</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid-form" id="unidadNueva">
                            <div class="field-group">
                                <label for="nombre_unidad">Nombre de la nueva unidad</label>
                                <input id="nombre_unidad" name="nombre_unidad" maxlength="20" placeholder="Por ejemplo, metro" value="<?= $campo('nombre_unidad') ?>">
                            </div>
                            <div class="field-group">
                                <label for="sigla">Sigla</label>
                                <input id="sigla" name="sigla" maxlength="5" placeholder="Por ejemplo, m" value="<?= $campo('sigla') ?>">
                            </div>
                        </div>
                    <?php endif; ?>
                </fieldset>
                <div class="form-actions">
                    <button type="reset" class="btn-limpiar">
                        Limpiar
                    </button>

                    <button type="submit" class="btn-registrar">
                        Registrar proveedor
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
        const unidad = document.getElementById('id_unidad');
        if (unidad) {
            const grupo = document.getElementById('unidadNueva');
            function actualizarUnidad() {
                const nueva = unidad.value === 'nueva';
                grupo.style.display = nueva ? '' : 'none';
                grupo.querySelectorAll('input').forEach(input => { input.required = nueva; });
            }
            unidad.addEventListener('change', actualizarUnidad);
            formulario.addEventListener('reset', () => setTimeout(actualizarUnidad, 0));
            actualizarUnidad();
        }
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


</body>

</html>
