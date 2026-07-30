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

  <div class="header">
    <div class="logo">
      <a href="../login/login.html">
        <img src="../../public/imagenes/logo.png" alt="logo">
      </a>
    </div>
    Recuperar Contraseña
  </div>

  <div class="login-container">

    <h2>Reset Password</h2>

    <form action="../../app/logicamail.php" method="post">

      <div class="input-group">
        <label for="email">Escriba su email</label>
        <input type="email" name="email" id="email">
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
        <a href="../login/login.html" class="forgot-link">Login</a>
      </div>

    </form>

  </div>

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>