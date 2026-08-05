<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Operario - Max & Flex</title>

    <link rel="stylesheet" href="panel_operario.css">
</head>

<body>

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="header">

        <button
            class="mobile-open"
            id="mobileOpen"
            type="button"
            aria-label="Abrir menú">
            ☰
        </button>

        <div class="header-left">

            <h1>
                BIENVENIDO, JAFET DAVID
            </h1>

            <p>
                Operario
            </p>

        </div>

        <button
            id="btnLogout"
            class="btn-logout"
            onclick="cerrarSesion()">

            Cerrar sesión

        </button>

    </header>


    <!-- =====================================================
         CONTENEDOR
    ====================================================== -->

    <div class="contenedor">


        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <aside
            class="sidebar"
            id="sidebar">


            <!-- LOGO -->

            <div class="brand">

                <img
                    src="../../public/imagenes/logo.png"
                    alt="Logo COLSOFTCO">

                <div>

                    <strong>
                        COLSOFTCO
                    </strong>

                    <span>
                        Sistema de Gestión
                    </span>

                </div>

            </div>


            <!-- BOTÓN MÓVIL -->

            <button
                class="mobile-menu"
                id="menuToggle"
                type="button">

                <span>
                    ☰ Menú
                </span>

                <span>
                    ▾
                </span>

            </button>


            <!-- MENÚ -->

            <nav
                class="sidebar-links"
                id="sidebarLinks">


                <button
                    onclick="window.location.href='../lista_verificacion/lista_verificacion.php'">

                    <span>☑</span>

                    Lista de Verificación

                </button>


                <button
                    onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">

                    <span>♙</span>

                    Lista de Proveedores

                </button>


                <button
                    onclick="window.location.href='../registromp/registromp.php'">

                    <span>＋</span>

                    Registrar Materia Prima

                </button>


                <button
                    onclick="window.location.href='../generar_informe/generar_informe.php'">

                    <span>▤</span>

                    Generar Informe

                </button>


                <button
                    onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">

                    <span>◇</span>

                    Inventario de Materia Prima

                </button>


                <button
                    onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">

                    <span>□</span>

                    Inventario de Productos

                </button>


                <button
                    onclick="window.location.href='../receta_de_colchones/receta_colchones.php'">

                    <span>⚙</span>

                    Receta de Colchones

                </button>


            </nav>


            <!-- AYUDA -->

            <div class="help-box">

                <strong>
                    ¿Necesitas ayuda?
                </strong>

                <p>
                    Nuestro equipo está para apoyarte.
                </p>

                <a href="mailto:contacto@colsoftco.com">
                    Contáctanos
                </a>

            </div>


        </aside>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="contenido">


            <!-- PERFIL -->

            <section class="profile-card">


                <img
                    src="../../public/imagenes/operario.jpg"
                    alt="Jafet David Pineda Céspedes">


                <div class="info-usuario">


                    <div class="profile-heading">

                        <div>

                            <h2>
                                Jafet David Pineda Céspedes
                            </h2>

                            <p>
                                <strong>Rol:</strong>
                                Operario
                            </p>

                        </div>


                        <span class="online">

                            <i></i>

                            Activo

                        </span>

                    </div>


                    <div class="profile-details">

                        <span>
                            ▣ Área de producción
                        </span>

                        <span>
                            ◇ Control de inventario
                        </span>

                        <span>
                            ⌖ Bogotá, Colombia
                        </span>

                    </div>


                </div>


            </section>


            <!-- =================================================
                 TARJETAS
            ================================================== -->

            <section class="summary-grid">


                <article class="summary-card">

                    <div class="summary-icon yellow">
                        ☑
                    </div>

                    <span>
                        Tareas pendientes
                    </span>

                    <strong>
                        5
                    </strong>

                    <small>
                        Actividades por realizar
                    </small>

                </article>


                <article class="summary-card">

                    <div class="summary-icon green">
                        ◇
                    </div>

                    <span>
                        Inventario
                    </span>

                    <strong>
                        1,240
                    </strong>

                    <small>
                        Existencias registradas
                    </small>

                </article>


                <article class="summary-card">

                    <div class="summary-icon purple">
                        ▣
                    </div>

                    <span>
                        Pedidos
                    </span>

                    <strong>
                        8
                    </strong>

                    <small>
                        Por despachar
                    </small>

                </article>


                <article class="summary-card">

                    <div class="summary-icon blue">
                        ⚠
                    </div>

                    <span>
                        Novedades
                    </span>

                    <strong>
                        2
                    </strong>

                    <small>
                        Requieren seguimiento
                    </small>

                </article>


            </section>


            <!-- =================================================
                 DASHBOARD
            ================================================== -->

            <section class="dashboard-grid">


                <!-- TAREAS -->

                <article class="tareas card">


                    <div class="section-title">

                        <div>

                            <span class="section-icon">
                                ☑
                            </span>

                            <div>

                                <h3>
                                    Tareas Pendientes
                                </h3>

                                <p>
                                    Actividades asignadas al operario
                                </p>

                            </div>

                        </div>


                        <span class="task-count">
                            5 pendientes
                        </span>

                    </div>


                    <div class="tasks-list">


                        <!-- TAREA 1 -->

                        <div class="task">

                            <div class="task-number">
                                01
                            </div>

                            <div class="task-main">

                                <strong>
                                    Controlar inventario
                                </strong>

                                <span>
                                    Revisar existencias y movimientos
                                </span>

                            </div>

                            <span class="priority high">
                                Alta
                            </span>

                            <span class="task-date">
                                Pendiente
                            </span>

                        </div>


                        <!-- TAREA 2 -->

                        <div class="task">

                            <div class="task-number">
                                02
                            </div>

                            <div class="task-main">

                                <strong>
                                    Recibir mercancía
                                </strong>

                                <span>
                                    Verificar mercancía recibida
                                </span>

                            </div>

                            <span class="priority medium">
                                Media
                            </span>

                            <span class="task-date">
                                Pendiente
                            </span>

                        </div>


                        <!-- TAREA 3 -->

                        <div class="task">

                            <div class="task-number">
                                03
                            </div>

                            <div class="task-main">

                                <strong>
                                    Despachar pedidos
                                </strong>

                                <span>
                                    Preparar y entregar pedidos
                                </span>

                            </div>

                            <span class="priority high">
                                Alta
                            </span>

                            <span class="task-date">
                                Pendiente
                            </span>

                        </div>


                        <!-- TAREA 4 -->

                        <div class="task">

                            <div class="task-number">
                                04
                            </div>

                            <div class="task-main">

                                <strong>
                                    Organizar la bodega
                                </strong>

                                <span>
                                    Mantener materiales correctamente ubicados
                                </span>

                            </div>

                            <span class="priority low">
                                Baja
                            </span>

                            <span class="task-date">
                                Pendiente
                            </span>

                        </div>


                        <!-- TAREA 5 -->

                        <div class="task">

                            <div class="task-number">
                                05
                            </div>

                            <div class="task-main">

                                <strong>
                                    Reportar novedades
                                </strong>

                                <span>
                                    Registrar novedades encontradas
                                </span>

                            </div>

                            <span class="priority medium">
                                Media
                            </span>

                            <span class="task-date">
                                Pendiente
                            </span>

                        </div>


                    </div>


                </article>


                <!-- =================================================
                     COLUMNA DERECHA
                ================================================== -->

                <aside class="side-content">


                    <!-- ACCIONES -->

                    <article class="quick-actions card">


                        <div class="section-title compact">

                            <div>

                                <span class="section-icon">
                                    ϟ
                                </span>

                                <div>

                                    <h3>
                                        Acciones rápidas
                                    </h3>

                                    <p>
                                        Funciones frecuentes
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="quick-grid">


                            <button
                                onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">

                                <span class="quick-icon blue">
                                    ◇
                                </span>

                                <strong>
                                    Ver inventario
                                </strong>

                            </button>


                            <button
                                onclick="window.location.href='../registromp/registromp.php'">

                                <span class="quick-icon green">
                                    ＋
                                </span>

                                <strong>
                                    Registrar materia prima
                                </strong>

                            </button>


                            <button
                                onclick="window.location.href='../lista_verificacion/lista_verificacion.php'">

                                <span class="quick-icon yellow">
                                    ☑
                                </span>

                                <strong>
                                    Lista de verificación
                                </strong>

                            </button>


                            <button
                                onclick="window.location.href='../generar_informe/generar_informe.php'">

                                <span class="quick-icon purple">
                                    ▤
                                </span>

                                <strong>
                                    Generar informe
                                </strong>

                            </button>


                        </div>


                    </article>


                    <!-- CONTACTO -->

                    <article class="contact-card card">


                        <div class="section-title compact">

                            <div>

                                <span class="section-icon">
                                    ⌕
                                </span>

                                <div>

                                    <h3>
                                        Información de contacto
                                    </h3>

                                    <p>
                                        Soporte y atención
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="contact-list">

                            <p>
                                📍 Bogotá, Colombia
                            </p>

                            <p>
                                ✉ contacto@colsoftco.com
                            </p>

                            <p>
                                📞 +57 (1) 234-5678
                            </p>

                            <p>
                                🕐 Lun - Vie: 8:00 am - 6:00 pm
                            </p>

                        </div>


                    </article>


                </aside>


            </section>


        </main>


    </div>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

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


    <!-- =====================================================
         SCRIPTS
    ====================================================== -->

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>

    <script
        src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js"
        defer>
    </script>


    <script src="../../public/js/app.js"></script>


    <script>

        const sidebar =
            document.getElementById("sidebar");

        const mobileOpen =
            document.getElementById("mobileOpen");

        const menuToggle =
            document.getElementById("menuToggle");

        const sidebarLinks =
            document.getElementById("sidebarLinks");


        /* Abrir sidebar */

        mobileOpen.addEventListener("click", function () {

            sidebar.classList.add("mobile-visible");

        });


        /* Abrir menú interno */

        menuToggle.addEventListener("click", function () {

            sidebarLinks.classList.toggle("abierto");

        });


        /* Cerrar sidebar al hacer clic fuera */

        document.addEventListener("click", function (event) {

            if (
                window.innerWidth <= 900 &&
                sidebar.classList.contains("mobile-visible") &&
                !sidebar.contains(event.target) &&
                event.target !== mobileOpen
            ) {

                sidebar.classList.remove("mobile-visible");

            }

        });

    </script>


</body>

</html>