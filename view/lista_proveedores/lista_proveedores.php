<?php

require_once '../../app/logica_proveedores.php';

$logica = new ProveedorLogica();
$proveedores = $logica->getProveedores();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colsoftco - Lista de Proveedores</title>
    <link href="listaproveedores.css" rel="stylesheet">
</head>

<body>

    <header>
        <a class="logo" href="../panel_admin/panel_admin.html">
            <img src="../../public/imagenes/logo.png" alt="logo">
        </a>

        <a class="header-title">
            Lista de Proveedores
        </a>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>

    </header>

    <main class="content">

        <div class="controls-container">

            <div class="tabs">
                <button class="tab active">Todos</button>
            </div>

            <div class="actions">
                <button onclick="window.location.href='../registro_proveedores/registroproveedores.php'">
                    Registrar Proveedor
                </button>

                <button class="btn-action"
                    onclick="window.location.href='../lista_verificacion/lista_verificacion.php'">
                    Generar lista de verificación
                </button>
            </div>

        </div>

        <div class="provider-list">

            <?php if (count($proveedores) > 0): ?>

                <?php foreach ($proveedores as $proveedor): ?>

                    <div class="provider-card">

                        <div class="provider-logo">

                            <div style="
                                font-weight:bold;
                                font-size:24px;
                                color:#2E8B57;
                                text-align:center;
                            ">
                                <?php
                                echo strtoupper(substr(
                                    $proveedor['nombre_empresa'],
                                    0,
                                    3
                                ));
                                ?>
                            </div>

                        </div>

                        <div class="provider-info">

                            <p>
                                <strong>Proveedor:</strong>
                                <?php echo htmlspecialchars($proveedor['nombre_empresa']); ?>
                            </p>

                            <?php if (isset($proveedor['nit'])): ?>
                                <p>
                                    <strong>NIT:</strong>
                                    <?php echo htmlspecialchars($proveedor['nit']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (isset($proveedor['direccion'])): ?>
                                <p>
                                    <strong>Dirección:</strong>
                                    <?php echo htmlspecialchars($proveedor['direccion']); ?>
                                </p>
                            <?php endif; ?>

                            <?php if (isset($proveedor['descripcion_empresa'])): ?>
                                <p>
                                    <strong>Descripción:</strong>
                                    <?php echo htmlspecialchars($proveedor['descripcion_empresa']); ?>
                                </p>
                            <?php endif; ?>

                            <p>
                                <strong>Contacto:</strong>
                                <?php
                                echo htmlspecialchars(
                                    $proveedor['contacto_nombre'] . ' ' .
                                        $proveedor['contacto_apellido']
                                );
                                ?>
                            </p>

                            <p>
                                <strong>Correo:</strong>
                                <?php echo htmlspecialchars($proveedor['email']); ?>
                            </p>

                            <p>
                                <strong>Teléfono:</strong>
                                <?php echo htmlspecialchars($proveedor['telefono']); ?>
                            </p>

                        </div>

                        <div class="provider-buttons">

                            <div class="btn-group-top">

                                <button class="btn-card" onclick="editarProveedor(<?php echo $proveedor['id_proveedor']; ?>)">
                                    Editar
                                </button>

                                <button class="btn-card" onclick="eliminarProveedor(<?php echo $proveedor['id_proveedor']; ?>)">
                                    Eliminar
                                </button>

                            </div>

                            <button class="btn-card btn-card-large">
                                Hacer Pedido
                            </button>

                            <button class="btn-card btn-card-large">
                                Contactar
                            </button>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="provider-card">

                    <div class="provider-info">

                        <p>
                            No existen proveedores registrados.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </main>

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
            <span>© 2025 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.</span>
            <span>Desarrollado por <strong>Equipo SENA</strong></span>
        </div>
    </footer>

    <script>
        function editarProveedor(id) {

            window.location.href =
                "../registro_proveedores/editar_proveedor.php?id=" + id;
        }

        function eliminarProveedor(id) {

            window.location.href =
                "../registro_proveedores/eliminar_proveedor.php?id=" + id;
        }

        document.querySelectorAll('.tab').forEach(tab => {

            tab.addEventListener('click', function() {

                document.querySelectorAll('.tab')
                    .forEach(t => t.classList.remove('active'));

                this.classList.add('active');
            });

        });
    </script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>