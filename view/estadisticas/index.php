<?php
require_once "../../app/verificar_sesion.php";

if (($_SESSION['rol'] ?? '') !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}
$rolActual = 'Administrador';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - COLSOFTCO</title>
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="estadisticas.css">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php include __DIR__ . '/../partials/topbar.php'; ?>

            <main class="content">
                <div class="est-container">

                    <!-- Selector de rango -->
                    <div class="est-card est-controles">
                        <div class="rango-tabs" role="tablist">
                            <button class="rango-tab" data-rango="dia" onclick="cambiarRango('dia')">Día</button>
                            <button class="rango-tab active" data-rango="semana" onclick="cambiarRango('semana')">Semana</button>
                            <button class="rango-tab" data-rango="mes" onclick="cambiarRango('mes')">Mes</button>
                        </div>
                        <div class="rango-fechas">
                            <input type="date" id="inputFecha" onchange="cargarTodo()">
                            <input type="month" id="inputMes" style="display:none;" onchange="cargarTodo()">
                        </div>
                        <p class="est-periodo" id="etiquetaPeriodo">—</p>
                    </div>

                    <!-- KPIs -->
                    <div class="est-kpis">
                        <div class="est-card kpi">
                            <span class="kpi-label">Colchones fabricados</span>
                            <strong class="kpi-num" id="kpiProduccion">—</strong>
                            <small class="kpi-var" id="varProduccion"></small>
                        </div>
                        <div class="est-card kpi">
                            <span class="kpi-label">Cumplimiento tareas</span>
                            <strong class="kpi-num" id="kpiCumplimiento">—</strong>
                            <small class="kpi-var" id="varCumplimiento"></small>
                        </div>
                        <div class="est-card kpi">
                            <span class="kpi-label">Material más usado</span>
                            <strong class="kpi-num small" id="kpiMaterial">—</strong>
                            <small class="kpi-var" id="varMaterial"></small>
                        </div>
                        <div class="est-card kpi">
                            <span class="kpi-label">Tareas vencidas</span>
                            <strong class="kpi-num" id="kpiVencidas">—</strong>
                            <small class="kpi-var" id="varPendientes"></small>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="est-grid">
                        <div class="est-card">
                            <h3>Producción por modelo</h3>
                            <div class="chart-wrap"><canvas id="chModelos"></canvas></div>
                        </div>
                        <div class="est-card">
                            <h3>Tareas: terminadas vs pendientes</h3>
                            <div class="chart-wrap"><canvas id="chTareas"></canvas></div>
                        </div>
                        <div class="est-card">
                            <h3>Consumo top materias primas</h3>
                            <div class="chart-wrap"><canvas id="chConsumo"></canvas></div>
                        </div>
                    </div>

                    <!-- Panel de rendimiento -->
                    <h2 class="est-seccion">Panel de rendimiento</h2>
                    <div class="est-rend">
                        <div class="est-card rend">
                            <h3>Completadas a tiempo</h3>
                            <strong class="rend-num" id="rendATiempo">—</strong>
                            <small class="kpi-var" id="rendATiempoBase"></small>
                        </div>
                        <div class="est-card rend">
                            <h3>Colchones por operario</h3>
                            <div id="tablaOperario" class="mini-tabla">—</div>
                            <h3 class="mt">Por turno</h3>
                            <div class="chart-wrap sm"><canvas id="chTurno"></canvas></div>
                        </div>
                        <div class="est-card rend">
                            <h3>Tiempo prom. creación → cierre</h3>
                            <strong class="rend-num" id="rendCierre">—</strong>
                            <small class="kpi-var" id="rendCierreBase"></small>
                        </div>
                    </div>

                    <p class="est-error" id="estError" style="display:none;"></p>

                </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
    <script src="../../public/js/app.js"></script>
    <script>
        let rango = 'semana';
        let charts = {};

        const GOLD = '#D4AF37', NAVY = '#0A1F44';

        function esOscuro() {
            return document.documentElement.getAttribute('data-tema') === 'oscuro';
        }
        function colorTexto() { return esOscuro() ? '#e8edf5' : '#152846'; }
        function colorGrid() { return esOscuro() ? 'rgba(148,163,184,.18)' : '#e5eaf0'; }

        function cambiarRango(r) {
            rango = r;
            document.querySelectorAll('.rango-tab').forEach(t => t.classList.toggle('active', t.dataset.rango === r));
            document.getElementById('inputFecha').style.display = r === 'mes' ? 'none' : '';
            document.getElementById('inputMes').style.display = r === 'mes' ? '' : 'none';
            cargarTodo();
        }

        function paramsRango() {
            const p = new URLSearchParams({ accion: 'resumen', rango });
            if (rango === 'mes') {
                const v = document.getElementById('inputMes').value; // YYYY-MM
                if (v) { const [a, m] = v.split('-'); p.set('anio', a); p.set('mes', parseInt(m, 10)); }
            } else if (document.getElementById('inputFecha').value) {
                p.set('fecha', document.getElementById('inputFecha').value);
            }
            return p;
        }

        function dibujar(id, cfg) {
            if (charts[id]) charts[id].destroy();
            Chart.defaults.color = colorTexto();
            charts[id] = new Chart(document.getElementById(id), cfg);
        }

        function varTexto(v, sufijo = '%') {
            if (v === null || v === undefined) return '';
            const f = v > 0 ? 'up' : (v < 0 ? 'down' : 'flat');
            const s = v > 0 ? '▲' : (v < 0 ? '▼' : '＝');
            return `<span class="${f}">${s} ${Math.abs(v)}${sufijo} vs anterior</span>`;
        }

        async function cargarTodo() {
            const err = document.getElementById('estError');
            err.style.display = 'none';
            try {
                const resp = await fetch('../../app/estadisticas.php?' + paramsRango());
                const data = await resp.json();
                if (!data.ok) throw new Error(data.error || 'Error');
                pintarResumen(data.resumen);
                await cargarRendimiento(data.resumen.periodo.inicio, data.resumen.periodo.fin);
            } catch (e) {
                err.textContent = e.message || 'Error de conexión.';
                err.style.display = 'block';
            }
        }

        function pintarResumen(r) {
            document.getElementById('etiquetaPeriodo').textContent = 'Periodo: ' + r.periodo.etiqueta;
            const a = r.actual, v = r.variacion;

            document.getElementById('kpiProduccion').textContent = a.produccion.total;
            document.getElementById('varProduccion').innerHTML = varTexto(v.produccion);
            document.getElementById('kpiCumplimiento').textContent = a.tareas.pct_cumplimiento + '%';
            document.getElementById('varCumplimiento').innerHTML = varTexto(v.cumplimiento, ' pts');
            const top = a.consumo[0];
            document.getElementById('kpiMaterial').textContent = top ? top.material : '—';
            document.getElementById('varMaterial').textContent = top ? top.consumo + ' uds.' : '';
            document.getElementById('kpiVencidas').textContent = a.tareas.vencidas;
            document.getElementById('varPendientes').textContent = a.tareas.pendientes + ' pendientes';

            dibujar('chModelos', {
                type: 'bar',
                data: {
                    labels: a.produccion.por_modelo.map(m => m.modelo || 'Modelo'),
                    datasets: [{ data: a.produccion.por_modelo.map(m => m.cantidad), backgroundColor: NAVY, hoverBackgroundColor: GOLD, borderRadius: 6 }]
                },
                options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: colorGrid() } }, x: { grid: { display: false } } } }
            });

            dibujar('chTareas', {
                type: 'doughnut',
                data: {
                    labels: ['Terminadas', 'Pendientes'],
                    datasets: [{ data: [a.tareas.terminadas, a.tareas.pendientes], backgroundColor: [GOLD, '#94a3b8'], borderWidth: 0 }]
                },
                options: { cutout: '62%' }
            });

            dibujar('chConsumo', {
                type: 'bar',
                data: {
                    labels: a.consumo.map(c => c.material),
                    datasets: [{ data: a.consumo.map(c => c.consumo), backgroundColor: GOLD, borderRadius: 6 }]
                },
                options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: colorGrid() } }, y: { grid: { display: false } } } }
            });
        }

        async function cargarRendimiento(inicio, fin) {
            const p = new URLSearchParams({ accion: 'rendimiento', inicio, fin });
            const resp = await fetch('../../app/estadisticas.php?' + p);
            const data = await resp.json();
            if (!data.ok) throw new Error(data.error || 'Error');
            const r = data.rendimiento;

            document.getElementById('rendATiempo').textContent = r.a_tiempo.pct + '%';
            document.getElementById('rendATiempoBase').textContent =
                `${r.a_tiempo.a_tiempo} de ${r.a_tiempo.terminadas} terminadas` +
                (r.a_tiempo.sin_fecha ? ` (${r.a_tiempo.sin_fecha} sin fecha de cierre)` : '');

            document.getElementById('tablaOperario').innerHTML = r.operario.length
                ? r.operario.map(o => `<div class="mini-fila"><span>${o.operario}</span><strong>${o.colchones}</strong></div>`).join('')
                : '<small>Sin producción en el periodo.</small>';

            dibujar('chTurno', {
                type: 'doughnut',
                data: {
                    labels: r.turno.map(t => t.turno),
                    datasets: [{ data: r.turno.map(t => t.colchones), backgroundColor: [GOLD, NAVY, '#94a3b8'], borderWidth: 0 }]
                },
                options: { cutout: '62%' }
            });

            document.getElementById('rendCierre').textContent = r.cierre.horas !== null ? r.cierre.horas + ' h' : '—';
            document.getElementById('rendCierreBase').textContent =
                r.cierre.horas !== null ? `Promedio sobre ${r.cierre.base} tareas` : 'Sin cierres registrados en el periodo';
        }

        // Fechas por defecto: hoy / mes actual
        document.getElementById('inputFecha').valueAsDate = new Date();
        const hoy = new Date();
        document.getElementById('inputMes').value = hoy.toISOString().slice(0, 7);
        cargarTodo();
    </script>
</body>

</html>
