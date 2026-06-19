<?php
session_start();

require_once '../../config/conexion.php';

$conexion = new Conexion();

$tiempo = time();

$MiConexion = $conexion->getConnection();

$conexion->sql = "SELECT * FROM usuarios WHERE id_usuario=? 
and token_password=? and expired_session>?";

try {
  $conexion->pps = $MiConexion->prepare($conexion->sql);
  $conexion->pps->bindParam(1, $_GET['id']);
  $conexion->pps->bindParam(2, $_GET['token']);
  $conexion->pps->bindParam(3, $tiempo);

  $conexion->pps->execute();

  $data = $conexion->pps->fetchAll(PDO::FETCH_OBJ);
} catch (\Throwable $th) {
  echo $th->getMessage();
} finally {
  $conexion->closeDataBase();
}

if (count($data) > 0):

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambio Contraseña</title>
  <link rel="stylesheet" href="cambiocontrasena.css">
</head>

<body>
  <div class="container">
    <div class="row">
      <div class="col">
        <div class="card">

          <div class="card-header">
            <p>Cambiar Contraseña</p>
          </div>

          <form action="../../app/logicamail.php" method="post">

            <div class="card-body">

              <?php
              if (isset($_SESSION['error'])):
              ?>
                <div class="alert">
                  <?php echo $_SESSION['error'] ?>
                </div>
              <?php
                unset($_SESSION['error']);
              endif;
              ?>

              <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password">
              </div>

              <div class="form-group">
                <label for="password">Confirmar Password</label>
                <input type="password" name="new_password" id="password">
                <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
              </div>

            </div>

            <div class="card-footer">
              <button name="save">Guardar</button>
              <button type="reset">Cancelar</button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>

<?php else:
  header("Location:../../login/login.html");
endif; ?>