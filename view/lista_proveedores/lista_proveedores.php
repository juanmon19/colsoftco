<?php

require_once "../../app/verificar_sesion.php";
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
    <link href="lista_proveedores.css" rel="stylesheet">
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Lista de Proveedores</h1>
        </div>

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
                <button class="btn-action" onclick="window.location.href='registro_proveedores.php'">
                    Registrar Proveedor
                </button>

                <button class="btn-action"
                    onclick="window.location.href='../historial_movimientos/historial.php'">
                    historial de movimientos
                </button>
            </div>

        </div>

        <!-- =========================
             BARRA DE FILTROS
        ========================== -->
        <div class="filtros-container">

            <div class="filtro-busqueda">
                <input
                    type="text"
                    id="inputBusquedaProveedor"
                    placeholder="Buscar por nombre, NIT o contacto...">
            </div>

            <div class="filtro-orden">
                <label for="selectOrden">Ordenar:</label>
                <select id="selectOrden">
                    <option value="az">Nombre (A-Z)</option>
                    <option value="za">Nombre (Z-A)</option>
                </select>
            </div>

            <button type="button" id="btnLimpiarFiltros" class="btn-limpiar-filtros">
                Limpiar filtros
            </button>

        </div>

        <p id="mensajeSinResultados" class="mensaje-sin-resultados" style="display:none;">
            No se encontraron proveedores que coincidan con la búsqueda.
        </p>

        <div class="provider-list" id="listaProveedores">

            <?php if (count($proveedores) > 0): ?>

                <?php foreach ($proveedores as $proveedor): ?>

                    <div class="provider-card"
                        data-nombre="<?php echo htmlspecialchars(strtolower($proveedor['nombre_empresa'])); ?>"
                        data-nit="<?php echo htmlspecialchars(strtolower($proveedor['nit'] ?? '')); ?>"
                        data-contacto="<?php echo htmlspecialchars(strtolower($proveedor['contacto_nombre'] . ' ' . $proveedor['contacto_apellido'])); ?>">

                        <!-- =========================
             IMAGEN DEL PROVEEDOR
        ========================== -->
                        <div class="provider-logo">

                            <?php if (!empty($proveedor['imagen'])): ?>

                                <img
                                    src="../../public/imagenes/proveedores/<?php echo htmlspecialchars($proveedor['imagen']); ?>"
                                    alt="<?php echo htmlspecialchars($proveedor['nombre_empresa']); ?>"
                                    class="provider-logo-img">

                            <?php else: ?>

                                <div style="
                    font-weight: bold;
                    font-size: 24px;
                    color: #2E8B57;
                    text-align: center;
                ">
                                    <?php
                                    echo strtoupper(substr(
                                        $proveedor['nombre_empresa'],
                                        0,
                                        3
                                    ));
                                    ?>
                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- =========================
            INFORMACIÓN DEL PROVEEDOR
        ========================== -->
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


                        <!-- =========================
             BOTONES
        ========================== -->
                        <div class="provider-buttons">

                            <div class="btn-group-top">

                                <a href="editar_proveedor.php?id=<?php echo (int)$proveedor['id_proveedor']; ?>" class="btn-card">
                                    Editar
                                </a>

                                <a
                                    href="eliminar_proveedor.php?id=<?php echo (int)$proveedor['id_proveedor']; ?>"
                                    class="btn-card">
                                    Eliminar
                                </a>

                            </div>

                            <!-- HACER PEDIDO -->
                            <!-- <a
                                href="../registro_proveedores/hacer_pedido.php?id=<?php echo (int)$proveedor['id_proveedor']; ?>"
                                class="btn-card btn-card-large">
                                Hacer Pedido
                            </a> -->

                            <!-- CONTACTAR -->
                            <a
                                href="../lista_proveedores/contactar_proveedor.php?id=<?php echo (int)$proveedor['id_proveedor']; ?>"
                                class="btn-card btn-card-large">
                                Contactar
                            </a>

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
            <span>© 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.</span>
            <span>Desarrollado por <strong>Equipo SENA</strong></span>
        </div>
    </footer>

    <script>
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

        // =========================
        // FILTROS: BÚSQUEDA + ORDEN ALFABÉTICO
        // =========================
        (function() {

            const inputBusqueda = document.getElementById('inputBusquedaProveedor');
            const selectOrden = document.getElementById('selectOrden');
            const btnLimpiar = document.getElementById('btnLimpiarFiltros');
            const contenedorLista = document.getElementById('listaProveedores');
            const mensajeSinResultados = document.getElementById('mensajeSinResultados');

            if (!contenedorLista) return;

            const tarjetas = Array.from(
                contenedorLista.querySelectorAll('.provider-card[data-nombre]')
            );

            function aplicarFiltros() {

                const texto = inputBusqueda.value.trim().toLowerCase();
                let visibles = 0;

                tarjetas.forEach(tarjeta => {

                    const nombre = tarjeta.dataset.nombre || '';
                    const nit = tarjeta.dataset.nit || '';
                    const contacto = tarjeta.dataset.contacto || '';

                    const coincide =
                        texto === '' ||
                        nombre.includes(texto) ||
                        nit.includes(texto) ||
                        contacto.includes(texto);

                    tarjeta.style.display = coincide ? '' : 'none';

                    if (coincide) visibles++;
                });

                mensajeSinResultados.style.display = visibles === 0 ? 'block' : 'none';
            }

            function aplicarOrden() {

                const orden = selectOrden.value;

                const tarjetasOrdenadas = [...tarjetas].sort((a, b) => {

                    const nombreA = a.dataset.nombre || '';
                    const nombreB = b.dataset.nombre || '';

                    return orden === 'az'
                        ? nombreA.localeCompare(nombreB)
                        : nombreB.localeCompare(nombreA);
                });

                tarjetasOrdenadas.forEach(tarjeta => {
                    contenedorLista.appendChild(tarjeta);
                });
            }

            inputBusqueda.addEventListener('input', aplicarFiltros);

            selectOrden.addEventListener('change', aplicarOrden);

            btnLimpiar.addEventListener('click', () => {
                inputBusqueda.value = '';
                selectOrden.value = 'az';
                aplicarFiltros();
                aplicarOrden();
            });

            // Orden inicial A-Z al cargar la página
            aplicarOrden();

        })();
    </script>

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>