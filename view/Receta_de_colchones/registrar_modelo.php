<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

$conexion = new Conexion();
$dbConn = $conexion->getConnection();

$materialesDisponibles = $dbConn->query("
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
    <title>Registrar Modelo de Colchón</title>
    <link href="registrar_modelo.css" rel="stylesheet">
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
<div class="paneles" style="grid-template-columns: 1fr;">
        <button style="background:#0A1F44;color:#fff;border:none;border-radius:8px;padding:10px 16px;font-size:14px;cursor:pointer;margin:4px 6px 4px 0;" onclick="window.location.href='receta_colchones.php'">← Volver a Receta de Colchones</button>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- PANEL REGISTRAR NUEVO MODELO + RECETA         -->
    <!-- ══════════════════════════════════════════════ -->
    <div class="paneles" style="grid-template-columns: 1fr;">

        <div class="panel">
            <div class="panel-header">📋 Registrar Nuevo Modelo de Colchón</div>
            <div class="panel-body">

                <div style="display:grid; grid-template-columns:1fr; gap:16px; margin-bottom:18px;">
                    <div>
                        <label style="display:block;font-weight:bold;font-size:14px;margin-bottom:6px;">Nombre del Modelo</label>
                        <input type="text" id="regNombreModelo" placeholder="Ej: Colchón Imperial" style="width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;">
                    </div>
                </div>

                <h3 style="font-size:16px;margin-bottom:12px;color:#0A1F44;">🧪 Receta (Obligatoria)</h3>
                <p style="font-size:13px;color:#666;margin-bottom:12px;">Agrega los materiales que componen este modelo de colchón.</p>

                <div id="regRecetaContainer">
                    <!-- Filas de ingredientes se agregan aquí dinámicamente -->
                </div>

                <button type="button" class="btn" style="background:#16a34a;color:white;margin-top:10px;margin-bottom:18px;padding:10px 18px;font-size:14px;" onclick="agregarFilaReceta('reg')">
                    ＋ Agregar Material
                </button>

                <div id="regMensaje" style="display:none;padding:10px 14px;border-radius:6px;font-weight:600;margin-bottom:12px;"></div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <button type="button" class="btn" style="background:#6b7280;color:white;" onclick="limpiarFormRegistro()">
                        Limpiar
                    </button>
                    <button type="button" class="btn" style="background:#0A1F44;color:white;" onclick="registrarModelo()">
                        💾 Registrar Modelo
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
<script>
        /* ══════════════════════════════════════════
           DATOS DE MATERIALES PARA SELECTS DINÁMICOS
           ══════════════════════════════════════════ */
        const materialesDisponibles = <?= json_encode($materialesDisponibles) ?>;

        function crearSelectMaterial(selectedId = '') {
            let options = '<option value="">-- Seleccionar material --</option>';
            materialesDisponibles.forEach(m => {
                const sel = (String(m.id_material) === String(selectedId)) ? 'selected' : '';
                const unidad = m.nombre_unidad ? ` (${m.nombre_unidad})` : '';
                options += `<option value="${m.id_material}" ${sel}>${m.nombre_material}${unidad}</option>`;
            });
            return options;
        }

        /* ══════════════════════════════════════════
           AGREGAR / QUITAR FILAS DE RECETA
           ══════════════════════════════════════════ */
        function agregarFilaReceta(prefix, idMaterial = '', cantidad = '') {
            const container = document.getElementById(prefix + 'RecetaContainer');
            const fila = document.createElement('div');
            fila.style.cssText = 'display:flex;gap:12px;align-items:center;margin-bottom:10px;';
            fila.innerHTML = `
                <select class="receta-material" style="flex:2;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;">
                    ${crearSelectMaterial(idMaterial)}
                </select>
                <input type="number" class="receta-cantidad" placeholder="Cantidad" step="0.01" min="0.01"
                       value="${cantidad}"
                       style="flex:1;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;">
                <button type="button" onclick="this.parentElement.remove()"
                        style="background:#dc2626;color:white;border:none;border-radius:8px;padding:10px 14px;cursor:pointer;font-weight:bold;font-size:14px;"
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
                receta.push({ id_material: idMaterial, cantidad: cantidad });
            }

            return receta;
        }

        function mostrarMensaje(elementId, texto, tipo) {
            const el = document.getElementById(elementId);
            el.style.display = 'block';
            el.textContent = texto;
            el.style.background = tipo === 'ok' ? '#e5f7ec' : '#fdecea';
            el.style.color = tipo === 'ok' ? '#1e7e42' : '#c0392b';
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

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>