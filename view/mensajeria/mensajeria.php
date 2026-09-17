<?php
require_once "../../app/verificar_sesion.php";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajería Interna - COLSOFTCO</title>
    <!-- Orden estándar: global -> layout -> módulo -->
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="mensajeria.css">
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
<div class="container">

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="bandeja" onclick="cambiarTab('bandeja')">📥 Bandeja de entrada</button>
            <button class="tab" data-tab="enviados" onclick="cambiarTab('enviados')">📤 Enviados</button>
            <button class="tab" data-tab="componer" onclick="cambiarTab('componer')">✏️ Componer mensaje</button>
            <button class="tab" data-tab="chat" onclick="cambiarTab('chat')">💬 Chat directo</button>
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

        <!-- Chat directo con hilos -->
        <div class="tab-content" id="tab-chat" style="display:none;">
            <div class="card">
                <h3>Chat directo</h3>
                <label for="chatUsuario">Conversar con</label>
                <select id="chatUsuario" onchange="abrirChat()"><option value="">-- Seleccionar --</option></select>
                <div id="hiloChat" style="margin:15px 0; display:flex; flex-direction:column; gap:8px;"></div>
                <div id="citaPreview" style="display:none; border-left:3px solid #D4AF37; padding:6px 10px; background:#f4f6fb;">
                    <small>Respondiendo a:</small>
                    <div id="citaTexto" style="font-style:italic;"></div>
                    <button type="button" onclick="cancelarCita()">✖ Cancelar</button>
                </div>
                <form id="formChat" style="display:flex; gap:8px; margin-top:10px;">
                    <input type="hidden" id="chatPadre" value="">
                    <input type="text" id="chatTexto" placeholder="Escribe y pulsa Enter..." style="flex:1;" required>
                    <button type="submit" class="btn-primary">Enviar</button>
                </form>
            </div>
        </div>

    </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
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
            if (tab === 'chat') cargarChatUsuarios();
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

        // ===== Chat directo con hilos =====
        let chatOtro = 0;
        async function cargarChatUsuarios() {
            const sel = document.getElementById('chatUsuario');
            try {
                const resp = await fetch('../../app/logica_mensajes.php?accion=listar_usuarios');
                const data = await resp.json();
                if (!data.ok) return;
                const actual = sel.value;
                sel.innerHTML = '<option value="">-- Seleccionar --</option>' +
                    data.usuarios.map(u => `<option value="${u.id_usuario}">${escHtml(u.nombre)} ${escHtml(u.apellido)} (${escHtml(u.rol)})</option>`).join('');
                if (actual) sel.value = actual;
            } catch (e) { console.error(e); }
        }
        async function abrirChat() {
            chatOtro = parseInt(document.getElementById('chatUsuario').value || '0', 10);
            cancelarCita();
            if (!chatOtro) { document.getElementById('hiloChat').innerHTML = ''; return; }
            const div = document.getElementById('hiloChat');
            try {
                const resp = await fetch(`../../app/logica_mensajes.php?accion=hilo&otro=${chatOtro}`);
                const txt = await resp.text();
                let data;
                try { data = JSON.parse(txt); }
                catch (je) { div.innerHTML = `<p class="placeholder">Error del servidor: ${escHtml(txt.substring(0,200))}</p>`; return; }
                if (!data.ok) {
                    div.innerHTML = `<p class="placeholder">${escHtml(data.error || 'No se pudo cargar.')}</p>`; return;
                }
                if (!data.mensajes.length) {
                    div.innerHTML = '<p class="placeholder">Sin mensajes. ¡Saluda primero!</p>'; return;
                }
                div.innerHTML = data.mensajes.map(m => `
                    <div style="border:1px solid #e2e6f0; border-radius:8px; padding:8px; background:${m.leido==0?'#fffbe6':'#fff'}">
                        ${m.id_padre ? `<blockquote style="margin:0 0 6px; padding-left:8px; border-left:3px solid #D4AF37; color:#555;">${escHtml(m.padre_contenido||'')}</blockquote>` : ''}
                        <div><strong>${escHtml(m.nombre)} ${escHtml(m.apellido)}</strong> <small>${formatFecha(m.fecha_envio)}</small></div>
                        <div>${escHtml(m.contenido)}</div>
                        <button type="button" data-id="${m.id_mensaje}" data-text="${escHtml((m.contenido||'').substring(0,80))}" onclick="responderA(this)">↩ Responder</button>
                    </div>`).join('');
                div.scrollTop = div.scrollHeight;
            } catch (e) { div.innerHTML = `<p class="placeholder">Error de conexión: ${escHtml(e.message)}</p>`; }
        }
        function responderA(btn) {
            document.getElementById('chatPadre').value = btn.dataset.id;
            document.getElementById('citaTexto').textContent = btn.dataset.text;
            document.getElementById('citaPreview').style.display = 'block';
            document.getElementById('chatTexto').focus();
        }
        function cancelarCita() {
            document.getElementById('chatPadre').value = '';
            document.getElementById('citaPreview').style.display = 'none';
        }
        document.getElementById('formChat').addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!chatOtro) { alert('Elige un usuario'); return; }
            const txt = document.getElementById('chatTexto').value.trim();
            if (!txt) return;
            const fd = new FormData();
            fd.append('accion', document.getElementById('chatPadre').value ? 'responder' : 'enviar');
            fd.append('otro', chatOtro);
            fd.append('id_destinatario', chatOtro);
            fd.append('contenido', txt);
            fd.append('id_padre', document.getElementById('chatPadre').value);
            fd.append('asunto', 'Chat');
            const resp = await fetch('../../app/logica_mensajes.php', { method: 'POST', body: fd });
            const data = await resp.json();
            if (data.ok) {
                document.getElementById('chatTexto').value = '';
                cancelarCita();
                abrirChat();
            } else alert(data.error || 'No se pudo enviar');
        });
    </script>
</body>

</html>