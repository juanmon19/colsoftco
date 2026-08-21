<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios </title>
    <link href="registro.css" rel="stylesheet">

    <style>
        .campo-imagen {
            margin-bottom: 20px;
        }

        .campo-imagen > label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .imagen-upload-wrapper {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f7f8fa;
            border: 1px solid #e2e5eb;
            border-radius: 10px;
            padding: 16px;
        }

        .imagen-preview {
            width: 110px;
            height: 110px;
            flex-shrink: 0;
            border: 2px solid #f2c14e;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
        }

        .imagen-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .imagen-preview span {
            color: #9a9a9a;
            font-size: 13px;
            text-align: center;
            padding: 0 8px;
        }

        .imagen-controles {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .btn-seleccionar-imagen {
            display: inline-block;
            width: fit-content;
            background: #0d1b3d;
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-seleccionar-imagen:hover {
            background: #16255c;
        }

        .imagen-controles input[type="file"] {
            display: none;
        }

        .texto-archivo {
            font-size: 13px;
            color: #333;
        }

        .texto-ayuda {
            font-size: 12px;
            color: #9a9a9a;
        }

        /* --- ESTILOS PARA EL OJITO --- */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 40px; 
            box-sizing: border-box;
        }

        .btn-ojito {
            position: absolute;
            right: 10px;
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            outline: none;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .btn-ojito:hover {
            background: #f0f2f5;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../panel_admin/panel_admin.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>REGISTRO DE USUARIOS</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="contenedor-principal">

        <div class="acciones-superior">
            <a href="../panel_admin/panel_admin.php" class="btn-volver">
                ← Volver al Panel
            </a>
        </div>

        <?php
            if (isset($_SESSION['mensaje'])): ?>
            <div class="mensaje">
                <?php echo $_SESSION['mensaje']; ?>
            </div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>

        <div class="card-formulario">

            <div class="card-header">
                <span class="barra-amarilla"></span>
                <h2>Registrar Nuevo Usuario</h2>
            </div>

            <form id="formRegistro" method="POST" action="../../app/logica.php" enctype="multipart/form-data">

                <input type="hidden" name="registro" value="1">

                <div class="campo-imagen">
                    <label>Imagen del Usuario</label>
                    <div class="imagen-upload-wrapper">
                        <div class="imagen-preview" id="previewContenedor">
                            <img id="previewImagen" src="" alt="Vista previa" style="display:none;">
                            <span id="previewTexto">Sin imagen</span>
                        </div>
                        <div class="imagen-controles">
                            <label for="foto" class="btn-seleccionar-imagen">Seleccionar Imagen</label>
                            <input type="file" id="foto" name="foto" accept="image/png, image/jpeg, image/jpg">
                            <span id="nombreArchivo" class="texto-archivo">Ningún archivo seleccionado</span>
                            <small class="texto-ayuda">Formatos permitidos: JPG, PNG. Máximo 2 MB.</small>
                        </div>
                    </div>
                </div>

                <div class="grid-form">

                    <div class="field-group">
                        <label>Email</label>
                        <input type="text" id="contacto" name="email" required>
                    </div>

                    <div class="field-group">
                        <label>Documento</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>

                    <div class="field-group">
                        <label>Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="field-group">
                        <label>Apellido</label>
                        <input type="text" id="apellido" name="apellido" required>
                    </div>

                    <div class="field-group">
                        <label>Teléfono</label>
                        <input type="text" id="telefono" name="telefono">
                    </div>

                    <div class="field-group full">
                        <label>Rol</label>
                        <select id="rol" name="rol">
                            <option value="administrador">Administrador</option>
                            <option value="bodeguero">Bodeguero</option>
                            <option value="operario">Operario</option>
                        </select>
                    </div>

                    <!-- Campos de contraseñas con el wrapper para el botón -->
                    <div class="field-group">
                        <label>Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required>
                            <button type="button" class="btn-ojito" id="btn-ojo-pass"></button>
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Confirmar Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirmar" required>
                            <button type="button" class="btn-ojito" id="btn-ojo-conf"></button>
                        </div>
                    </div>

                </div>

                <hr class="form-divider">

                <div class="form-actions">
                    <button type="reset" class="btn-limpiar">Limpiar</button>
                    <button type="submit" class="btn-registrar">Registrar</button>
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

    <script src="../../public/js/validacionesRegistro.js"></script>

<script>

    function mostrarError(id, mensaje) {

        const input = document.getElementById(id);

        input.style.border = "2px solid #dc3545";

        const errorExistente =
            input.parentNode.querySelector(".mensaje-error");

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

            document.querySelectorAll(".mensaje-error")
                .forEach(e => e.remove());

            document.querySelectorAll("#formRegistro input")
                .forEach(campo => {
                    campo.style.border = "";
                });
        }


    // VISTA PREVIA DE IMAGEN
    const inputFoto = document.getElementById("foto");
    const previewImagen = document.getElementById("previewImagen");
    const previewTexto = document.getElementById("previewTexto");
    const nombreArchivo = document.getElementById("nombreArchivo");

    inputFoto.addEventListener("change", function() {

        const archivo = this.files[0];

        if (!archivo) {
            previewImagen.style.display = "none";
            previewTexto.style.display = "block";
            nombreArchivo.textContent = "Ningún archivo seleccionado";
            return;
        }

        const tiposPermitidos = ["image/png", "image/jpeg", "image/jpg"];

        if (!tiposPermitidos.includes(archivo.type)) {
            mostrarError("foto", "Formato no válido. Solo se permiten JPG o PNG.");
            this.value = "";
            return;
        }

        if (archivo.size > 7 * 1024 * 1024) {
            mostrarError("foto", "La imagen no debe superar los 7 MB.");
            this.value = "";
            return;
        }

        const errorPrevio = inputFoto.parentNode.querySelector(".mensaje-error");
        if (errorPrevio) errorPrevio.remove();

        nombreArchivo.textContent = archivo.name;

        const lector = new FileReader();

        lector.onload = function(e) {
            previewImagen.src = e.target.result;
            previewImagen.style.display = "block";
            previewTexto.style.display = "none";
        };

        lector.readAsDataURL(archivo);
    });


    const formulario = document.getElementById("formRegistro");


    formulario.addEventListener("submit", function(e) {

        const documento =
            document.getElementById("documento").value;

        const nombre =
            document.getElementById("nombre").value;

        const apellido =
            document.getElementById("apellido").value;

        const correo =
            document.getElementById("contacto").value;

        const password =
            document.getElementById("password").value;

        const confirmar =
            document.getElementById("confirmar").value;


        limpiarErrores();


        // FOTO OBLIGATORIA
        if (inputFoto.files.length === 0) {

            mostrarError(
                "foto",
                "Debe seleccionar una imagen para el usuario."
            );

            e.preventDefault();
            return;
        }


        // DOCUMENTO
        if (!expresiones.documento.test(documento)) {

            mostrarError(
                "documento",
                "Ingrese un documento válido (6 a 12 números)."
            );

            e.preventDefault();
            return;
        }


        // NOMBRE
        if (!expresiones.nombre.test(nombre)) {

            mostrarError(
                "nombre",
                "Ingrese un nombre válido."
            );

            e.preventDefault();
            return;
        }


        // APELLIDO
        if (!expresiones.apellido.test(apellido)) {

            mostrarError(
                "apellido",
                "Ingrese un apellido válido."
            );

            e.preventDefault();
            return;
        }


        // TELÉFONO
        const telefono =
            document.getElementById("telefono").value;

        if (telefono.trim() !== "" && !/^[0-9]{7,10}$/.test(telefono)) {

            mostrarError(
                "telefono",
                "Ingrese un teléfono válido (7 a 10 números)."
            );

            e.preventDefault();
            return;
        }


        // CORREO
        if (!expresiones.correo.test(correo)) {

            mostrarError(
                "contacto",
                "Ingrese un correo electrónico válido."
            );

            e.preventDefault();
            return;
        }


        // CONTRASEÑA
        if (password.length < 8) {

            mostrarError(
                "password",
                "La contraseña debe tener mínimo 8 caracteres."
            );

            e.preventDefault();

        } else if (!/[A-Z]/.test(password)) {

            mostrarError(
                "password",
                "La contraseña debe tener al menos una letra mayúscula."
            );

            e.preventDefault();

        } else if (!/[a-z]/.test(password)) {

            mostrarError(
                "password",
                "La contraseña debe tener al menos una letra minúscula."
            );

            e.preventDefault();

        } else if (!/[0-9]/.test(password)) {

            mostrarError(
                "password",
                "La contraseña debe tener al menos un número."
            );

            e.preventDefault();

        } else if (!/[\W_]/.test(password)) {

            mostrarError(
                "password",
                "La contraseña debe tener al menos un carácter especial."
            );

            e.preventDefault();
        }


        // CONFIRMAR CONTRASEÑA
        if (password !== confirmar) {

            mostrarError(
                "confirmar",
                "Las contraseñas no coinciden."
            );

            e.preventDefault();
        }

    });

    // --- LÓGICA DEL OJITO CON VECTORES SVG ---
    function configurarOjito(inputId, btnId) {
        const input = document.getElementById(inputId);
        const btn = document.getElementById(btnId);

        // SVG del ojo abierto (color oscuro #1c2b36)
        const ojoAbierto = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1c2b36" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        
        // SVG del ojo con la línea roja atravesada (usando el rojo #dc3545 de tus alertas)
        const ojoCerrado = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1c2b36" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23" stroke="#dc3545" stroke-width="2.5"></line></svg>`;

        // Estado inicial del botón
        btn.innerHTML = ojoAbierto;

        btn.addEventListener("click", function() {
            if (input.type === "password") {
                input.type = "text";
                btn.innerHTML = ojoCerrado; // Cambia al ojo tachado con línea roja
            } else {
                input.type = "password";
                btn.innerHTML = ojoAbierto; // Vuelve al ojo normal
            }
        });
    }

    configurarOjito("password", "btn-ojo-pass");
    configurarOjito("confirmar", "btn-ojo-conf");

</script>

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>