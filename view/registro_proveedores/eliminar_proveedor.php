<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->eliminarProveedor($id);

    header("Location: ../lista_proveedores/lista_proveedores.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eliminar Proveedor</title>

<link rel="stylesheet" href="crud_proveedor.css">

</head>
<body>

    <header class="header">
        <div class="logo">
            <a href="../lista_proveedores/lista_proveedores.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Eliminar Proveedor</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="contenedor">

        <div class="acciones-superior">
            <a href="../lista_proveedores/lista_proveedores.php" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <div class="card">

            <div class="card-header bg-danger">
                Eliminar Proveedor
            </div>

            <div class="card-body">

                <h2>¿Desea eliminar este proveedor?</h2>

                <br>

                <p>
                    <strong>Empresa:</strong>
                    <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                </p>

                <p>
                    <strong>NIT:</strong>
                    <?= htmlspecialchars($proveedor['nit']) ?>
                </p>

                <p>
                    <strong>Contacto:</strong>
                    <?= htmlspecialchars($proveedor['contacto_nombre']) ?>
                    <?= htmlspecialchars($proveedor['contacto_apellido']) ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?= htmlspecialchars($proveedor['email']) ?>
                </p>

                <p>
                    <strong>Teléfono:</strong>
                    <?= htmlspecialchars($proveedor['telefono']) ?>
                </p>

                <div class="botones">

                    <form method="POST">

                        <button type="submit" class="btn btn-eliminar">
                            Eliminar definitivamente
                        </button>

                    </form>

                    <a href="../lista_proveedores/lista_proveedores.php"
                       class="btn btn-volver-secundario">
                        Cancelar
                    </a>

                </div>

            </div>

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