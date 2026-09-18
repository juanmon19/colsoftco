/**
 * calendario_tareas.js — Calendario mensual de tareas estilo Samsung.
 * Muestra mes actual, semana ISO en curso, día de hoy y vencimientos.
 * Lee las mismas tareas individuales (listar) — cada usuario ve las suyas.
 *
 * Requiere: #calTareas, pestañas .view-tabs[data-vista], #taskTableBody
 */

(function () {
    'use strict';

    const BASE_APP = (typeof PREFIJO_APP !== 'undefined') ? PREFIJO_APP : '/colsoftco/';

    const cont = document.getElementById('calTareas');
    const kanban = document.getElementById('taskTableBody');
    if (!cont || !kanban) return;

    const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const DIAS_CORTO = ['lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom'];
    const DOW = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];

    let vista = { anio: null, mes: null }; // mes 0-11 visible
    let seleccion = hoyISO();
    let tareas = [];

    function hoyISO() {
        const h = new Date();
        return iso(h.getFullYear(), h.getMonth() + 1, h.getDate());
    }
    function iso(y, m, d) {
        return `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    }
    function parseISO(s) {
        const [y, m, d] = s.split('-').map(Number);
        return new Date(y, m - 1, d);
    }
    // Semana ISO (como la app de Samsung: "Semana 38")
    function semanaISO(fecha) {
        const d = new Date(Date.UTC(fecha.getFullYear(), fecha.getMonth(), fecha.getDate()));
        const dia = (d.getUTCDay() + 6) % 7;
        d.setUTCDate(d.getUTCDate() - dia + 3);
        const primero = new Date(Date.UTC(d.getUTCFullYear(), 0, 4));
        return 1 + Math.round(((d - primero) / 86400000 - 3 + ((primero.getUTCDay() + 6) % 7)) / 7);
    }
    function nombreCorto(isoStr) {
        const f = parseISO(isoStr);
        return `${DIAS_CORTO[(f.getDay() + 6) % 7]} ${f.getDate()}`;
    }
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function clasePrioridad(p) {
        return p === 'high' ? 'ev-alta' : (p === 'low' ? 'ev-baja' : 'ev-media');
    }

    function pintar() {
        const hoy = hoyISO();
        const { anio, mes } = vista;
        const primero = new Date(anio, mes, 1);
        const offset = (primero.getDay() + 6) % 7; // lunes = 0
        const diasMes = new Date(anio, mes + 1, 0).getDate();
        const diasPrev = new Date(anio, mes, 0).getDate();

        const porDia = {};
        tareas.forEach(t => {
            if (!t.fecha_vencimiento) return;
            (porDia[t.fecha_vencimiento] = porDia[t.fecha_vencimiento] || []).push(t);
        });

        const semSel = semanaISO(parseISO(seleccion));
        const esMesSel = parseISO(seleccion).getMonth() === mes &&
            parseISO(seleccion).getFullYear() === anio;

        let celdas = '';
        for (let i = 0; i < 42; i++) {
            const numDia = i - offset + 1;
            let f, otroMes = false;
            if (numDia < 1) { f = new Date(anio, mes - 1, diasPrev + numDia); otroMes = true; }
            else if (numDia > diasMes) { f = new Date(anio, mes + 1, numDia - diasMes); otroMes = true; }
            else { f = new Date(anio, mes, numDia); }
            const key = iso(f.getFullYear(), f.getMonth() + 1, f.getDate());
            const evs = porDia[key] || [];
            const pills = evs.slice(0, 2).map(t =>
                `<span class="cal-ev ${clasePrioridad(t.prioridad)}">${escapeHtml(t.titulo)}</span>`
            ).join('') + (evs.length > 2 ? `<span class="cal-mas">+${evs.length - 2} más</span>` : '');

            const clases = ['cal-day'];
            if (otroMes) clases.push('otro-mes');
            if (key === hoy) clases.push('hoy');
            if (key === seleccion) clases.push('sel');
            celdas += `<button type="button" class="${clases.join(' ')}" data-fecha="${key}">
                <span class="cal-num">${f.getDate()}</span>${pills}</button>`;
        }

        const evsSel = porDia[seleccion] || [];
        const lista = evsSel.length
            ? evsSel.map(t => `<li><strong>${escapeHtml(t.titulo)}</strong>
                <span class="mini ${t.estado === 'terminado' ? 'term' : (t.estado === 'por-hacer' ? 'hacer' : 'pend')}">${escapeHtml(t.estado)}</span></li>`).join('')
            : '<li class="vacia">Sin vencimientos este día. ¡Buen momento para adelantar trabajo!</li>';

        cont.innerHTML = `
            <div class="cal-head">
                <button type="button" class="cal-nav" id="calPrev" aria-label="Mes anterior">‹</button>
                <div class="cal-titulo">
                    <h4>${MESES[mes]} ${anio}</h4>
                    <span>Semana ${semSel} • Hoy: ${nombreCorto(hoy)}${esMesSel ? '' : ' • Viendo: ' + nombreCorto(seleccion)}</span>
                </div>
                <button type="button" class="cal-nav" id="calNext" aria-label="Mes siguiente">›</button>
                <button type="button" class="cal-hoy" id="calHoy">Hoy</button>
            </div>
            <div class="cal-dow">${DOW.map(d => `<span>${d}</span>`).join('')}</div>
            <div class="cal-grid">${celdas}</div>
            <div class="cal-detalle">
                <h5>Vencen el ${nombreCorto(seleccion)} (${evsSel.length})</h5>
                <ul>${lista}</ul>
            </div>`;

        cont.querySelector('#calPrev').addEventListener('click', () => mover(-1));
        cont.querySelector('#calNext').addEventListener('click', () => mover(1));
        cont.querySelector('#calHoy').addEventListener('click', irHoy);
        cont.querySelectorAll('.cal-day').forEach(b =>
            b.addEventListener('click', () => {
                seleccion = b.dataset.fecha;
                const f = parseISO(seleccion);
                vista = { anio: f.getFullYear(), mes: f.getMonth() };
                pintar();
            }));
    }

    function mover(dir) {
        const f = new Date(vista.anio, vista.mes + dir, 1);
        vista = { anio: f.getFullYear(), mes: f.getMonth() };
        pintar();
    }
    function irHoy() {
        const h = new Date();
        vista = { anio: h.getFullYear(), mes: h.getMonth() };
        seleccion = hoyISO();
        pintar();
    }

    async function cargar() {
        try {
            const resp = await fetch(BASE_APP + 'app/logica_tareas.php?accion=listar');
            const data = await resp.json();
            if (data.ok) {
                tareas = data.tareas || [];
                pintar();
            }
        } catch (e) { /* se reintenta en el siguiente ciclo */ }
    }

    // Pestañas Kanban | Calendario
    const tabs = document.querySelectorAll('.view-tabs [data-vista]');
    function aplicarVista(v) {
        const esCal = v === 'calendario';
        cont.hidden = !esCal;
        kanban.hidden = esCal;
        tabs.forEach(b => b.classList.toggle('active', b.dataset.vista === v));
        try { sessionStorage.setItem('colsoftco_vista_tareas', v); } catch (e) {}
        if (esCal) cargar();
    }
    tabs.forEach(b => b.addEventListener('click', () => aplicarVista(b.dataset.vista)));

    const h = new Date();
    vista = { anio: h.getFullYear(), mes: h.getMonth() };
    let inicial = 'kanban';
    try { inicial = sessionStorage.getItem('colsoftco_vista_tareas') || 'kanban'; } catch (e) {}
    aplicarVista(inicial === 'calendario' ? 'calendario' : 'kanban');

    cargar();
    setInterval(cargar, 30000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) cargar(); });

})();
