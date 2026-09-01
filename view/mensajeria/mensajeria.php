<?php
require_once "../../app/verificar_sesion.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajería Interna - COLSOFTCO</title>
    <link rel="stylesheet" href="mensajeria.css">
</head>

<body>

    <header>
        <a class="logo" href="../../app/ir_panel.php">
            <img src="../../public/imagenes/logo.png" alt="Logo">
            <h1>Mensajería Interna</h1>
        </a>
        <button class="btn-volver" onclick="window.location.href='../../app/ir_panel.php'">← Volver al Panel</button>
    </header>

    <div class="container">

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="bandeja" onclick="cambiarTab('bandeja')">📥 Bandeja de entrada</button>
            <button class="tab" data-tab="enviados" onclick="cambiarTab('enviados')">📤 Enviados</button>
            <button class="tab" data-tab="componer" onclick="cambiarTab('componer')">✏️ Componer mensaje</button>
        </div>

        <!-- Bandeja -->
        <div class="tab-content" id="tab-bandeja">
            <div class="card">
                <div id="listaBandeja">
                    <p class="placeholder">Cargando mensajes...</p>
                </div>
            </div>
        </div>

        <!-- Enviados -->
        <div class="tab-content" id="tab-enviados" style="display:none;">
            <div class="card">
                <div id="listaEnviados">
                    <p class="placeholder">Cargando mensajes...</p>
                </div>
            </div>
        </div>

        <!-- Componer -->
        <div class="tab-content" id="tab-componer" style="display:none;">
            <div class="card">
                <h3>Nuevo Mensaje</h3>
                <form id="formMensaje">
                    <label for="destinatario">Destinatario</label>
                    <select id="destinatario" required>
                        <option value="">-- Seleccionar --</option>
                    </select>

                    <label for="asunto">Asunto</label>
                    <input type="text" id="asunto" placeholder="Ej: Maquinaria dañada" required>

                    <label for="contenido">Mensaje</label>
                    <textarea id="contenido" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>

                    <div id="msgResultado" style="display:none;"></div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">📨 Enviar Mensaje</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <footer>
        © 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.
    </footer>

    <script src="../../public/js/app.js"></script>
    <script>
        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str ?? '';
            return d.innerHTML;
        }

        function formatFecha(f) {
            if (!f) return '';
            const d = new Date(f);
            return d.toLocaleDateString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Tabs
        function cambiarTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
            document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
            document.getElementById(`tab-${tab}`).style.display = 'block';
            if (tab === 'bandeja') cargarBandeja();
            if (tab === 'enviados') cargarEnviados();
            if (tab === 'componer') cargarDestinatarios();
        }

        // Bandeja
        async function cargarBandeja() {
            const div = document.getElementById('listaBandeja');
            try {
                const resp = await fetch('../../app/logica_mensajes.php?accion=bandeja');
                const data = await resp.json();
                if (!data.ok || !data.mensajes.length) {
                    div.innerHTML = '<p class="placeholder">No tienes mensajes.</p>';
                    return;
                }
                div.innerHTML = data.mensajes.map(m => `
                <div class="mensaje-item ${m.leido == 0 ? 'no-leido' : ''}" onclick="marcarLeido(${m.id_mensaje}, this)">
                    <div class="mensaje-header">
                        <strong>${escHtml(m.remitente_nombre)} ${escHtml(m.remitente_apellido)}</strong>
                        <small class="badge-rol">${escHtml(m.remitente_rol)}</small>
                        <span class="mensaje-fecha">${formatFecha(m.fecha_envio)}</span>
                        ${m.leido == 0 ? '<span class="badge-nuevo">Nuevo</span>' : ''}
                    </div>
                    <div class="mensaje-asunto">${escHtml(m.asunto)}</div>
                    <div class="mensaje-contenido">${escHtml(m.contenido)}</div>
                </div>
            `).join('');
            } catch (e) {
                div.innerHTML = '<p class="placeholder">Error al cargar mensajes.</p>';
            }
        }

        // Enviados
        async function cargarEnviados() {
            const div = document.getElementById('listaEnviados');
            try {
                const resp = await fetch('../../app/logica_mensajes.php?accion=enviados');
                const data = await resp.json();
                if (!data.ok || !data.mensajes.length) {
                    div.innerHTML = '<p class="placeholder">No has enviado mensajes.</p>';
                    return;
                }
                div.innerHTML = data.mensajes.map(m => `
                <div class="mensaje-item">
                    <div class="mensaje-header">
                        <strong>Para: ${escHtml(m.destinatario_nombre)} ${escHtml(m.destinatario_apellido)}</strong>
                        <small class="badge-rol">${escHtml(m.destinatario_rol)}</small>
                        <span class="mensaje-fecha">${formatFecha(m.fecha_envio)}</span>
                    </div>
                    <div class="mensaje-asunto">${escHtml(m.asunto)}</div>
                    <div class="mensaje-contenido">${escHtml(m.contenido)}</div>
                </div>
            `).join('');
            } catch (e) {
                div.innerHTML = '<p class="placeholder">Error al cargar mensajes.</p>';
            }
        }

        // Destinatarios
        async function cargarDestinatarios() {
            const sel = document.getElementById('destinatario');
            try {
                const resp = await fetch('../../app/logica_mensajes.php?accion=listar_usuarios');
                const data = await resp.json();
                if (!data.ok) return;
                sel.innerHTML = '<option value="">-- Seleccionar destinatario --</option>' +
                    data.usuarios.map(u => `<option value="${u.id_usuario}">${u.nombre} ${u.apellido} (${u.rol})</option>`).join('');
            } catch (e) {
                console.error(e);
            }
        }

        // Marcar leído
        async function marcarLeido(id, el) {
            if (el.classList.contains('no-leido')) {
                try {
                    await fetch('../../app/logica_mensajes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `accion=leer&id_mensaje=${id}`
                    });
                    el.classList.remove('no-leido');
                    el.querySelector('.badge-nuevo')?.remove();
                } catch (e) {}
            }
        }

        // Enviar
        document.getElementById('formMensaje').addEventListener('submit', async (e) => {
            e.preventDefault();
            const dest = document.getElementById('destinatario').value;
            const asunto = document.getElementById('asunto').value.trim();
            const contenido = document.getElementById('contenido').value.trim();
            const msgDiv = document.getElementById('msgResultado');

            if (!dest || !asunto || !contenido) return;

            try {
                const formData = new FormData();
                formData.append('accion', 'enviar');
                formData.append('id_destinatario', dest);
                formData.append('asunto', asunto);
                formData.append('contenido', contenido);

                const resp = await fetch('../../app/logica_mensajes.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await resp.json();

                msgDiv.style.display = 'block';
                if (data.ok) {
                    msgDiv.className = 'msg-ok';
                    msgDiv.textContent = data.mensaje;
                    document.getElementById('formMensaje').reset();
                } else {
                    msgDiv.className = 'msg-error';
                    msgDiv.textContent = data.error;
                }
            } catch (e) {
                msgDiv.style.display = 'block';
                msgDiv.className = 'msg-error';
                msgDiv.textContent = 'Error de conexión.';
            }
        });

        // Initial load
        cargarBandeja();
    </script>
</body>

</html>