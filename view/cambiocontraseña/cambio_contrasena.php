<?php
session_start();

require_once '../../config/conexion.php';

$conexion = new Conexion();

$tiempo = time();

$MiConexion = $conexion->getConnection();

/* Inicializar para evitar "Undefined variable $data" */
$data = [];

$conexion->sql = "SELECT * FROM usuarios 
                  WHERE id_usuario = ? 
                  AND token_password = ? 
                  AND expired_session > ?";

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
    <link href="cambiocontrasena.css" rel="stylesheet">
</head>

<body>

    <div class="header">
        <div class="logo">
            <a href="../../login/login.html">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>
        Cambiar Contraseña
    </div>

    <div class="login-container">

        <h2>Cambiar Contraseña</h2>

        <form action="../../app/logicamail.php" method="post">

            <?php if (isset($_SESSION['error'])): ?>
                <p class="error">
                    <?php echo $_SESSION['error']; ?>
                </p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="input-group">
                <label for="password">Nueva contraseña</label>
                <input type="password" name="password" id="password" required>
            </div>

            <div class="input-group">
                <label for="new_password">Confirmar contraseña</label>
                <input type="password" name="new_password" id="new_password" required>

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $_GET['id']; ?>">
            </div>

            <button type="submit" name="save" class="btn">
                Guardar
            </button>

            <button type="reset" class="btn-secondary">
                Cancelar
            </button>

        </form>

    </div>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>
</html>

<?php
else:
    header("Location: ../../login/login.html");
    exit();
endif;
?>