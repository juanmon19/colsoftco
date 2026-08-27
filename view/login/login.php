<?php
session_start();

// Obtener mensajes de sesión
$error = '';
$mensaje = '';

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="login.css" rel="stylesheet">
    <title>Login</title>
</head>

<body>

    <div class="header">
        <div class="logo">
            <img src="../../public/imagenes/logo.png" alt="Logo">
        </div>
        INGRESE AL SISTEMA
    </div>

    <div class="login-wrapper">
        <div class="login-container">

            <h2>INICIAR SESIÓN</h2>

            <!-- Mensajes -->
            <?php if (!empty($error)) : ?>
                <div class="mensaje-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje)) : ?>
                <div class="mensaje-exito">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="../../app/logica.php" autocomplete="off">

                <input type="hidden" name="login" value="1">

                <div class="input-group">
                    <label for="username">Documento</label>
                    <input type="text" id="username" name="documento" required>
                </div>

                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="togglePassword" class="toggle-password" tabindex="-1" aria-label="Mostrar contraseña">
                            <!-- Icono de Ojo Abierto (Por defecto) -->
                            <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                                <circle cx="12" cy="12" r="3" fill="currentColor"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn">
                    Ingresar
                </button>

                <div class="forgot-container">
                    <a href="../recuperar_contrasena/recuperar_contrasena.php" class="forgot-link">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

            </form>

        </div>
    </div>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script src="../js/app.js"></script>

    <script>
        // Limpiar el formulario al cargar o regresar a la página
        window.addEventListener('pageshow', function () {
            document.getElementById('loginForm').reset();
        });

        // Funcionalidad para alternar la contraseña y cambiar entre los iconos vectoriales de la imagen
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'password') {
                    // Muestra el icono de ojo abierto
                    togglePassword.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3" fill="currentColor"></circle>
                        </svg>
                    `;
                } else {
                    // Muestra el icono de ojo con la barra diagonal cruzada
                    togglePassword.innerHTML = `
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle cx="12" cy="12" r="3" fill="currentColor"></circle>
                            <line x1="2" y1="2" x2="22" y2="22"></line>
                        </svg>
                    `;
                }
            });
        }
    </script>

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

</body>

</html>