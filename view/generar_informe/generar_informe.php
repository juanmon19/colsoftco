<?php
require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_informes.php'; // Agregamos la lógica aquí

$logica = new InformeLogica();
$mesesDisponibles = $logica->obtenerMesesDisponibles(); // Traemos solo los meses con actividad

$MESES = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Informes</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo (auditoría responsive) -->
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link href="generar_informe.css" rel="stylesheet">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>
<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php
            $rolActual = 'Administrador';
            include __DIR__ . '/../partials/topbar.php';
            ?>

            <main class="content">
<div class="contenido">

        <!-- TARJETA INFORME COMPARATIVO MES vs MES -->
        <div class="informes">
            <h2>COMPARAR MESES</h2>
            <form id="formComparativo" action="generar_informe_excel.php" method="GET" target="_blank">
                <p class="form-text">Seleccione los 2 meses a comparar (una fila por material + diferencias):</p>
                <div class="select-group">
                    <select name="mes_a" id="selMesA" onchange="cargarVistaPrevia()" required>
                        <?php if (empty($mesesDisponibles)): ?>
                            <option value="">Sin datos</option>
                        <?php else: ?>
                            <?php foreach ($mesesDisponibles as $p): ?>
                                <option value="<?= $p['anio'] ?>-<?= $p['mes'] ?>"><?= $MESES[$p['mes']] ?> <?= $p['anio'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select name="mes_b" id="selMesB" onchange="cargarVistaPrevia()" required>
                        <?php if (empty($mesesDisponibles)): ?>
                            <option value="">Sin datos</option>
                        <?php else: ?>
                            <?php $ultimo = end($mesesDisponibles); ?>
                            <?php foreach ($mesesDisponibles as $p): ?>
                                <?php $v = $p['anio'] . '-' . $p['mes']; $vu = $ultimo['anio'] . '-' . $ultimo['mes']; ?>
                                <option value="<?= $v ?>" <?= $v == $vu ? 'selected' : '' ?>><?= $MESES[$p['mes']] ?> <?= $p['anio'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <p class="form-text">Formato:</p>
                <div class="radio-group">
                    <label><input type="radio" name="fmt" value="excel" checked onclick="cambiarRutaComp('generar_informe_excel.php')"> Excel</label>
                    <label><input type="radio" name="fmt" value="pdf" onclick="cambiarRutaComp('generar_informe_pdf.php')"> PDF</label>
                </div>
                <button type="submit" <?= empty($mesesDisponibles) ? 'disabled style="background:gray; cursor:not-allowed;"' : '' ?>>
                    Comparar y descargar
                </button>
            </form>
        </div>

        <!-- TARJETA VISTA PREVIA DEL COMPARATIVO -->
        <div class="materias">
            <h2>VISTA PREVIA</h2>
            <p class="form-text" id="prevRango" style="text-align:center;">Cargando...</p>
            <input type="text" id="buscador" class="mp-buscar informe-buscador" placeholder="Buscar material...">

            <div class="mp-wrap informe-tabla-envolvedora table-responsive">
                <table class="mp-tabla comp informe-tabla-comparativa">
                    <thead>
                        <tr>
                            <th>MATERIAL</th>
                            <th id="thEntA">ENT. A</th>
                            <th id="thSalA">SAL. A</th>
                            <th id="thEntB">ENT. B</th>
                            <th id="thSalB">SAL. B</th>
                            <th>DIF. MOV.</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                        <tr><td colspan="6" style="text-align:center; color:white;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
            <span class="registros-count" id="conteoRegistros">0 de 0 registros</span>
        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script src="../../public/js/app.js"></script>
    <script>
        function cambiarRutaComp(ruta) {
            const f = document.getElementById('formComparativo');
            // conserva mes_a/mes_b y solo cambia el endpoint
            f.action = ruta;
        }

        const MESES_CORTO = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

        function nombreMes(valor) {
            // "2026-8" -> {anio:2026, mes:8}
            const p = String(valor || '').split('-');
            if (p.length === 2) return { anio: p[0], mes: parseInt(p[1], 10) };
            return { anio: new Date().getFullYear(), mes: parseInt(valor, 10) || 1 };
        }

        async function cargarVistaPrevia() {
            const selA = document.getElementById('selMesA');
            const selB = document.getElementById('selMesB');
            const tbody = document.getElementById('tablaMaterias');
            const conteo = document.getElementById('conteoRegistros');
            if (!selA || !selB || !selA.value || !selB.value) return;

            const a = nombreMes(selA.value), b = nombreMes(selB.value);
            document.getElementById('prevRango').textContent =
                `${selA.options[selA.selectedIndex].text} vs ${selB.options[selB.selectedIndex].text}`;
            document.getElementById('thEntA').textContent = 'ENT. ' + (MESES_CORTO[a.mes] || 'A');
            document.getElementById('thSalA').textContent = 'SAL. ' + (MESES_CORTO[a.mes] || 'A');
            document.getElementById('thEntB').textContent = 'ENT. ' + (MESES_CORTO[b.mes] || 'B');
            document.getElementById('thSalB').textContent = 'SAL. ' + (MESES_CORTO[b.mes] || 'B');

            try {
                const resp = await fetch(`obtener_comparativo.php?mes_a=${encodeURIComponent(selA.value)}&mes_b=${encodeURIComponent(selB.value)}`);
                const datos = await resp.json();

                if (datos.error || datos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="color:white; text-align:center;">No hay datos para comparar.</td></tr>';
                    conteo.textContent = '0 de 0 registros';
                    return;
                }

                tbody.innerHTML = datos.map(d => {
                    const salA = d.salidas_a > 0 ? -d.salidas_a : 0;
                    const salB = d.salidas_b > 0 ? -d.salidas_b : 0;
                    const dif = parseFloat(d.dif_mov);
                    const claseDif = dif > 0 ? 'mp-disp' : (dif < 0 ? 'mp-ago' : '');

                    return `
                        <tr class="fila-materia">
                            <td>${d.nombre || 'N/A'}</td>
                            <td>${d.entradas_a}</td>
                            <td style="color:${d.salidas_a > 0 ? '#ff8a8a' : 'white'};">${salA}</td>
                            <td>${d.entradas_b}</td>
                            <td style="color:${d.salidas_b > 0 ? '#ff8a8a' : 'white'};">${salB}</td>
                            <td><span class="mp-badge ${claseDif}">${d.dif_mov}</span></td>
                        </tr>
                    `;
                }).join('');

                conteo.textContent = `${datos.length} de ${datos.length} registros`;

            } catch (e) {
                console.error("Error cargando vista previa", e);
                tbody.innerHTML = '<tr><td colspan="6" style="color:white; text-align:center;">Error al cargar la vista previa.</td></tr>';
            }
        }
        
        // Filtro buscador simple
        document.getElementById('buscador').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const filas = document.querySelectorAll('.fila-materia');
            let visibles = 0;
            
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                if(texto.includes(term)) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });
            
            const total = filas.length;
            document.getElementById('conteoRegistros').textContent = `${visibles} de ${total} registros`;
        });

        cargarVistaPrevia();
    </script>
</body>
</html>