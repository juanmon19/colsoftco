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

    /* Ocultar el input hidden */
    input[type="hidden"] {
        display: none;
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

    /* ===== BOTÓN SECUNDARIO (Cancelar) ===== */
    .btn-secondary {
        width: 100%;
        padding: 10px;
        background: transparent;
        border: 1px solid white;
        color: white;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }

    .btn-secondary:hover {
        background: white;
        color: #0A1F44;
    }

    /* ===== ERROR ===== */
    .error {
        color: red;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
    }
  </style>
</head>

<body>

  <div class="header">
    <div class="logo">
      <a href="../../login/login.html">
        <img src="../../imagenes/logo.png" alt="logo">
      </a>
    </div>
    Cambiar Contraseña
  </div>

  <div class="login-container">

    <h2>Cambiar Contraseña</h2>

    <form action="../../app/logicamail.php" method="post">

      <?php
      if (isset($_SESSION['error'])):
      ?>
        <p class="error"><?php echo $_SESSION['error'] ?></p>
      <?php
        unset($_SESSION['error']);
      endif;
      ?>

      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
      </div>

      <div class="input-group">
        <label for="password">Confirmar Password</label>
        <input type="password" name="new_password" id="password">
        <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
      </div>

      <button name="save" class="btn">Guardar</button>
      <button type="reset" class="btn-secondary">Cancelar</button>

    </form>

  </div>

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>

<?php else:
  header("Location:../../login/login.html");
endif; ?>