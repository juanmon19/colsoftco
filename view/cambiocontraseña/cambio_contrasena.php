<?php
session_start();

require_once '../../config/Conexion.php';

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

if (count($data)>0 ):

?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="cambiocontraseña.css" rel="stylesheet">
  </head>

  <body>
    <div class="container">
      <div class="row justify-content-center align-items-center vh-100">
        <div class="col-xl-6 col-log-5 col-md-6 col-sm-9 col-2">
          <div class="card">
            <div class="card-header bg bg-primary">
              <p class="h4 text-white">Cambiar Contraseña</p>
            </div>
            <form action="../../app/logicamail.php" method="post">
              <div class="card-body">

                <?php
                if (isset($_SESSION['error'])):
                ?>
                  <div class="alert alert-danger">
                    <?php echo  $_SESSION['error'] ?>
                  </div>
                <?php
                  unset($_SESSION['error']);
                endif;
                ?>

                <div class="form-group">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" name="password" id="password" class="form-control">
                </div>

                <div class="form-group">
                  <label for="password" class="form-label">Confirmar Password</label>
                  <input type="password" name="new_password" id="password" class="form-control">
                  <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
                </div>


              </div>

              <div class="card-footer">
                <button class="btn btn-primary" name="save">Guardar</button>
                <button type="reset" class="btn btn-danger">Cancelar</button>

              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </body>

  </html>
<?php else:
  header("Location:../login/login.html");
endif; ?>