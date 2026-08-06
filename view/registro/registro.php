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

            <form id="formRegistro" method="POST" action="../../app/logica.php">

                <input type="hidden" name="registro" value="1">

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

                    <div class="field-group full">
                        <label>Rol</label>
                        <select id="rol" name="rol">
                            <option value="administrador">Administrador</option>
                            <option value="bodeguero">Bodeguero</option>
                            <option value="operario">Operario</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label>Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="field-group">
                        <label>Confirmar Contraseña</label>
                        <input type="password" id="confirmar" required>
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

    <script>
        document.getElementById("formRegistro").addEventListener("submit", function(e){

            let password = document.getElementById("password").value;
            let confirmar = document.getElementById("confirmar").value;

            if(password !== confirmar){

                e.preventDefault();

                alert("Las contraseñas no coinciden");
            }
        });
    </script>

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>