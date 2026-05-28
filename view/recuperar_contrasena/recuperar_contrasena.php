<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset contraseña</title>
  <link rel="stylesheet" href="recuperar_contrasena.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="card">

          <div class="card-header">
            <p>Reset Password</p>
          </div>

          <form action="../../app/logicamail.php" method="post">

            <div class="card-body">

              <div class="form-group">
                <label for="email">Escriba su email</label>
                <input type="email" name="email" id="email">
              </div>

              <?php
              if (isset($_SESSION['response'])):
              ?>
                <h2><?php echo $_SESSION['response'] ?></h2>
              <?php
                unset($_SESSION['response']);
              endif;
              ?>

            </div>

            <div class="card-footer">
              <button name="send">Enviar</button>
              <a href="../login/login.html">Login</a>
              <!-- <button type="reset">Cancelar</button> -->
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</body>

</html>