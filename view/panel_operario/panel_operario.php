<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Panel Operario - Max & Flex</title>

    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="panel_operario.css">
    
    <script>
        /* Aplica el tema guardado ANTES de pintar la página, para evitar parpadeo */
        (function () {
            const temaGuardado = localStorage.getItem('colsoftco_tema');
            if (temaGuardado === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        })();
    </script>
</head>

<body>

    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="header">

        <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

        <div class="header-left">
            <h1 id="saludoHeader">BIENVENIDO, JAFET DAVID</h1>
            <p>Operario</p>
        </div>

        <div class="header-actions">

            <!-- MENÚ DE PERFIL DESPLEGABLE -->
            <div class="perfil-menu" id="perfilMenu">
                <button class="perfil-trigger" id="btnPerfilMenu" type="button" aria-haspopup="true" aria-expanded="false">
                    <img id="avatarHeader" class="avatar-header" src="../../public/imagenes/operario.jpg" alt="Foto de perfil">
                    <span class="perfil-trigger-text">
                        <strong id="nombreHeaderCorto">Jafet David</strong>
                        <small>Operario</small>
                    </span>
                    <span class="perfil-caret">⌄</span>
                </button>

                <div class="perfil-dropdown" id="perfilDropdown">
                    <button type="button" id="btnCambiarFoto">🖼 Cambiar foto de perfil</button>
                    <button type="button" id="btnEditarDatos">✎ Editar mis datos</button>
                    <button type="button" id="btnTemaOscuro">
                        <span id="temaIconoTexto">🌙 Activar tema oscuro</span>
                    </button>
                    <div class="perfil-dropdown-divider"></div>
                    <button type="button" class="perfil-dropdown-logout" onclick="cerrarSesion()">⏻ Cerrar sesión</button>
                </div>
            </div>

            <input type="file" id="inputFotoPerfil" accept="image/png, image/jpeg, image/webp" hidden>

        </div>

    </header>


    <!-- =====================================================
         CONTENEDOR
    ====================================================== -->

    <div class="contenedor">

        <!-- =================================================
             SIDEBAR
        ================================================== -->

        <aside class="sidebar" id="sidebar">

            <div class="brand">
                <img src="../../public/imagenes/logo.png" alt="Logo COLSOFTCO">
                <div>
                    <strong>COLSOFTCO</strong>
                    <span>Sistema de Gestión</span>
                </div>
            </div>

            <button class="mobile-menu" id="menuToggle" type="button">
                <span>☰ Menú</span>
                <span>▾</span>
            </button>

            <nav class="sidebar-links" id="sidebarLinks">

                <button onclick="window.location.href='../historial_movimientos/historial.php'">
                    <span>☑</span> Historial de movimientos
                </button>

                <button onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">
                    <span>♙</span> Lista de Proveedores
                </button>

                <button onclick="window.location.href='../registromp/registromp.php'">
                    <span>＋</span> Registrar Materia Prima
                </button>

                <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                    <span>▤</span> Generar Informe
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

                <button onclick="window.location.href='../mensajeria/mensajeria.php'">
                    <span>📨</span> Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span>
                </button>

            </nav>

            <div class="help-box">
                <strong>¿Necesitas ayuda?</strong>
                <p>Nuestro equipo está para apoyarte.</p>
                <a href="mailto:contacto@colsoftco.com">Contáctanos</a>
            </div>

        </aside>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <main class="contenido">

            <!-- PERFIL -->
            <section class="profile-card">

                <img id="fotoPerfilGrande" src="../../public/imagenes/operario.jpg" alt="Foto de perfil">

                <div class="info-usuario">

                    <div class="profile-heading">
                        <div>
                            <h2 id="nombreCompletoPerfil">Jafet David Pineda Céspedes</h2>
                            <p><strong>Rol:</strong> Operario</p>
                        </div>

                        <span class="online">
                            <i></i>
                            Activo
                        </span>
                    </div>

                    <div class="profile-details">
                        <span>▣ Área de producción</span>
                        <span>◇ Control de inventario</span>
                        <span>⌖ Bogotá, Colombia</span>
                    </div>

                </div>

            </section>

            <!-- TARJETAS (datos reales) -->
            <section class="summary-grid">

                <article class="summary-card">
                    <div class="summary-icon yellow">☑</div>
                    <span>Tareas pendientes</span>
                    <strong id="statTareas">0</strong>
                    <small>Actividades por realizar</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon green">◇</div>
                    <span>Materia prima</span>
                    <strong id="statInventario">0</strong>
                    <small>Existencias registradas</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon purple">▣</div>
                    <span>Productos terminados</span>
                    <strong id="statProductos">0</strong>
                    <small>Unidades disponibles</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon blue">♙</div>
                    <span>Proveedores</span>
                    <strong id="statProveedores">0</strong>
                    <small>Registrados en el sistema</small>
                </article>

            </section>


            <!-- DASHBOARD -->
            <section class="dashboard-grid">

                <!-- TAREAS -->
                <article class="tareas card">

                    <div class="section-title section-title-row">
                        <div>
                            <span class="section-icon">☑</span>
                            <div>
                                <h3>Tareas Pendientes</h3>
                                <p>Actividades asignadas al operario</p>
                            </div>
                        </div>
                        <button id="btnNuevaTarea" class="btn-nueva-tarea" type="button">+ Nueva tarea</button>
                    </div>

                    <div class="tasks-list" id="tasksListBody">
                        <p class="placeholder">Cargando tareas...</p>
                    </div>

                </article>


                <!-- COLUMNA DERECHA -->
                <aside class="side-content">

                    <!-- ACCIONES -->
                    <article class="quick-actions card">

                        <div class="section-title compact">
                            <div>
                                <span class="section-icon">ϟ</span>
                                <div>
                                    <h3>Acciones rápidas</h3>
                                    <p>Funciones frecuentes</p>
                                </div>
                            </div>
                        </div>

                        <div class="quick-grid">

                            <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                                <span class="quick-icon blue">◇</span>
                                <strong>Ver inventario</strong>
                            </button>

                            <button onclick="window.location.href='../registromp/registromp.php'">
                                <span class="quick-icon green">＋</span>
                                <strong>Registrar materia prima</strong>
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

                    <!-- CONTACTO -->
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
                            <p>📍 Bogotá, Colombia</p>
                            <p>✉ contacto@colsoftco.com</p>
                            <p>📞 +57 (1) 234-5678</p>
                            <p>🕐 Lun - Vie: 8:00 am - 6:00 pm</p>
                        </div>

                    </article>

                </aside>

            </section>

        </main>

    </div>


    <!-- ══ MODAL NUEVA TAREA ══ -->
    <div class="modal-overlay" id="modalTareaOverlay">
        <div class="modal-box">
            <h3>Registrar nueva tarea</h3>
            <form id="formNuevaTarea">
                <label for="tareaTitulo">Título</label>
                <input type="text" id="tareaTitulo" list="sugerenciasTareas" placeholder="Ej. Controlar inventario" autocomplete="off" required>
                <datalist id="sugerenciasTareas"></datalist>

                <label for="tareaPrioridad">Prioridad</label>
                <select id="tareaPrioridad">
                    <option value="low">Baja</option>
                    <option value="medium" selected>Media</option>
                    <option value="high">Alta</option>
                </select>

                <label for="tareaVencimiento">Fecha de vencimiento</label>
                <input type="date" id="tareaVencimiento">

                <div class="modal-actions">
                    <button type="button" id="btnCancelarTarea" class="btn-outline">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar tarea</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══ MODAL EDITAR PERFIL ══ -->
    <div class="modal-overlay" id="modalPerfilOverlay">
        <div class="modal-box">
            <h3>Editar mis datos</h3>

            <div class="modal-perfil-foto">
                <img id="fotoPerfilModal" src="../../public/imagenes/operario.jpg" alt="Foto de perfil">
                <button type="button" id="btnCambiarFotoModal">Cambiar foto</button>
            </div>

            <form id="formEditarPerfil">
                <label for="perfilNombre">Nombre</label>
                <input type="text" id="perfilNombre" required>

                <label for="perfilApellido">Apellido</label>
                <input type="text" id="perfilApellido" required>

                <label for="perfilEmail">Correo electrónico</label>
                <input type="email" id="perfilEmail" required>

                <label for="perfilTelefono">Teléfono</label>
                <input type="text" id="perfilTelefono" placeholder="Ej. 3001234567">

                <div class="modal-actions">
                    <button type="button" id="btnCancelarPerfil" class="btn-outline">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="menu-overlay" id="menuOverlay"></div>


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
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script src="../../public/js/app.js"></script>

    <script>
        const sidebar = document.getElementById("sidebar");
        const mobileOpen = document.getElementById("mobileOpen");
        const menuToggle = document.getElementById("menuToggle");
        const sidebarLinks = document.getElementById("sidebarLinks");

        mobileOpen.addEventListener("click", function () {
            sidebar.classList.add("mobile-visible");
        });

        menuToggle.addEventListener("click", function () {
            sidebarLinks.classList.toggle("abierto");
        });

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

    <script src="../../public/js/tareas.js"></script>
    <script src="../../public/js/mensajes_badge.js"></script>

    <script>
        // ================= MENÚ DE PERFIL: DATOS, FOTO Y TEMA =================

        const btnPerfilMenu = document.getElementById('btnPerfilMenu');
        const perfilDropdown = document.getElementById('perfilDropdown');
        const btnCambiarFoto = document.getElementById('btnCambiarFoto');
        const btnCambiarFotoModal = document.getElementById('btnCambiarFotoModal');
        const inputFotoPerfil = document.getElementById('inputFotoPerfil');
        const btnEditarDatos = document.getElementById('btnEditarDatos');
        const btnTemaOscuro = document.getElementById('btnTemaOscuro');
        const temaIconoTexto = document.getElementById('temaIconoTexto');

        const modalPerfilOverlay = document.getElementById('modalPerfilOverlay');
        const formEditarPerfil = document.getElementById('formEditarPerfil');
        const btnCancelarPerfil = document.getElementById('btnCancelarPerfil');
        const perfilNombre = document.getElementById('perfilNombre');
        const perfilApellido = document.getElementById('perfilApellido');
        const perfilEmail = document.getElementById('perfilEmail');
        const perfilTelefono = document.getElementById('perfilTelefono');

        const avatarHeader = document.getElementById('avatarHeader');
        const fotoPerfilGrande = document.getElementById('fotoPerfilGrande');
        const fotoPerfilModal = document.getElementById('fotoPerfilModal');
        const nombreHeaderCorto = document.getElementById('nombreHeaderCorto');
        const nombreCompletoPerfil = document.getElementById('nombreCompletoPerfil');
        const saludoHeader = document.getElementById('saludoHeader');

        function togglePerfilDropdown() {
            const abierto = perfilDropdown.classList.contains('open');
            perfilDropdown.classList.toggle('open', !abierto);
            btnPerfilMenu.setAttribute('aria-expanded', String(!abierto));
        }
        function cerrarPerfilDropdown() {
            perfilDropdown.classList.remove('open');
            btnPerfilMenu.setAttribute('aria-expanded', 'false');
        }

        btnPerfilMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePerfilDropdown();
        });
        perfilDropdown.addEventListener('click', (e) => e.stopPropagation());

        async function cargarPerfil() {
            try {
                const resp = await fetch('../../app/perfil_usuario.php?accion=obtener');
                const data = await resp.json();
                if (!data.ok) return;

                const u = data.usuario;
                const nombreCorto = u.nombre;
                const nombreCompleto = `${u.nombre} ${u.apellido}`;

                nombreHeaderCorto.textContent = nombreCorto;
                nombreCompletoPerfil.textContent = nombreCompleto;
                saludoHeader.textContent = `BIENVENIDO, ${nombreCorto.toUpperCase()}`;

                if (u.foto) {
                    const url = `../../public/imagenes/perfiles/${u.foto}`;
                    avatarHeader.src = url;
                    fotoPerfilGrande.src = url;
                    fotoPerfilModal.src = url;
                }

                perfilNombre.value = u.nombre || '';
                perfilApellido.value = u.apellido || '';
                perfilEmail.value = u.email || '';
                perfilTelefono.value = u.telefono || '';
            } catch (e) {
                console.error('No se pudo cargar el perfil', e);
            }
        }

        function abrirSelectorFoto() {
            cerrarPerfilDropdown();
            inputFotoPerfil.click();
        }
        btnCambiarFoto.addEventListener('click', abrirSelectorFoto);
        btnCambiarFotoModal.addEventListener('click', abrirSelectorFoto);

        inputFotoPerfil.addEventListener('change', async () => {
            const archivo = inputFotoPerfil.files[0];
            if (!archivo) return;

            const formData = new FormData();
            formData.append('accion', 'foto');
            formData.append('foto', archivo);

            try {
                const resp = await fetch('../../app/perfil_usuario.php', { method: 'POST', body: formData });
                const data = await resp.json();
                if (data.ok) {
                    avatarHeader.src = data.url;
                    fotoPerfilGrande.src = data.url;
                    fotoPerfilModal.src = data.url;
                } else {
                    alert(data.error || 'No se pudo actualizar la foto.');
                }
            } catch (e) {
                alert('Error de conexión al subir la foto.');
                console.error(e);
            } finally {
                inputFotoPerfil.value = '';
            }
        });

        btnEditarDatos.addEventListener('click', () => {
            cerrarPerfilDropdown();
            modalPerfilOverlay.classList.add('show');
        });
        btnCancelarPerfil.addEventListener('click', () => modalPerfilOverlay.classList.remove('show'));
        modalPerfilOverlay.addEventListener('click', (e) => {
            if (e.target === modalPerfilOverlay) modalPerfilOverlay.classList.remove('show');
        });

        formEditarPerfil.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const resp = await fetch('../../app/perfil_usuario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=actualizar&nombre=${encodeURIComponent(perfilNombre.value.trim())}` +
                          `&apellido=${encodeURIComponent(perfilApellido.value.trim())}` +
                          `&email=${encodeURIComponent(perfilEmail.value.trim())}` +
                          `&telefono=${encodeURIComponent(perfilTelefono.value.trim())}`
                });
                const data = await resp.json();
                if (data.ok) {
                    modalPerfilOverlay.classList.remove('show');
                    cargarPerfil();
                } else {
                    alert(data.error || 'No se pudieron guardar los cambios.');
                }
            } catch (e) {
                alert('Error de conexión al guardar los cambios.');
                console.error(e);
            }
        });

        function aplicarTextoTema() {
            const esOscuro = document.documentElement.getAttribute('data-tema') === 'oscuro';
            temaIconoTexto.textContent = esOscuro ? '☀ Activar tema claro' : '🌙 Activar tema oscuro';
        }

        btnTemaOscuro.addEventListener('click', () => {
            const esOscuro = document.documentElement.getAttribute('data-tema') === 'oscuro';
            if (esOscuro) {
                document.documentElement.removeAttribute('data-tema');
                localStorage.setItem('colsoftco_tema', 'claro');
            } else {
                document.documentElement.setAttribute('data-tema', 'oscuro');
                localStorage.setItem('colsoftco_tema', 'oscuro');
            }
            aplicarTextoTema();
        });

        aplicarTextoTema();
        cargarPerfil();

        document.addEventListener('click', () => cerrarPerfilDropdown());
    </script>

</body>

</html>