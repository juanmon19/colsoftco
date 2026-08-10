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
        $logica = new ProveedorLogica();

        $resultado = $logica->registrarProveedor(
            $nombre_empresa,
            $contacto_nombre,
            $contacto_apellido,
            $telefono,
            $email,
            $nit,
            $direccion,
            $descripcion_empresa
        );

        if ($resultado) {
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
                ],
                'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
            ]);
        } else {
            $mensaje = "Error al registrar proveedor";
        }
        } catch (Exception $e) {
        $mensaje = $e->getMessage();
    }
} 
?>

    <link rel="stylesheet" href="registrop.css">

    <div class="contenedor-principal">

        <button
            type="button"
            class="btn-volver"
            onclick="cargarModulo('../lista_proveedores/lista_proveedores.php')">
            ← Volver a Proveedores
        </button>

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

            <form method="POST" id="formProveedor">

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

    <script src="../../public/js/validacionesProveedor.js"></script>

    <script>
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
