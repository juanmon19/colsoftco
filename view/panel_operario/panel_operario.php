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

    <script>
        // ================= TAREAS: CARGA, CREACIÓN, EDICIÓN Y BORRADO (AJAX) =================

        const statusLabels = { 'pendiente': 'Pendiente', 'por-hacer': 'Por hacer', 'terminado': 'Terminado' };
        const priorityLabels = { 'low': 'Baja', 'medium': 'Media', 'high': 'Alta' };

        const menuOverlay = document.getElementById('menuOverlay');
        const tasksListBody = document.getElementById('tasksListBody');
        const statTareas = document.getElementById('statTareas');
        const statInventario = document.getElementById('statInventario');
        const statProveedores = document.getElementById('statProveedores');
        const statProductos = document.getElementById('statProductos');

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function formatearFecha(fechaISO) {
            if (!fechaISO) return 'Sin fecha';
            const [y, m, d] = fechaISO.split('-');
            const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            return `${d} ${meses[parseInt(m, 10) - 1]} ${y}`;
        }

        function actualizarOverlay() {
            const hayMenuAbierto = document.querySelector('.edit-menu.open') !== null;
            menuOverlay.classList.toggle('show', hayMenuAbierto);
        }

        function cerrarTodosLosMenus(exceptoMenu) {
            document.querySelectorAll('.edit-menu.open').forEach((menu) => {
                if (menu !== exceptoMenu) {
                    menu.classList.remove('open');
                    const boton = menu.previousElementSibling;
                    if (boton) boton.setAttribute('aria-expanded', 'false');
                }
            });
            actualizarOverlay();
        }

        function renderTareas(tareas) {
            if (!tareas.length) {
                tasksListBody.innerHTML = '<p class="placeholder">No hay tareas registradas.</p>';
                return;
            }

            tasksListBody.innerHTML = tareas.map((t, i) => `
                <div class="task" data-id="${t.id_tarea}">
                    <div class="task-number">${String(i + 1).padStart(2, '0')}</div>
                    <div class="task-main">
                        <strong>${escapeHtml(t.titulo)}</strong>
                        <span>Vence: ${formatearFecha(t.fecha_vencimiento)}</span>
                    </div>
                    <span class="priority ${t.prioridad}">${priorityLabels[t.prioridad]}</span>
                    <span class="status ${t.estado}">${statusLabels[t.estado]}</span>
                    <div class="task-actions">
                        <button class="task-dots" type="button" aria-label="Editar tarea" aria-expanded="false">⋮</button>
                        <div class="edit-menu">
                            <div class="edit-menu-group">
                                <label>Estado</label>
                                <select class="edit-status">
                                    <option value="pendiente" ${t.estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                                    <option value="por-hacer" ${t.estado === 'por-hacer' ? 'selected' : ''}>Por hacer</option>
                                    <option value="terminado" ${t.estado === 'terminado' ? 'selected' : ''}>Terminado</option>
                                </select>
                            </div>
                            <div class="edit-menu-group">
                                <label>Prioridad</label>
                                <select class="edit-priority">
                                    <option value="low" ${t.prioridad === 'low' ? 'selected' : ''}>Baja</option>
                                    <option value="medium" ${t.prioridad === 'medium' ? 'selected' : ''}>Media</option>
                                    <option value="high" ${t.prioridad === 'high' ? 'selected' : ''}>Alta</option>
                                </select>
                            </div>
                            <button type="button" class="edit-apply">Guardar</button>
                            <button type="button" class="edit-delete">Eliminar</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function cargarTareas() {
            try {
                const resp = await fetch('../../app/logica_tareas.php?accion=listar');
                const data = await resp.json();
                if (!data.ok) {
                    tasksListBody.innerHTML = '<p class="placeholder">No se pudieron cargar las tareas.</p>';
                    return;
                }
                renderTareas(data.tareas);
            } catch (e) {
                tasksListBody.innerHTML = '<p class="placeholder">Error de conexión al cargar tareas.</p>';
                console.error(e);
            }
        }

        async function actualizarStats() {
            try {
                const resp = await fetch('../../app/dashboard_stats.php');
                const data = await resp.json();
                if (!data.ok) return;

                statTareas.textContent = Number(data.tareas_pendientes).toLocaleString('es-CO');
                statInventario.textContent = Number(data.inventario_total).toLocaleString('es-CO');
                statProveedores.textContent = Number(data.proveedores).toLocaleString('es-CO');
                statProductos.textContent = Number(data.productos).toLocaleString('es-CO');
            } catch (e) {
                console.error('Error al actualizar estadísticas', e);
            }
        }

        async function guardarCambiosTarea(boton) {
            const fila = boton.closest('.task');
            const id = fila.dataset.id;
            const estado = fila.querySelector('.edit-status').value;
            const prioridad = fila.querySelector('.edit-priority').value;

            try {
                const resp = await fetch('../../app/logica_tareas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=actualizar&id_tarea=${encodeURIComponent(id)}&estado=${encodeURIComponent(estado)}&prioridad=${encodeURIComponent(prioridad)}`
                });
                const data = await resp.json();
                if (data.ok) {
                    await cargarTareas();
                    actualizarStats();
                } else {
                    alert(data.error || 'No se pudo actualizar la tarea.');
                }
            } catch (e) {
                alert('Error de conexión al actualizar la tarea.');
                console.error(e);
            }
        }

        async function eliminarTarea(boton) {
            const fila = boton.closest('.task');
            const id = fila.dataset.id;
            if (!confirm('¿Eliminar esta tarea?')) return;

            try {
                const resp = await fetch('../../app/logica_tareas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=eliminar&id_tarea=${encodeURIComponent(id)}`
                });
                const data = await resp.json();
                if (data.ok) {
                    await cargarTareas();
                    actualizarStats();
                } else {
                    alert(data.error || 'No se pudo eliminar la tarea.');
                }
            } catch (e) {
                alert('Error de conexión al eliminar la tarea.');
                console.error(e);
            }
        }

        document.addEventListener('click', (e) => {
            const boton = e.target.closest('.task-dots');
            if (boton && tasksListBody.contains(boton)) {
                e.stopPropagation();
                const menu = boton.nextElementSibling;
                const abierto = menu.classList.contains('open');
                cerrarTodosLosMenus(menu);
                menu.classList.toggle('open', !abierto);
                boton.setAttribute('aria-expanded', String(!abierto));
                actualizarOverlay();
                return;
            }

            const guardar = e.target.closest('.edit-apply');
            if (guardar) { e.stopPropagation(); guardarCambiosTarea(guardar); return; }

            const eliminar = e.target.closest('.edit-delete');
            if (eliminar) { e.stopPropagation(); eliminarTarea(eliminar); return; }

            if (e.target.closest('.edit-menu')) { e.stopPropagation(); return; }

            cerrarTodosLosMenus(null);
            cerrarPerfilDropdown();
        });

        menuOverlay.addEventListener('click', () => cerrarTodosLosMenus(null));

        // ================= MODAL: NUEVA TAREA =================

        const btnNuevaTarea = document.getElementById('btnNuevaTarea');
        const modalTareaOverlay = document.getElementById('modalTareaOverlay');
        const formNuevaTarea = document.getElementById('formNuevaTarea');
        const btnCancelarTarea = document.getElementById('btnCancelarTarea');
        const sugerenciasTareas = document.getElementById('sugerenciasTareas');
        const inputTareaTitulo = document.getElementById('tareaTitulo');
        const selectTareaPrioridad = document.getElementById('tareaPrioridad');
        const inputTareaVencimiento = document.getElementById('tareaVencimiento');

        const sugerenciasFijas = [
            'Controlar inventario', 'Empacar productos', 'Etiquetar productos',
            'Apoyar producción', 'Organizar materia prima', 'Reportar novedades'
        ];

        async function cargarSugerencias() {
            let sugerencias = [...sugerenciasFijas];
            try {
                const resp = await fetch('../../app/logica_tareas.php?accion=sugerencias');
                const data = await resp.json();
                if (data.ok && Array.isArray(data.sugerencias)) {
                    sugerencias = [...new Set([...sugerencias, ...data.sugerencias])];
                }
            } catch (e) { /* solo se usan las sugerencias fijas */ }
            sugerenciasTareas.innerHTML = sugerencias.map(s => `<option value="${escapeHtml(s)}"></option>`).join('');
        }

        btnNuevaTarea.addEventListener('click', () => {
            modalTareaOverlay.classList.add('show');
            cargarSugerencias();
            inputTareaTitulo.focus();
        });

        btnCancelarTarea.addEventListener('click', () => {
            modalTareaOverlay.classList.remove('show');
            formNuevaTarea.reset();
        });

        modalTareaOverlay.addEventListener('click', (e) => {
            if (e.target === modalTareaOverlay) modalTareaOverlay.classList.remove('show');
        });

        formNuevaTarea.addEventListener('submit', async (e) => {
            e.preventDefault();
            const titulo = inputTareaTitulo.value.trim();
            const prioridad = selectTareaPrioridad.value;
            const vencimiento = inputTareaVencimiento.value;
            if (!titulo) return;

            try {
                const resp = await fetch('../../app/logica_tareas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=crear&titulo=${encodeURIComponent(titulo)}&prioridad=${encodeURIComponent(prioridad)}&fecha_vencimiento=${encodeURIComponent(vencimiento)}`
                });
                const data = await resp.json();
                if (data.ok) {
                    modalTareaOverlay.classList.remove('show');
                    formNuevaTarea.reset();
                    await cargarTareas();
                    actualizarStats();
                } else {
                    alert(data.error || 'No se pudo registrar la tarea.');
                }
            } catch (e) {
                alert('Error de conexión al registrar la tarea.');
                console.error(e);
            }
        });

        // ================= CARGA INICIAL Y AUTO-ACTUALIZACIÓN =================

        cargarTareas();
        actualizarStats();

        setInterval(() => { actualizarStats(); cargarTareas(); }, 20000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { actualizarStats(); cargarTareas(); }
        });
    </script>

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