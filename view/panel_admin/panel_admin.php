<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - COLSOFTCO</title>
    <link rel="stylesheet" href="paneladmin.css">
</head>

<body>

    <div class="app">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                <img src="../../public/imagenes/logo.png" alt="COLSOFTCO">
                <div class="brand-text">
                    <strong>COLSOFTCO</strong>
                    <span>Sistema de Gestión</span>
                </div>
            </div>

            <button class="mobile-menu" id="mobileMenu" type="button" aria-expanded="false">
                <span>☰ Menú</span>
                <span>⌄</span>
            </button>

            <nav class="nav" id="navMenu">
                <button class="nav-item"onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
                <button class="nav-item"onclick="window.location.href='../registro_proveedores/registro_proveedores.php'">Registrar Proveedor</button>
                <button class="nav-item"onclick="window.location.href='../lista_verificacion/lista_verificacion.php'">Lista Verificación</button>
                <button class="nav-item"onclick="window.location.href='../generar_informe/generar_informe.php'">Generar Informe</button>
                <button class="nav-item"onclick="window.location.href='../registromp/registromp.php'">Registrar Materia Prima</button>
                <button class="nav-item"onclick="window.location.href='../control_de_stock/control_de_stock.php'">Control de Stock</button>
                <button class="nav-item"onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
                <button class="nav-item"onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
                <button class="nav-item"onclick="window.location.href='../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
                <button class="nav-item"onclick="window.location.href='../receta_de_colchones/receta_colchones.php'">Receta de Colchones</button>
            </nav>

            <div class="help-box">
                <div class="headset">♧</div>
                <div>
                    <strong>¿Necesitas ayuda?</strong>
                    <p>Nuestro equipo está<br>para apoyarte.</p>
                    <a href="mailto:juanjosemon19@gmail.com">Contáctanos</a>
                </div>
            </div>
        </aside>

        <div class="main">

            <!-- HEADER -->
            <header class="topbar">
                <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

                <div class="welcome">
                    <h1>BIENVENIDO, JUAN JOSE</h1>
                    <p>Administrador</p>
                </div>

                <div class="header-actions">
                    <a href="../registro/registro.php" class="btn-header">Registrar</a>

                    <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
                        Cerrar sesión
                    </button> 
                </div>
            </header>

            <main class="content">

                <!-- PERFIL + ESTADÍSTICAS -->
                <section class="hero-grid">

                    <article class="profile">
                        <img src="../../public/imagenes/usuario.png" alt="Juan Jose Montaño">

                        <div class="profile-data">
                            <h2>Juan Jose Montaño</h2>
                            <p class="role"><b>Rol:</b> Administrador</p>
                            <p><span class="small-icon">✉</span> juanjosemon19@gmail.com</p>
                            <p><span class="small-icon">⌕</span> +57 322-903-5224</p>
                            <p><span class="small-icon">⌖</span> Bogotá, Colombia</p>
                        </div>
                    </article>

                    <div class="stats">

                        <article class="stat">
                            <div class="stat-content">
                                <span>Tareas pendientes</span>
                                <strong>5</strong>
                                <i class="stat-icon yellow">▣</i>
                            </div>
                            <a href="#tareas">Ver detalles <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Inventario total</span>
                                <strong>1,240</strong>
                                <i class="stat-icon green">◇</i>
                            </div>
                            <a href="../inventario_materia_prima/inventario_materia_prima.php">Ver inventario
                                <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Proveedores</span>
                                <strong>36</strong>
                                <i class="stat-icon purple">♙</i>
                            </div>
                            <a href="../lista_proveedores/lista_proveedores.php">Ver proveedores <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Productos</span>
                                <strong>320</strong>
                                <i class="stat-icon blue">◇</i>
                            </div>
                            <a href="../inventario_productos_terminados/inventario_productos_terminados.php">Ver
                                productos <b>›</b></a>
                        </article>

                    </div>
                </section>

                <!-- TAREAS + DERECHA -->
                <section class="dashboard-grid">

                    <article class="tasks card" id="tareas">
                        <div class="title-row">
                            <h3><span>▣</span> Tareas Pendientes</h3>
                        </div>

                        <div class="task-table">
                            <div class="task-row heading">
                                <span>TAREA</span>
                                <span>PRIORIDAD</span>
                                <span>VENCIMIENTO</span>
                                <span>ESTADO</span>
                                <span></span>
                            </div>

                            <div class="task-row">
                                <strong>Solicitar espuma</strong>
                                <span><em class="priority medium">Media</em></span>
                                <span>20 May 2026</span>
                                <span><em class="status">Pendiente</em></span>
                                <span class="dots">⋮</span>
                            </div>

                            <div class="task-row">
                                <strong>Pedido N° 346</strong>
                                <span><em class="priority high">Alta</em></span>
                                <span>18 May 2026</span>
                                <span><em class="status">Pendiente</em></span>
                                <span class="dots">⋮</span>
                            </div>

                            <div class="task-row">
                                <strong>Verificar inventario</strong>
                                <span><em class="priority medium">Media</em></span>
                                <span>21 May 2026</span>
                                <span><em class="status">Pendiente</em></span>
                                <span class="dots">⋮</span>
                            </div>

                            <div class="task-row">
                                <strong>Supervisar el área de producción</strong>
                                <span><em class="priority high">Alta</em></span>
                                <span>19 May 2026</span>
                                <span><em class="status">Pendiente</em></span>
                                <span class="dots">⋮</span>
                            </div>

                            <div class="task-row">
                                <strong>Contactar proveedor N° 12</strong>
                                <span><em class="priority low">Baja</em></span>
                                <span>22 May 2026</span>
                                <span><em class="status">Pendiente</em></span>
                                <span class="dots">⋮</span>
                            </div>
                        </div>

                        <button class="all-tasks" type="button">Ver todas las tareas <b>›</b></button>
                    </article>

                    <aside class="right">

                        <article class="quick card">
                            <div class="title-row">
                                <h3><span>ϟ</span> Acciones rápidas</h3>
                            </div>

                            <div class="quick-grid">
                                <button onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">
                                    <span class="quick-icon yellow-icon">🛒</span>
                                    <b>Nuevo pedido</b>
                                </button>

                                <button
                                    onclick="window.location.href='../registro_de_producto_terminado/registro_producto_terminado.php'">
                                    <span class="quick-icon blue-icon">◇</span>
                                    <b>Registrar producto</b>
                                </button>

                                <button
                                    onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                                    <span class="quick-icon green-icon">↓</span>
                                    <b>Entrada de inventario</b>
                                </button>

                                <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                                    <span class="quick-icon purple-icon">▤</span>
                                    <b>Reporte de inventario</b>
                                </button>
                            </div>
                        </article>

                        <article class="contact card">
                            <div class="title-row">
                                <h3><span>⌕</span> Información de contacto</h3>
                            </div>
                            <p>⌖ <span>Bogotá, Colombia</span></p>
                            <p>✉ <span>contacto@colsoftco.com</span></p>
                            <p>⌕ <span>+57 (1) 234 5678</span></p>
                            <p>◷ <span>Lun - Vie: 8:00 am - 6:00 pm</span></p>
                        </article>

                    </aside>
                </section>
            </main>

            <footer>
                <span>© 2026 <b>COLSOFTCO</b> - Todos los derechos reservados.</span>
                <span>Desarrollado por <b>Equipo SENA</b></span>
            </footer>
        </div>
    </div>

    <script src="../../public/js/app.js"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const nav = document.getElementById('navMenu');
        const openButton = document.getElementById('mobileOpen');
        const menuButton = document.getElementById('mobileMenu');

        function openSidebar() {
            sidebar.classList.add('mobile-visible');
            document.body.classList.add('menu-open');
        }

        function closeSidebar() {
            sidebar.classList.remove('mobile-visible');
            document.body.classList.remove('menu-open');
        }

        openButton.addEventListener('click', openSidebar);

        menuButton.addEventListener('click', () => {
            nav.classList.toggle('open');
            menuButton.setAttribute('aria-expanded', nav.classList.contains('open'));
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 900 &&
                sidebar.classList.contains('mobile-visible') &&
                !sidebar.contains(e.target) &&
                e.target !== openButton) {
                closeSidebar();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) {
                sidebar.classList.remove('mobile-visible');
                nav.classList.remove('open');
                document.body.classList.remove('menu-open');
            }
        });
    </script>

</body>

</html>