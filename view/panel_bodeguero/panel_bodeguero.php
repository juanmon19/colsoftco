<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Bodeguero - Max & Flex</title>
    <link rel="stylesheet" href="panelbodeguero.css">
</head>

<body>
<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="../../public/imagenes/logo.png" alt="COLSOFTCO">
            <div>
                <strong>COLSOFTCO</strong>
                <span>Sistema de Gestión</span>
            </div>
        </div>

        <button class="mobile-menu" id="btnMenuToggle" type="button"
                aria-expanded="false" aria-controls="sidebarLinks">
            <span>☰ Menú</span>
            <span class="toggle-icono">⌄</span>
        </button>

        <nav class="sidebar-links" id="sidebarLinks">
            <button onclick="window.location.href='../historial_movimientos/historial.php'">
                <span>☑</span> Historial de Moviemientos
            </button>
            <button onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">
                <span>♙</span> Lista de Proveedores
            </button>
            <button onclick="window.location.href='../registromp/registromp.php'">
                <span>＋</span> Registrar Materia Prima
            </button>
            <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                <span>▥</span> Generar Informe
            </button>
            <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                <span>◇</span> Inventario de Materia Prima
            </button>
            <button onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">
                <span>□</span> Inventario de Productos
            </button>
            <button onclick="window.location.href='../receta_de_colchones/receta_colchones.php'">
                <span>⚙</span> Receta de Colchones
            </button>
        </nav>

        <div class="help-box">
            <div class="help-icon">♧</div>
            <div>
                <strong>¿Necesitas ayuda?</strong>
                <p>Nuestro equipo está<br>para apoyarte.</p>
                <a href="mailto:contacto@colsoftco.com">Contáctanos</a>
            </div>
        </div>
    </aside>

    <div class="main">

        <!-- HEADER -->
        <header class="header">
            <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

            <div class="header-left">
                <div>
                    <h1>BIENVENIDO, NICOLÁS SANTIAGO</h1>
                    <p>Bodeguero</p>
                </div>
            </div>

            <div class="header-actions">
                <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">Cerrar sesión</button>
            </div>
        </header>

        <main class="content">

            <!-- PERFIL -->
            <section class="profile-card">
                <img src="../../public/imagenes/bodeguero.jpeg" alt="Nicolás Santiago Polo Moreno">

                <div class="profile-info">
                    <div class="profile-heading">
                        <div>
                            <h2>Nicolás Santiago Polo Moreno</h2>
                            <p><strong>Rol:</strong> Bodeguero</p>
                        </div>
                        <span class="online"><i></i> Activo</span>
                    </div>

                    <div class="profile-details">
                        <span>▣ Gestión de bodega</span>
                        <span>◇ Control de inventario</span>
                        <span>⌖ Bogotá, Colombia</span>
                    </div>
                </div>
            </section>

            <!-- RESUMEN DEL BODEGUERO -->
            <section class="summary-grid">
                <article class="summary-card">
                    <div class="summary-icon yellow">☑</div>
                    <span>Tareas pendientes</span>
                    <strong>6</strong>
                    <small>Actividades por realizar</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon green">◇</div>
                    <span>Materia prima</span>
                    <strong>1,240</strong>
                    <small>Existencias registradas</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon purple">▣</div>
                    <span>Pedidos</span>
                    <strong>12</strong>
                    <small>Por organizar o revisar</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon blue">⚠</div>
                    <span>Novedades</span>
                    <strong>3</strong>
                    <small>Requieren seguimiento</small>
                </article>
            </section>

            <!-- TAREAS + ACCIONES -->
            <section class="dashboard-grid">

                <article class="tasks card">
                    <div class="section-title">
                        <div>
                            <span class="section-icon">☑</span>
                            <div>
                                <h3>Tareas Pendientes</h3>
                                <p>Actividades de la bodega que requieren seguimiento</p>
                            </div>
                        </div>
                        <span class="task-count">6 pendientes</span>
                    </div>

                    <div class="tasks-list">

                        <div class="task">
                            <div class="task-number">01</div>
                            <div class="task-main">
                                <strong>Mover mercancía</strong>
                                <span>Trasladar materiales dentro de la bodega</span>
                            </div>
                            <span class="priority medium">Media</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                        <div class="task">
                            <div class="task-number">02</div>
                            <div class="task-main">
                                <strong>Recibir mercancía</strong>
                                <span>Verificar mercancía recibida</span>
                            </div>
                            <span class="priority high">Alta</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                        <div class="task">
                            <div class="task-number">03</div>
                            <div class="task-main">
                                <strong>Organizar pedidos</strong>
                                <span>Preparar y ordenar pedidos</span>
                            </div>
                            <span class="priority medium">Media</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                        <div class="task">
                            <div class="task-number">04</div>
                            <div class="task-main">
                                <strong>Organizar la bodega</strong>
                                <span>Mantener materiales correctamente ubicados</span>
                            </div>
                            <span class="priority low">Baja</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                        <div class="task">
                            <div class="task-number">05</div>
                            <div class="task-main">
                                <strong>Control de inventario</strong>
                                <span>Revisar existencias y movimientos</span>
                            </div>
                            <span class="priority high">Alta</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                        <div class="task">
                            <div class="task-number">06</div>
                            <div class="task-main">
                                <strong>Reportar novedades</strong>
                                <span>Registrar novedades encontradas</span>
                            </div>
                            <span class="priority medium">Media</span>
                            <span class="task-date">Pendiente</span>
                        </div>

                    </div>

                    <button class="all-tasks" type="button">Ver todas las tareas <b>›</b></button>
                </article>

                <aside class="side-content">

                    <!-- ACCIONES -->
                    <article class="quick-actions card">
                        <div class="section-title compact">
                            <div>
                                <span class="section-icon">ϟ</span>
                                <div>
                                    <h3>Acciones rápidas</h3>
                                    <p>Funciones frecuentes del bodeguero</p>
                                </div>
                            </div>
                        </div>

                        <div class="quick-grid">
                            <button onclick="window.location.href='../registromp/registromp.php'">
                                <span class="quick-icon green">＋</span>
                                <strong>Registrar materia prima</strong>
                            </button>

                            <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                                <span class="quick-icon blue">◇</span>
                                <strong>Ver inventario</strong>
                            </button>

                            <button onclick="window.location.href='../lista_verificacion/lista_verificacion.php'">
                                <span class="quick-icon yellow">☑</span>
                                <strong>Lista de verificación</strong>
                            </button>

                            <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                                <span class="quick-icon purple">▤</span>
                                <strong>Generar informe</strong>
                            </button>
                        </div>
                    </article>

                    <!-- INFORMACIÓN -->
                    <article class="contact-card card">
                        <div class="section-title compact">
                            <div>
                                <span class="section-icon">⌕</span>
                                <div>
                                    <h3>Información de contacto</h3>
                                    <p>Soporte y atención</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-list">
                            <p><span>⌖</span> Bogotá, Colombia</p>
                            <p><span>✉</span> contacto@colsoftco.com</p>
                            <p><span>⌕</span> +57 (1) 234-5678</p>
                            <p><span>◷</span> Lun - Vie: 8:00 am - 6:00 pm</p>
                        </div>
                    </article>

                </aside>
            </section>
        </main>

        <!-- FOOTER -->
        <footer>
            <div class="footer-main">
                <div>
                    <strong class="footer-brand">COLSOFTCO</strong>
                    <span class="footer-sub">SISTEMA DE GESTIÓN</span>
                    <p>
                        Sistema de gestión y administración de materias primas para Max&Flex.
                        Eficiencia en inventarios y movimientos empresariales.
                    </p>
                </div>

                <div class="footer-contact">
                    <strong>CONTACTO</strong>
                    <span>📍 Bogotá, Colombia</span>
                    <span>✉ contacto@colsoftco.com</span>
                    <span>📞 +57 (1) 234-5678</span>
                    <span>◷ Lun - Vie: 8:00 am - 6:00 pm</span>
                </div>
            </div>

            <div class="footer-bottom">
                <span>© 2026 <b>COLSOFTCO</b> · Max&Flex. Todos los derechos reservados.</span>
                <span>Desarrollado por <b>Equipo SENA</b></span>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

