/**
 * tareas.js — Tareas individuales en Kanban.
 * Se incluye en los 3 paneles (admin, bodeguero, operario).
 *
 * Cada usuario ve SOLO sus tareas; el admin ve las de todos y puede
 * asignar (modal con select de usuario + nombre en la tarjeta).
 *
 * Requiere en el HTML:
 * - #taskTableBody: tablero kanban
 * - #menuOverlay: overlay para menús contextuales
 * - #statTareas: stat card de tareas pendientes
 * - #modalTareaOverlay, #formNuevaTarea, #tareaTitulo, #tareaPrioridad,
 *   #tareaVencimiento, #btnNuevaTarea, #btnCancelarTarea, #sugerenciasTareas
 * - Opcional (solo admin): #wrapAsignado > #tareaAsignado
 */

(function () {
    'use strict';

    const BASE_APP = (typeof PREFIJO_APP !== 'undefined') ? PREFIJO_APP : '/colsoftco/';

    const columnas = [
        { key: 'pendiente',  titulo: 'Pendientes' },
        { key: 'por-hacer',  titulo: 'Por hacer' },
        { key: 'terminado',  titulo: 'Terminadas' }
    ];

    const priorityLabels = {
        'low': 'Baja',
        'medium': 'Media',
        'high': 'Alta'
    };

    const sugerenciasFijas = [
        'Solicitar materia prima',
        'Verificar inventario',
        'Supervisar producción',
        'Contactar proveedor',
        'Generar informe de stock',
        'Revisar pedidos pendientes',
        'Programar mantenimiento de maquinaria'
    ];

    const taskTableBody = document.getElementById('taskTableBody');
    const menuOverlay = document.getElementById('menuOverlay');
    const statTareas = document.getElementById('statTareas');
    const modalTareaOverlay = document.getElementById('modalTareaOverlay');
    const formNuevaTarea = document.getElementById('formNuevaTarea');
    const btnNuevaTarea = document.getElementById('btnNuevaTarea');
    const btnCancelarTarea = document.getElementById('btnCancelarTarea');
    const sugerenciasTareas = document.getElementById('sugerenciasTareas');
    const inputTareaTitulo = document.getElementById('tareaTitulo');
    const inputTareaVencimiento = document.getElementById('tareaVencimiento');
    const wrapAsignado = document.getElementById('wrapAsignado');
    const selectAsignado = document.getElementById('tareaAsignado');

    // Si no existen los elementos, no inicializar
    if (!taskTableBody || !formNuevaTarea) return;

    let esAdmin = false;

    // ══ Establecer fecha mínima (hoy) ══
    if (inputTareaVencimiento) {
        inputTareaVencimiento.min = new Date().toISOString().split('T')[0];
    }

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
        if (!menuOverlay) return;
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

    function tarjetaHTML(t) {
        const asignado = t.asignado
            ? `<span class="kanban-user">👤 ${escapeHtml(t.asignado)}</span>`
            : (esAdmin ? `<span class="kanban-user">👤 Sin asignar</span>` : '');
        const grupoDueno = esAdmin
            ? `<div class="edit-menu-group">
                   <label>Asignada a</label>
                   <select class="edit-owner">
                       <option value="">Sin asignar</option>
                       ${(window.__tareasUsuarios || []).map(u =>
                           `<option value="${u.id_usuario}" ${String(t.id_usuario ?? '') === String(u.id_usuario) ? 'selected' : ''}>${escapeHtml(u.nombre + ' ' + u.apellido)} (${escapeHtml(u.rol)})</option>`
                       ).join('')}
                   </select>
               </div>`
            : '';
        return `
        <article class="kanban-card priority-${t.prioridad}" draggable="true" data-id="${t.id_tarea}">
            <div class="kanban-card-top">
                <strong>${escapeHtml(t.titulo)}</strong>
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
                        ${grupoDueno}
                        <button type="button" class="edit-apply">Guardar</button>
                        <button type="button" class="edit-delete">Eliminar</button>
                    </div>
                </div>
            </div>
            <div class="kanban-meta">
                <span><em class="priority ${t.prioridad}">${priorityLabels[t.prioridad]}</em></span>
                <span>${formatearFecha(t.fecha_vencimiento)}</span>
            </div>
            ${asignado}
        </article>`;
    }

    function renderTareas(tareas) {
        taskTableBody.innerHTML = columnas.map(col => {
            const items = tareas.filter(t => t.estado === col.key);
            const tarjetas = items.length
                ? items.map(tarjetaHTML).join('')
                : '<p class="kanban-empty">Sin tareas. Arrastra aquí o crea una nueva.</p>';
            return `
            <div class="kanban-col" data-estado="${col.key}">
                <div class="kanban-head">
                    <span class="kanban-dot"></span>
                    <h4>${col.titulo}</h4>
                    <span class="kanban-count">${items.length}</span>
                </div>
                <div class="kanban-list" data-estado="${col.key}">${tarjetas}</div>
            </div>`;
        }).join('');
    }

    function configurarAsignado(data) {
        esAdmin = !!data.es_admin;
        window.__tareasUsuarios = Array.isArray(data.usuarios) ? data.usuarios : [];
        if (!wrapAsignado || !selectAsignado) return;
        if (!esAdmin || !Array.isArray(data.usuarios)) {
            wrapAsignado.style.display = 'none';
            return;
        }
        wrapAsignado.style.display = '';
        const actual = selectAsignado.value;
        selectAsignado.innerHTML = '<option value="">Para mí</option>' +
            data.usuarios.map(u =>
                `<option value="${u.id_usuario}">${escapeHtml(u.nombre + ' ' + u.apellido)} (${escapeHtml(u.rol)})</option>`
            ).join('');
        if (actual) selectAsignado.value = actual;
    }

    async function cargarTareas() {
        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php?accion=listar');
            const data = await resp.json();
            if (!data.ok) {
                taskTableBody.innerHTML = '<p class="placeholder">No se pudieron cargar las tareas.</p>';
                return;
            }
            configurarAsignado(data);
            renderTareas(data.tareas);
            // Actualizar stat card
            if (statTareas) {
                const pendientes = data.tareas.filter(t => t.estado === 'pendiente').length;
                statTareas.textContent = pendientes.toLocaleString('es-CO');
            }
            // Resumen al pie de la tarjeta (si la página lo incluye)
            const resumen = document.getElementById('tasksSummary');
            if (resumen) {
                const t = data.tareas || [];
                const pend = t.filter(x => x.estado === 'pendiente').length;
                const hacer = t.filter(x => x.estado === 'por-hacer').length;
                const term = t.filter(x => x.estado === 'terminado').length;
                const pct = t.length ? Math.round(term / t.length * 100) : 0;
                resumen.innerHTML =
                    `<span class="sum-pill">Total <b>${t.length}</b></span>` +
                    `<span class="sum-pill pend">Pendientes <b>${pend}</b></span>` +
                    `<span class="sum-pill hacer">Por hacer <b>${hacer}</b></span>` +
                    `<span class="sum-pill term">Terminadas <b>${term}</b></span>` +
                    `<div class="tasks-progress"><i style="width:${pct}%"></i></div>` +
                    `<span>${pct}% terminado</span>`;
            }
        } catch (e) {
            taskTableBody.innerHTML = '<p class="placeholder">Error de conexión al cargar tareas.</p>';
            console.error(e);
        }
    }

    async function guardarCambiosTarea(boton) {
        const tarjeta = boton.closest('.kanban-card');
        const id = tarjeta.dataset.id;
        const estado = tarjeta.querySelector('.edit-status').value;
        const prioridad = tarjeta.querySelector('.edit-priority').value;
        const selDueno = tarjeta.querySelector('.edit-owner');

        let body = `accion=actualizar&id_tarea=${encodeURIComponent(id)}&estado=${encodeURIComponent(estado)}&prioridad=${encodeURIComponent(prioridad)}`;
        if (esAdmin && selDueno) {
            body += `&id_usuario=${encodeURIComponent(selDueno.value ? selDueno.value : '0')}`;
        }

        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            });
            const data = await resp.json();
            if (data.ok) {
                await cargarTareas();
            } else {
                alert(data.error || 'No se pudo actualizar la tarea.');
            }
        } catch (e) {
            alert('Error de conexión al actualizar la tarea.');
        }
    }

    async function moverTarea(id, estado) {
        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=actualizar&id_tarea=${encodeURIComponent(id)}&estado=${encodeURIComponent(estado)}`
            });
            const data = await resp.json();
            if (!data.ok) alert(data.error || 'No se pudo mover la tarea.');
        } catch (e) {
            alert('Error de conexión al mover la tarea.');
        }
        await cargarTareas();
    }

    async function eliminarTarea(boton) {
        const tarjeta = boton.closest('.kanban-card');
        const id = tarjeta.dataset.id;
        if (!confirm('¿Eliminar esta tarea?')) return;

        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=eliminar&id_tarea=${encodeURIComponent(id)}`
            });
            const data = await resp.json();
            if (data.ok) {
                await cargarTareas();
            } else {
                alert(data.error || 'No se pudo eliminar la tarea.');
            }
        } catch (e) {
            alert('Error de conexión.');
        }
    }

    async function cargarSugerencias() {
        if (!sugerenciasTareas) return;
        let sugerencias = [...sugerenciasFijas];
        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php?accion=sugerencias');
            const data = await resp.json();
            if (data.ok && Array.isArray(data.sugerencias)) {
                sugerencias = [...new Set([...sugerencias, ...data.sugerencias])];
            }
        } catch (e) {}
        sugerenciasTareas.innerHTML = sugerencias.map(s => `<option value="${escapeHtml(s)}"></option>`).join('');
    }

    // Event delegation: menú ⋮, guardar, eliminar
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
        if (guardar) { e.stopPropagation(); guardarCambiosTarea(guardar); return; }

        const eliminar = e.target.closest('.edit-delete');
        if (eliminar) { e.stopPropagation(); eliminarTarea(eliminar); return; }

        if (e.target.closest('.edit-menu')) { e.stopPropagation(); return; }

        cerrarTodosLosMenus(null);
    });

    if (menuOverlay) {
        menuOverlay.addEventListener('click', () => cerrarTodosLosMenus(null));
    }

    // Arrastrar y soltar entre columnas
    let idArrastrado = null;
    document.addEventListener('dragstart', (e) => {
        const tarjeta = e.target.closest('.kanban-card');
        if (!tarjeta || !taskTableBody.contains(tarjeta)) return;
        idArrastrado = tarjeta.dataset.id;
        tarjeta.classList.add('dragging');
        try { e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', idArrastrado); } catch (err) {}
    });
    document.addEventListener('dragend', () => {
        idArrastrado = null;
        document.querySelectorAll('.kanban-card.dragging').forEach(t => t.classList.remove('dragging'));
        document.querySelectorAll('.kanban-list.drag-over').forEach(l => l.classList.remove('drag-over'));
    });
    document.addEventListener('dragover', (e) => {
        const lista = e.target.closest('.kanban-list');
        if (!lista || !taskTableBody.contains(lista)) return;
        e.preventDefault();
        lista.classList.add('drag-over');
        try { e.dataTransfer.dropEffect = 'move'; } catch (err) {}
    });
    document.addEventListener('dragleave', (e) => {
        const lista = e.target.closest('.kanban-list');
        if (lista && !lista.contains(e.relatedTarget)) lista.classList.remove('drag-over');
    });
    document.addEventListener('drop', (e) => {
        const lista = e.target.closest('.kanban-list');
        if (!lista || !taskTableBody.contains(lista)) return;
        e.preventDefault();
        lista.classList.remove('drag-over');
        const nuevoEstado = lista.dataset.estado;
        const id = idArrastrado || (function () { try { return e.dataTransfer.getData('text/plain'); } catch (err) { return null; } })();
        if (id && nuevoEstado) moverTarea(id, nuevoEstado);
    });

    // Modal: nueva tarea
    if (btnNuevaTarea) {
        btnNuevaTarea.addEventListener('click', () => {
            modalTareaOverlay.classList.add('show');
            cargarSugerencias();
            if (inputTareaTitulo) inputTareaTitulo.focus();
            // Refresh min date
            if (inputTareaVencimiento) {
                inputTareaVencimiento.min = new Date().toISOString().split('T')[0];
            }
        });
    }

    if (btnCancelarTarea) {
        btnCancelarTarea.addEventListener('click', () => {
            modalTareaOverlay.classList.remove('show');
            formNuevaTarea.reset();
        });
    }

    if (modalTareaOverlay) {
        modalTareaOverlay.addEventListener('click', (e) => {
            if (e.target === modalTareaOverlay) modalTareaOverlay.classList.remove('show');
        });
    }

    formNuevaTarea.addEventListener('submit', async (e) => {
        e.preventDefault();
        const titulo = inputTareaTitulo.value.trim();
        const prioridad = document.getElementById('tareaPrioridad').value;
        const vencimiento = inputTareaVencimiento.value;

        if (!titulo) return;

        // Validación frontend de fecha pasada
        if (vencimiento && vencimiento < new Date().toISOString().split('T')[0]) {
            alert('No se puede asignar una fecha de vencimiento en el pasado.');
            return;
        }

        let body = `accion=crear&titulo=${encodeURIComponent(titulo)}&prioridad=${encodeURIComponent(prioridad)}&fecha_vencimiento=${encodeURIComponent(vencimiento)}`;
        if (esAdmin && selectAsignado && selectAsignado.value) {
            body += `&id_usuario=${encodeURIComponent(selectAsignado.value)}`;
        }

        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            });
            const data = await resp.json();
            if (data.ok) {
                modalTareaOverlay.classList.remove('show');
                formNuevaTarea.reset();
                await cargarTareas();
            } else {
                alert(data.error || 'No se pudo registrar la tarea.');
            }
        } catch (e) {
            alert('Error de conexión.');
        }
    });

    // Initial load + auto-refresh
    cargarTareas();
    setInterval(cargarTareas, 20000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) cargarTareas();
    });

})();
