<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->actualizarProveedor(
        $id,
        $_POST['nombre_empresa'],
        $_POST['contacto_nombre'],
        $_POST['contacto_apellido'],
        $_POST['telefono'],
        $_POST['email'],
        $_POST['nit'],
        $_POST['direccion'],
        $_POST['descripcion_empresa']
    );

    header("Location: ../lista_proveedores/lista_proveedores.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Proveedor</title>

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
            <h1>Editar Proveedor</h1>
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

            <div class="card-header">
                Editar Proveedor
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="form-grid">

                        <div class="campo">
                            <label>Nombre Empresa</label>
                            <input
                                type="text"
                                name="nombre_empresa"
                                value="<?= htmlspecialchars($proveedor['nombre_empresa']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>NIT</label>
                            <input
                                type="text"
                                name="nit"
                                value="<?= htmlspecialchars($proveedor['nit']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Nombre Contacto</label>
                            <input
                                type="text"
                                name="contacto_nombre"
                                value="<?= htmlspecialchars($proveedor['contacto_nombre']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Apellido Contacto</label>
                            <input
                                type="text"
                                name="contacto_apellido"
                                value="<?= htmlspecialchars($proveedor['contacto_apellido']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Teléfono</label>
                            <input
                                type="text"
                                name="telefono"
                                value="<?= htmlspecialchars($proveedor['telefono']) ?>"
                                required>
                        </div>

                        <div class="campo">
                            <label>Correo Electrónico</label>
                            <input
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($proveedor['email']) ?>"
                                required>
                        </div>

                        <div class="campo full">
                            <label>Dirección</label>
                            <input
                                type="text"
                                name="direccion"
                                value="<?= htmlspecialchars($proveedor['direccion']) ?>"
                                required>
                        </div>

                        <div class="campo full">
                            <label>Descripción Empresa</label>
                            <textarea
                                name="descripcion_empresa"
                                required><?= htmlspecialchars($proveedor['descripcion_empresa']) ?></textarea>
                        </div>

                    </div>

                    <div class="botones">

                        <button type="submit" class="btn btn-guardar">
                            Guardar Cambios
                        </button>

                        <a href="../lista_proveedores/lista_proveedores.php"
                           class="btn btn-volver-secundario">
                            Cancelar
                        </a>

                    </div>

                </form>

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