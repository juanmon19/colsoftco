/**
 * tareas.js — Módulo compartido de gestión de tareas.
 * Se incluye en los 3 paneles (admin, bodeguero, operario).
 * 
 * Requiere que el HTML tenga estos IDs:
 * - #taskTableBody: contenedor de filas de tareas
 * - #menuOverlay: overlay para menús contextuales
 * - #statTareas: stat card de tareas pendientes
 * - #modalTareaOverlay: overlay del modal de nueva tarea
 * - #formNuevaTarea: formulario de nueva tarea
 * - #tareaTitulo, #tareaPrioridad, #tareaVencimiento: inputs del form
 * - #btnNuevaTarea, #btnCancelarTarea: botones del modal
 * - #sugerenciasTareas: datalist para autocompletado
 */

(function () {
    'use strict';

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

    // Si no existen los elementos, no inicializar
    if (!taskTableBody || !formNuevaTarea) return;

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
            renderTareas(data.tareas);
            // Actualizar stat card
            if (statTareas) {
                const pendientes = data.tareas.filter(t => t.estado === 'pendiente').length;
                statTareas.textContent = pendientes.toLocaleString('es-CO');
            }
        } catch (e) {
            taskTableBody.innerHTML = '<p class="placeholder">Error de conexión al cargar tareas.</p>';
            console.error(e);
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
            } else {
                alert(data.error || 'No se pudo actualizar la tarea.');
            }
        } catch (e) {
            alert('Error de conexión al actualizar la tarea.');
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
            const resp = await fetch('../../app/logica_tareas.php?accion=sugerencias');
            const data = await resp.json();
            if (data.ok && Array.isArray(data.sugerencias)) {
                sugerencias = [...new Set([...sugerencias, ...data.sugerencias])];
            }
        } catch (e) {}
        sugerenciasTareas.innerHTML = sugerencias.map(s => `<option value="${escapeHtml(s)}"></option>`).join('');
    }

    // Event delegation
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
