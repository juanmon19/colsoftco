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
    <link href="generar_informe.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/layout.css">
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
        
        <!-- TARJETA GENERAR INFORME -->
        <div class="informes">
            <h2>GENERAR INFORME</h2>
            
            <form id="formReporte" action="generar_informe_excel.php" method="GET" target="_blank">
                
                <p class="form-text">Marque la herramienta en la que desea generar el informe:</p>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="formato" value="excel" checked onclick="cambiarRuta('generar_informe_excel.php')"> 
                        Excel
                    </label>
                    <label>
                        <input type="radio" name="formato" value="pdf" onclick="cambiarRuta('generar_informe_pdf.php')"> 
                        PDF
                    </label>
                </div>

                <p class="form-text">Digite los meses a comparar:</p>
                <div class="select-group">
                    <!-- Selector de Mes Inicio -->
                    <select name="mes_inicio" required>
                        <?php if (empty($mesesDisponibles)): ?>
                            <option value="">Sin datos</option>
                        <?php else: ?>
                            <?php foreach ($mesesDisponibles as $num): ?>
                                <option value="<?= $num ?>"><?= $MESES[$num] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                    <!-- Selector de Mes Fin -->
                    <select name="mes_fin" required>
                        <?php if (empty($mesesDisponibles)): ?>
                            <option value="">Sin datos</option>
                        <?php else: ?>
                            <?php $ultimo = end($mesesDisponibles); // Selecciona por defecto el último mes disponible ?>
                            <?php foreach ($mesesDisponibles as $num): ?>
                                <option value="<?= $num ?>" <?= $num == $ultimo ? 'selected' : '' ?>><?= $MESES[$num] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Si no hay meses disponibles, el botón se bloquea y se pone gris -->
                <button type="submit" id="btnGenerar" <?= empty($mesesDisponibles) ? 'disabled style="background:gray; cursor:not-allowed;" title="No hay movimientos registrados"' : '' ?>>
                    Generar
                </button>
            </form>
        </div>

        <!-- TARJETA MATERIAS PRIMAS -->
        <div class="materias">
            <h2>MATERIAS PRIMAS</h2>
            <input type="text" id="buscador" class="mp-buscar" placeholder="Buscar por nombre o categoría...">
            
            <div class="mp-wrap">
                <table class="mp-tabla">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>CATEGORÍA</th>
                            <th>STOCK</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                        <tr><td colspan="4" style="text-align:center; color:white;">Cargando...</td></tr>
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
        function cambiarRuta(ruta) {
            document.getElementById('formReporte').action = ruta;
        }

        async function cargarMaterias() {
            try {
                const resp = await fetch('obtener_materias.php'); 
                const materias = await resp.json();
                const tbody = document.getElementById('tablaMaterias');
                const conteo = document.getElementById('conteoRegistros');
                
                if (materias.error || materias.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="color:white; text-align:center;">No hay datos disponibles.</td></tr>';
                    conteo.textContent = '0 de 0 registros';
                    return;
                }

                tbody.innerHTML = materias.map(m => {
                    let claseBadge = 'mp-disp';
                    if (m.estado === 'Agotado') claseBadge = 'mp-ago';
                    if (m.estado === 'Limitado') claseBadge = 'mp-lim';
                    
                    return `
                        <tr class="fila-materia">
                            <td>${m.nombre || 'N/A'}</td>
                            <td>${m.categoria || 'N/A'}</td>
                            <td>${m.stock || '0'}</td>
                            <td><span class="mp-badge ${claseBadge}">${m.estado || 'Desconocido'}</span></td>
                        </tr>
                    `;
                }).join('');

                conteo.textContent = `${materias.length} de ${materias.length} registros`;

            } catch (e) {
                console.error("Error cargando materias", e);
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

        cargarMaterias();
    </script>
</body>
</html>