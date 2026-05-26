<?php

  session_start();

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset contraseña</title>
  <link href="cambiocontraseña.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <div class="row justify-content-center align-items-center vh-100">
      <div class="col-xl-6 col-log-5 col-md-6 col-sm-9 col-2">
        <div class="card">
          <div class="card-header bg bg-primary">
            <p class="h4 text-white">Reset Password </p>
          </div>
          <form action="../app/logicamail.php" method="post">
            <div class="card-body">
              <div class="form-group">
                <label for="email" class="form-label">Escriba su email</label>
                <input type="email" name="email" id="email" class="form-control">
              </div>


              <?php
                if(isset($_SESSION['response'])):
                  
              ?>
                <h2><?php echo $_SESSION['response']?></h2>
              <?php
                unset($_SESSION['response']);
                endif;
              ?>

            <div class="card-footer">
              <button class="btn btn-primary" name="send">Enviar</button>
              <a href="login.php" class="btn btn btn-danger">Login</a>
              <!-- <button type="reset" class="btn btn-danger">Cancelar</button> -->

            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</body>

</html>