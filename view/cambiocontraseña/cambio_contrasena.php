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

    <header class="header">
        <div class="logo">
            <a href="../login/login.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>
        
        <div class="header-title">
            <h1>Cambiar Contraseña</h1>
        </div>

        <!-- Elemento invisible para equilibrar el espacio del logo y que el título quede perfectamente centrado -->
        <div style="width: 55px; flex-shrink: 0;"></div>
    </header>

    <div class="login-wrapper">
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

                    <input
                        type="hidden"
                        name="token"
                        value="<?php echo $_GET['token']; ?>">
                        
                </div>

                <button type="submit" name="save" class="btn">
                    Guardar
                </button>

                <button type="reset" class="btn-secondary">
                    Cancelar
                </button>

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

<?php
else:
    header("Location: ../login/login.php");
    exit();
endif;
?>