<script src="../../public/js/app.js"></script>

<script>
const sidebar = document.getElementById('sidebar');
const sidebarLinks = document.getElementById('sidebarLinks');
const menuToggle = document.getElementById('btnMenuToggle');
const mobileOpen = document.getElementById('mobileOpen');

function openSidebar() {
    sidebar.classList.add('mobile-visible');
    document.body.classList.add('menu-open');
}

function closeSidebar() {
    sidebar.classList.remove('mobile-visible');
    document.body.classList.remove('menu-open');
}

mobileOpen.addEventListener('click', openSidebar);

menuToggle.addEventListener('click', function () {
    sidebarLinks.classList.toggle('abierto');
    menuToggle.classList.toggle('abierto');
    menuToggle.setAttribute(
        'aria-expanded',
        sidebarLinks.classList.contains('abierto')
    );
});

document.addEventListener('click', function (event) {
    if (
        window.innerWidth <= 900 &&
        sidebar.classList.contains('mobile-visible') &&
        !sidebar.contains(event.target) &&
        event.target !== mobileOpen
    ) {
        closeSidebar();
    }
});

window.addEventListener('resize', function () {
    if (window.innerWidth > 900) {
        closeSidebar();
        sidebarLinks.classList.remove('abierto');
        menuToggle.classList.remove('abierto');
    }
});
</script>

</body>
</html>