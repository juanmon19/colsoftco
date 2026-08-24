<?php

session_start();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña</title>
  <link href="recuperar_contrasena.css" rel="stylesheet">
</head>

<body>

  <header class="header">
    <div class="logo">
        <img src="../../public/imagenes/logo.png" alt="logo">
    </div>
    
    <div class="header-title">
      <h1>Recuperar Contraseña</h1>
    </div>

    <!-- Elemento invisible para equilibrar el espacio del logo y centrar el título -->
    <div style="width: 55px; flex-shrink: 0;"></div>
  </header>

  <div class="login-wrapper">
    <div class="login-container">

      <h2>Recuperar Contraseña</h2>

      <form action="../../app/logicamail.php" method="post">

        <div class="input-group">
          <label for="email">Escriba su email</label>
          <input type="email" name="email" id="email" required>
        </div>

        <?php
        if (isset($_SESSION['response'])):
        ?>
          <p class="response"><?php echo $_SESSION['response'] ?></p>
        <?php
          unset($_SESSION['response']);
        endif;
        ?>

        <button name="send" class="btn">Enviar</button>

        <div class="forgot-container">
          <a href="../login/login.php" class="forgot-link">Volver al Login</a>
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

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>