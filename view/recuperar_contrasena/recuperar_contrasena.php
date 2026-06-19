<?php

session_start();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset contraseña</title>
  <style>
    * {
        box-sizing: border-box;
    }

    /* ===== GENERAL ===== */
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        font-size: 14px;
    }

    .header {
        background-color: #0A1F44;
        color: white;
        padding: 20px;
        text-align: center;
        font-size: 29px;
        font-weight: bold;
        position: relative;
    }

    .logo {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
    }

    .logo img {
        width: 75px;
        cursor: pointer;
        margin-top: 10px;
    }

    /* ===== CONTENEDOR LOGIN ===== */
    .login-container {
        background: #0A1F44;
        padding: 50px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        width: 400px;
        margin: 120px auto;
    }

    /* ===== TÍTULO ===== */
    .login-container h2 {
        color: white;
        text-align: center;
        margin-bottom: 20px;
        font-size: 20px;
    }

    /* ===== INPUTS ===== */
    .input-group {
        margin-bottom: 15px;
    }

    .input-group label {
        color: white;
        display: block;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .input-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    /* ===== BOTÓN ===== */
    .btn {
        width: 100%;
        padding: 10px;
        background: #D4AF37;
        border: none;
        color: black;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn:hover {
        background: white;
    }

    /* ===== ERROR ===== */
    .error {
        color: red;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
    }

    /* ===== MENSAJE DE RESPUESTA (éxito / info) ===== */
    .response {
        color: #D4AF37;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
    }

    /* ===== ENLACE RECUPERAR CONTRASEÑA ===== */
    .forgot-container {
        text-align: center;
        margin-top: 20px;
    }

    .forgot-link {
        color: #ffffff;
        text-decoration: none;
        font-size: 13px;
        display: inline-block;
        transition: color 0.3s ease;
    }

    .forgot-link:hover {
        color: #D4AF37;
        text-decoration: underline;
    }
  </style>
</head>

<body>

  <div class="header">
    <div class="logo">
      <a href="../login/login.html">
        <img src="../imagenes/logo.png" alt="logo">
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