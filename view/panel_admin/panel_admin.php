<?php

require_once "../../app/verificar_sesion.php";
require_once "../../config/conexion.php";

$conexion = new Conexion();
$db = $conexion->getConnection();

$inventarioTotal  = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM materias_primas")->fetchColumn();
$proveedoresTotal = (int) $db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
$productosTotal   = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM productos_terminados")->fetchColumn();

/* Si aún no has corrido crear_tabla_tareas.sql, esto no rompe la página */
try {
    $tareasPendientes = (int) $db->query("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente'")->fetchColumn();
} catch (Exception $e) {
    $tareasPendientes = 0;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - COLSOFTCO</title>
    <link rel="stylesheet" href="paneladmin.css">
    <style>
        .btn-nueva-tarea {
            margin-left: auto;
            padding: 8px 14px;
            border: 1px solid #1e3a8a;
            background: #1e3a8a;
            color: #fff;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-nueva-tarea:hover {
            background: #16296b;
        }

        .title-row {
            display: flex;
            align-items: center;
        }

        .edit-delete {
            margin-top: 6px;
            width: 100%;
            padding: 8px;
            border: 1px solid #dc2626;
            background: #fff;
            color: #dc2626;
            border-radius: 6px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
        }

        .edit-delete:hover {
            background: #fef2f2;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-tarea {
            background: #fff;
            border-radius: 10px;
            padding: 24px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-tarea h3 {
            margin: 0 0 16px;
            font-size: 18px;
            color: #1e293b;
        }

        .modal-tarea label {
            display: block;
            margin-top: 12px;
            margin-bottom: 6px;
            font-weight: 700;
            font-size: 13px;
            color: #1e293b;
        }

        .modal-tarea input,
        .modal-tarea select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d7dee7;
            border-radius: 6px;
            font-size: 14px;
            background: #f8fafc;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .modal-actions .btn-outline {
            padding: 9px 16px;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
        }

        .modal-actions .btn-primary {
            padding: 9px 16px;
            border: none;
            background: #1e3a8a;
            color: #fff;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="menu-overlay" id="menuOverlay"></div>

    <!-- ══ MODAL NUEVA TAREA ══ -->
    <div class="modal-overlay" id="modalTareaOverlay">
        <div class="modal-tarea">
            <h3>Registrar nueva tarea</h3>

            <form id="formNuevaTarea">
                <label for="tareaTitulo">Título</label>
                <input type="text" id="tareaTitulo" list="sugerenciasTareas"
                    placeholder="Ej. Solicitar espuma" autocomplete="off" required>
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
                <button class="nav-item" onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
                <button class="nav-item" onclick="window.location.href='../historial_movimientos/historial.php'">Historial de Movimientos</button>
                <button class="nav-item" onclick="window.location.href='../generar_informe/generar_informe.php'">Generar Informe</button>
                <button class="nav-item" onclick="window.location.href='../registromp/registromp.php'">Registrar Materia Prima</button>
                <button class="nav-item" onclick="window.location.href='../control_de_stock/control_de_stock.php'">Control de Stock</button>
                <button class="nav-item" onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
                <button class="nav-item" onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
                <button class="nav-item" onclick="window.location.href='../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
                <button class="nav-item" onclick="window.location.href='../receta_de_colchones/receta_colchones.php'">Receta de Colchones</button>
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
                                <strong id="statTareas"><?= $tareasPendientes ?></strong>
                                <i class="stat-icon yellow">▣</i>
                            </div>
                            <a href="#tareas">Ver detalles <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Inventario total</span>
                                <strong id="statInventario"><?= number_format($inventarioTotal, 0, ',', '.') ?></strong>
                                <i class="stat-icon green">◇</i>
                            </div>
                            <a href="../inventario_materia_prima/inventario_materia_prima.php">Ver inventario
                                <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Proveedores</span>
                                <strong id="statProveedores"><?= $proveedoresTotal ?></strong>
                                <i class="stat-icon purple">♙</i>
                            </div>
                            <a href="../lista_proveedores/lista_proveedores.php">Ver proveedores <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Productos</span>
                                <strong id="statProductos"><?= number_format($productosTotal, 0, ',', '.') ?></strong>
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
                            <button id="btnNuevaTarea" class="btn-nueva-tarea" type="button">+ Nueva tarea</button>
                        </div>

                        <div class="task-table">
                            <div class="task-row heading">
                                <span>TAREA</span>
                                <span>PRIORIDAD</span>
                                <span>VENCIMIENTO</span>
                                <span>ESTADO</span>
                                <span></span>
                            </div>

                            <div id="taskTableBody">
                                <p class="placeholder">Cargando tareas...</p>
                            </div>
                        </div>
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

    <script>
        // ================= TAREAS: CARGA, CREACIÓN, EDICIÓN Y BORRADO (AJAX) =================

        const statusLabels = {
            'pendiente': 'Pendiente',
            'por-hacer': 'Por hacer',
            'terminado': 'Terminado'
        };

        const priorityLabels = {
            'low': 'Baja',
            'medium': 'Media',
            'high': 'Alta'
        };

        const menuOverlay = document.getElementById('menuOverlay');
        const taskTableBody = document.getElementById('taskTableBody');
        const statTareas = document.getElementById('statTareas');
        const statInventario = document.getElementById('statInventario');
        const statProveedores = document.getElementById('statProveedores');
        const statProductos = document.getElementById('statProductos');

        let tareasCache = [];

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
                taskTableBody.innerHTML = '<p class="placeholder">No hay tareas registradas.</p>';
                return;
            }

            taskTableBody.innerHTML = tareas.map(t => `
                <div class="task-row" data-id="${t.id_tarea}">
                    <strong>${escapeHtml(t.titulo)}</strong>
                    <span><em class="priority ${t.prioridad}">${priorityLabels[t.prioridad]}</em></span>
                    <span>${formatearFecha(t.fecha_vencimiento)}</span>
                    <span><em class="status ${t.estado}">${statusLabels[t.estado]}</em></span>
                    <div class="task-actions">
                        <button class="dots" type="button" aria-label="Editar tarea" aria-expanded="false">⋮</button>
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
                    taskTableBody.innerHTML = '<p class="placeholder">No se pudieron cargar las tareas.</p>';
                    return;
                }

                tareasCache = data.tareas;
                renderTareas(tareasCache);
            } catch (e) {
                taskTableBody.innerHTML = '<p class="placeholder">Error de conexión al cargar tareas.</p>';
                console.error(e);
            }
        }

        async function actualizarStats() {
            try {
                const resp = await fetch('../../app/dashboard_stats.php');
                const data = await resp.json();
                if (!data.ok) return;

                if (statTareas) statTareas.textContent = Number(data.tareas_pendientes).toLocaleString('es-CO');
                if (statInventario) statInventario.textContent = Number(data.inventario_total).toLocaleString('es-CO');
                if (statProveedores) statProveedores.textContent = Number(data.proveedores).toLocaleString('es-CO');
                if (statProductos) statProductos.textContent = Number(data.productos).toLocaleString('es-CO');
            } catch (e) {
                console.error('Error al actualizar estadísticas', e);
            }
        }

        async function guardarCambiosTarea(boton) {
            const fila = boton.closest('.task-row');
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
            const fila = boton.closest('.task-row');
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

        // Delegación de eventos: los task-row se crean dinámicamente
        document.addEventListener('click', (e) => {
            const boton = e.target.closest('.dots');
            if (boton && taskTableBody.contains(boton)) {
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
            if (guardar) {
                e.stopPropagation();
                guardarCambiosTarea(guardar);
                return;
            }

            const eliminar = e.target.closest('.edit-delete');
            if (eliminar) {
                e.stopPropagation();
                eliminarTarea(eliminar);
                return;
            }

            if (e.target.closest('.edit-menu')) {
                e.stopPropagation();
                return;
            }

            cerrarTodosLosMenus(null);
        });

        menuOverlay.addEventListener('click', () => cerrarTodosLosMenus(null));

        // ================= MODAL: REGISTRAR NUEVA TAREA =================

        const btnNuevaTarea = document.getElementById('btnNuevaTarea');
        const modalTareaOverlay = document.getElementById('modalTareaOverlay');
        const formNuevaTarea = document.getElementById('formNuevaTarea');
        const btnCancelarTarea = document.getElementById('btnCancelarTarea');
        const sugerenciasTareas = document.getElementById('sugerenciasTareas');
        const inputTareaTitulo = document.getElementById('tareaTitulo');
        const selectTareaPrioridad = document.getElementById('tareaPrioridad');
        const inputTareaVencimiento = document.getElementById('tareaVencimiento');

        const sugerenciasFijas = [
            'Solicitar materia prima',
            'Verificar inventario',
            'Supervisar producción',
            'Contactar proveedor',
            'Generar informe de stock',
            'Revisar pedidos pendientes',
            'Programar mantenimiento de maquinaria'
        ];

        async function cargarSugerencias() {
            let sugerencias = [...sugerenciasFijas];

            try {
                const resp = await fetch('../../app/logica_tareas.php?accion=sugerencias');
                const data = await resp.json();
                if (data.ok && Array.isArray(data.sugerencias)) {
                    sugerencias = [...new Set([...sugerencias, ...data.sugerencias])];
                }
            } catch (e) {
                // si falla, se usan solo las sugerencias fijas
            }

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
            if (e.target === modalTareaOverlay) {
                modalTareaOverlay.classList.remove('show');
            }
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

        // Refresca las 4 tarjetas y la lista de tareas cada 20 segundos,
        // así el panel refleja cambios hechos desde cualquier otro módulo
        // (ventas, inventario, proveedores) sin que el usuario recargue.
        setInterval(() => {
            actualizarStats();
            cargarTareas();
        }, 20000);

        // También se actualiza al volver a esta pestaña del navegador
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                actualizarStats();
                cargarTareas();
            }
        });
    </script>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>