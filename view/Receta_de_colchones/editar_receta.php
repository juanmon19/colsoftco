<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

$conexion = new Conexion();
$dbConn = $conexion->getConnection();

$modelos = $dbConn->query(
    "SELECT id_modelo, nombre_modelo FROM modelos_colchon ORDER BY nombre_modelo ASC"
)->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Editar Receta de Colchón</title>
    <link href="editar_receta.css" rel="stylesheet">
</head>

<body>

    <header>
        <a class="logo" href="../../app/ir_panel.php">
            <img src="../../public/imagenes/logo.png" alt="logo">
        </a>
        <span class="header-title">Editar Receta de Colchón</span>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="paneles" style="grid-template-columns: 1fr;">
        <button class="nav-item" onclick="window.location.href='receta_colchones.php'">← Volver a Receta de Colchones</button>
    </div>

    <!-- ══════════════════════════════════════════════ -->
    <!-- PANEL EDITAR RECETA DE MODELO EXISTENTE       -->
    <!-- ══════════════════════════════════════════════ -->
    <div class="paneles" style="grid-template-columns: 1fr;">

        <div class="panel">
            <div class="panel-header">✏️ Editar Receta de Modelo Existente</div>
            <div class="panel-body">

                <label style="display:block;font-weight:bold;font-size:14px;margin-bottom:6px;">Seleccionar Modelo</label>
                <div style="display:flex;gap:12px;margin-bottom:18px;">
                    <select id="editSelectModelo" style="flex:1;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;">
                        <option value="">-- Seleccionar modelo --</option>
                        <?php foreach ($modelos as $m): ?>
                            <option value="<?= $m['id_modelo'] ?>">
                                <?= htmlspecialchars($m['nombre_modelo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn" style="background:#0A1F44;color:white;" onclick="cargarReceta()">
                        🔍 Cargar Receta
                    </button>
                </div>

                <div id="editRecetaSection" style="display:none;">
                    <h3 style="font-size:16px;margin-bottom:12px;color:#0A1F44;">🧪 Receta Actual</h3>

                    <div id="editRecetaContainer">
                        <!-- Filas de ingredientes se cargan aquí -->
                    </div>

                    <button type="button" class="btn" style="background:#16a34a;color:white;margin-top:10px;margin-bottom:18px;padding:10px 18px;font-size:14px;" onclick="agregarFilaReceta('edit')">
                        ＋ Agregar Material
                    </button>

                    <div id="editMensaje" style="display:none;padding:10px 14px;border-radius:6px;font-weight:600;margin-bottom:12px;"></div>

                    <div style="display:flex;gap:12px;justify-content:flex-end;">
                        <button type="button" class="btn" style="background:#0A1F44;color:white;" onclick="guardarReceta()">
                            💾 Guardar Cambios
                        </button>
                    </div>
                </div>

                <div id="editPlaceholder">
                    <p class="placeholder">Selecciona un modelo y presiona <strong>Cargar Receta</strong> para ver y editar su receta.</p>
                </div>

            </div>
        </div>

    </div>

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
           CARGAR RECETA DE MODELO EXISTENTE
           ══════════════════════════════════════════ */
        async function cargarReceta() {
            const idModelo = document.getElementById('editSelectModelo').value;

            if (!idModelo) {
                alert('Selecciona un modelo primero.');
                return;
            }

            try {
                const resp = await fetch(`../../app/logica_modelos.php?accion=obtener_receta&id_modelo=${idModelo}`);
                const data = await resp.json();

                if (!data.ok) {
                    alert(data.error);
                    return;
                }

                document.getElementById('editPlaceholder').style.display = 'none';
                document.getElementById('editRecetaSection').style.display = 'block';

                const container = document.getElementById('editRecetaContainer');
                container.innerHTML = '';

                if (data.receta.length === 0) {
                    container.innerHTML = '<p style="color:#666;font-size:14px;">Este modelo no tiene receta. Agrega materiales abajo.</p>';
                } else {
                    data.receta.forEach(item => {
                        agregarFilaReceta('edit', item.id_material, item.cantidad_requerida);
                    });
                }

                document.getElementById('editMensaje').style.display = 'none';

            } catch (e) {
                alert('Error de conexión.');
                console.error(e);
            }
        }

        /* ══════════════════════════════════════════
           GUARDAR CAMBIOS DE RECETA
           ══════════════════════════════════════════ */
        async function guardarReceta() {
            const idModelo = document.getElementById('editSelectModelo').value;

            if (!idModelo) {
                mostrarMensaje('editMensaje', 'No hay modelo seleccionado.', 'error');
                return;
            }

            const receta = recolectarReceta('edit');

            if (!receta || receta.length === 0) {
                mostrarMensaje('editMensaje', 'La receta es obligatoria. Agrega al menos un material con cantidad válida.', 'error');
                return;
            }

            // Verificar materiales duplicados
            const ids = receta.map(r => r.id_material);
            if (new Set(ids).size !== ids.length) {
                mostrarMensaje('editMensaje', 'No puedes repetir el mismo material en la receta.', 'error');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('accion', 'actualizar_receta');
                formData.append('id_modelo', idModelo);
                formData.append('receta', JSON.stringify(receta));

                const resp = await fetch('../../app/logica_modelos.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await resp.json();

                if (data.ok) {
                    mostrarMensaje('editMensaje', data.mensaje, 'ok');
                } else {
                    mostrarMensaje('editMensaje', data.error, 'error');
                }
            } catch (e) {
                mostrarMensaje('editMensaje', 'Error de conexión.', 'error');
                console.error(e);
            }
        }
    </script>

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>
