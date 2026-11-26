<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

$conexion = new Conexion();
$dbConn =$conexion->getConnection();

$materialesDisponibles =$dbConn->query("
    SELECT mp.id_material, mp.nombre_material, um.nombre_unidad
    FROM materias_primas mp
    LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
    WHERE mp.estado = 'activo'
    ORDER BY mp.nombre_material ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../partials/seo.php'; ?>
    <title>Registrar Modelo de Colchón</title>
    <!-- Orden estándar: global -> layout -> módulo -->
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link href="registrar_modelo.css" rel="stylesheet">
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
                <div class="paneles" style="grid-template-columns: 1fr;">
                    <button class="btn-back" onclick="window.location.href='receta_colchones.php'">← Volver</button>
                </div>

                <!-- ══════════════════════════════════════════════ -->
                <!-- PANEL REGISTRAR NUEVO MODELO + RECETA         -->
                <!-- ══════════════════════════════════════════════ -->
                <div class="paneles" style="grid-template-columns: 1fr;">

                    <div class="panel">
                        <div class="panel-header">Registrar Nuevo Modelo de Colchón</div>
                        <div class="panel-body">

                            <div class="campo-grupo">
                                <div>
                                    <label class="campo-label" for="regNombreModelo">Nombre del Modelo</label>
                                    <input type="text" id="regNombreModelo" placeholder="Ej: Colchón Imperial">
                                </div>
                            </div>

                            <h3 class="receta-titulo">Receta (Obligatoria)</h3>
                            <p class="receta-desc">Agrega los materiales que componen este modelo de colchón.</p>

                            <div id="regRecetaContainer">
                                <!-- Filas de ingredientes se agregan aquí dinámicamente -->
                            </div>

                            <button type="button" class="btn btn-agregar" onclick="agregarFilaReceta('reg')">
                                ＋ Agregar Material
                            </button>

                            <div id="regMensaje" class="mensaje-estado"></div>

                            <div class="acciones-formulario">
                                <button type="button" class="btn btn-limpiar" onclick="limpiarFormRegistro()">
                                    Limpiar
                                </button>
                                <button type="button" class="btn btn-registrar" onclick="registrarModelo()">
                                    Registrar Modelo
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
    <script src="../../public/js/escape-html.js"></script>
    <script>
        /* ══════════════════════════════════════════
           DATOS DE MATERIALES PARA SELECTS DINÁMICOS
           ══════════════════════════════════════════ */
        const materialesDisponibles = <?= json_encode($materialesDisponibles) ?>;

        function crearSelectMaterial(selectedId = '') {
            let options = '<option value="">-- Seleccionar material --</option>';
            materialesDisponibles.forEach(m => {
                const sel = (String(m.id_material) === String(selectedId)) ? 'selected' : '';
                const unidad = m.nombre_unidad ? ` (${escaparHTML(m.nombre_unidad)})` : '';
                options += `<option value="${m.id_material}" ${sel}>${escaparHTML(m.nombre_material)}${unidad}</option>`;
            });
            return options;
        }

        /* ══════════════════════════════════════════
           AGREGAR / QUITAR FILAS DE RECETA
           ══════════════════════════════════════════ */
        function agregarFilaReceta(prefix, idMaterial = '', cantidad = '') {
            const container = document.getElementById(prefix + 'RecetaContainer');
            const fila = document.createElement('div');
            fila.className = 'receta-fila';
            fila.innerHTML = `
                <select class="receta-material">
                    ${crearSelectMaterial(idMaterial)}
                </select>
                <input type="number" class="receta-cantidad" placeholder="Cantidad" step="0.01" min="0.01"
                       value="${cantidad}">
                <button type="button" class="btn-quitar" onclick="this.parentElement.remove()"
                        title="Quitar material">✕</button>
            `;
            container.appendChild(fila);
        }

        function recolectarReceta(prefix) {
            const container = document.getElementById(prefix + 'RecetaContainer');
            const filas = container.querySelectorAll('div');
            const receta = [];

            for (const fila of filas) {
                const sel = fila.querySelector('.receta-material');
                const inp = fila.querySelector('.receta-cantidad');
                if (!sel || !inp) continue;

                const idMaterial = sel.value;
                const cantidad = parseFloat(inp.value);

                if (!idMaterial || isNaN(cantidad) || cantidad <= 0) {
                    return null; // Validación fallida
                }
                receta.push({
                    id_material: idMaterial,
                    cantidad: cantidad
                });
            }

            return receta;
        }

        function mostrarMensaje(elementId, texto, tipo) {
            const el = document.getElementById(elementId);
            el.textContent = texto;
            el.className = 'mensaje-estado ' + (tipo === 'ok' ? 'mensaje-ok' : 'mensaje-error');
            el.style.display = 'block';
        }

        /* ══════════════════════════════════════════
           REGISTRAR NUEVO MODELO + RECETA
           ══════════════════════════════════════════ */
        async function registrarModelo() {
            const nombre = document.getElementById('regNombreModelo').value.trim();

            if (!nombre) {
                mostrarMensaje('regMensaje', 'El nombre del modelo es obligatorio.', 'error');
                return;
            }

            const receta = recolectarReceta('reg');

            if (!receta || receta.length === 0) {
                mostrarMensaje('regMensaje', 'La receta es obligatoria. Agrega al menos un material con cantidad válida.', 'error');
                return;
            }

            // Verificar materiales duplicados
            const ids = receta.map(r => r.id_material);
            if (new Set(ids).size !== ids.length) {
                mostrarMensaje('regMensaje', 'No puedes repetir el mismo material en la receta.', 'error');
                return;
            }

            try {
                const formData = new FormData();
                formData.set('csrf_token', <?= json_encode(generarTokenCSRF()) ?>);
                formData.append('accion', 'registrar_modelo');
                formData.append('nombre_modelo', nombre);
                formData.append('receta', JSON.stringify(receta));

                const resp = await fetch('../../app/logica_modelos.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await resp.json();

                if (data.ok) {
                    mostrarMensaje('regMensaje', data.mensaje, 'ok');
                    limpiarFormRegistro();
                } else {
                    mostrarMensaje('regMensaje', data.error, 'error');
                }
            } catch (e) {
                mostrarMensaje('regMensaje', 'Error de conexión.', 'error');
                console.error(e);
            }
        }

        function limpiarFormRegistro() {
            document.getElementById('regNombreModelo').value = '';
            document.getElementById('regRecetaContainer').innerHTML = '';
        }
    </script>



</body>

</html>