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
    <title>Gestión de Usuarios - COLSOFTCO</title>
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
    <style>
        /* El contenido crece y empuja el footer al fondo */
        main.content {
            flex: 1;
        }

        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 24px 28px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5eaf0;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(13, 35, 67, 0.07);
        }

        .card h2 {
            font-size: 18px;
            color: #0A1F44;
            margin-bottom: 16px;
        }

        /* Conectados */
        .conectados-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 8px;
        }

        .conectado-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #e5f7ec;
            border: 1px solid #b6e6c6;
            border-radius: 20px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            color: #1e7e42;
        }

        .conectado-badge .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .4;
            }
        }

        /* Tabla responsive */
        .tabla-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }

        th {
            background: #eef1f8;
            color: #0A1F44;
            text-align: left;
            padding: 12px 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        td {
            padding: 12px 14px;
            border-bottom: 1px solid #eef1f8;
            font-size: 13px;
            vertical-align: middle;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .badge-rol {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .badge-rol.administrador {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-rol.bodeguero {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-rol.operario {
            background: #ede9fe;
            color: #6d28d9;
        }

        .badge-estado {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-estado.activo {
            background: #e5f7ec;
            color: #1e7e42;
        }

        .badge-estado.inactivo {
            background: #fdecea;
            color: #c0392b;
        }

        .badge-online {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-online .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .badge-online.si .dot {
            background: #16a34a;
        }

        .badge-online.si {
            color: #16a34a;
        }

        .badge-online.no .dot {
            background: #9ca3af;
        }

        .badge-online.no {
            color: #9ca3af;
        }

        .btn-toggle {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-toggle.desactivar {
            background: #fdecea;
            color: #c0392b;
        }

        .btn-toggle.desactivar:hover {
            background: #f5c6cb;
        }

        .btn-toggle.activar {
            background: #e5f7ec;
            color: #1e7e42;
        }

        .btn-toggle.activar:hover {
            background: #b6e6c6;
        }

        .placeholder-msg {
            text-align: center;
            color: #9ca3af;
            padding: 20px;
            font-size: 14px;
        }

        /* ===== TEMA OSCURO navy + dorado ===== */
        html[data-tema="oscuro"] .card {
            background: var(--surface, #101a30);
            border-color: var(--border, rgba(148, 163, 184, .18));
            box-shadow: var(--shadow, 0 6px 20px rgba(0, 0, 0, .45));
        }

        html[data-tema="oscuro"] .card h2 {
            color: #fff;
        }

        html[data-tema="oscuro"] .conectado-badge {
            background: rgba(74, 222, 128, 0.12);
            border-color: rgba(74, 222, 128, 0.35);
            color: #4ade80;
        }

        html[data-tema="oscuro"] th {
            background: var(--surface-2, #16223c);
            color: var(--gold, #d4af37);
            border-bottom: 2px solid var(--gold, #d4af37);
        }

        html[data-tema="oscuro"] td {
            border-bottom-color: var(--border, rgba(148, 163, 184, .18));
            color: var(--text, #e8edf5);
        }

        html[data-tema="oscuro"] tr:hover td {
            background: var(--gold-soft, rgba(212, 175, 55, .12));
        }

        html[data-tema="oscuro"] .badge-rol.administrador {
            background: rgba(96, 165, 250, 0.16);
            color: #60a5fa;
        }

        html[data-tema="oscuro"] .badge-rol.bodeguero {
            background: rgba(212, 175, 55, 0.16);
            color: #e8bd3f;
        }

        html[data-tema="oscuro"] .badge-rol.operario {
            background: rgba(139, 124, 246, 0.16);
            color: #a78bfa;
        }

        html[data-tema="oscuro"] .badge-estado.activo {
            background: rgba(74, 222, 128, 0.14);
            color: #4ade80;
        }

        html[data-tema="oscuro"] .badge-estado.inactivo {
            background: rgba(248, 113, 113, 0.14);
            color: #f87171;
        }

        html[data-tema="oscuro"] .badge-online.si {
            color: #4ade80;
        }

        html[data-tema="oscuro"] .badge-online.no {
            color: var(--muted, #9aa7bd);
        }

        html[data-tema="oscuro"] .btn-toggle.desactivar {
            background: rgba(248, 113, 113, 0.14);
            color: #f87171;
        }

        html[data-tema="oscuro"] .btn-toggle.activar {
            background: rgba(74, 222, 128, 0.14);
            color: #4ade80;
        }

        html[data-tema="oscuro"] .placeholder-msg {
            color: var(--muted, #9aa7bd);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1100px) {
            .tabla-responsive {
                overflow-x: auto;
            }

            table {
                min-width: 760px;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 16px 12px;
            }

            .card {
                padding: 16px 12px;
            }

            .card h2 {
                font-size: 16px;
            }

            th, td {
                padding: 10px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php include __DIR__ . '/../partials/topbar.php'; ?>

            <main class="content">
                <div class="container">

                    <!-- Usuarios conectados -->
                    <div class="card">
                        <h2>🟢 Usuarios Conectados</h2>
                        <div class="conectados-grid" id="conectadosGrid">
                            <p class="placeholder-msg">Cargando...</p>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="card">
                        <h2>👥 Todos los Usuarios</h2>
                        <div class="tabla-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Documento</th>
                                        <th>Correo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>En línea</th>
                                        <th>Última actividad</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tablaUsuarios">
                                    <tr>
                                        <td colspan="8" class="placeholder-msg">Cargando usuarios...</td>
                                    </tr>
                                </tbody>
                            </table>
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
        function formatearFechaActividad(fechaStr) {
            if (!fechaStr) return 'Nunca';
            const d = new Date(fechaStr);
            const hoy = new Date();
            const diff = Math.floor((hoy - d) / 60000); // minutos
            if (diff < 1) return 'Ahora mismo';
            if (diff < 60) return `Hace ${diff} min`;
            if (diff < 1440) return `Hace ${Math.floor(diff/60)} h`;
            return d.toLocaleDateString('es-CO', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        async function cargarConectados() {
            try {
                const resp = await fetch('../../app/logica_usuarios.php?accion=conectados');
                const data = await resp.json();
                const grid = document.getElementById('conectadosGrid');

                if (!data.ok || !data.conectados.length) {
                    grid.innerHTML = '<p class="placeholder-msg">No hay usuarios conectados en este momento.</p>';
                    return;
                }

                grid.innerHTML = data.conectados.map(u => `
                <div class="conectado-badge">
                    <span class="dot"></span>
                    ${u.nombre} ${u.apellido} <small>(${u.rol})</small>
                </div>
            `).join('');
            } catch (e) {
                console.error(e);
            }
        }

        async function cargarUsuarios() {
            try {
                const resp = await fetch('../../app/logica_usuarios.php?accion=listar');
                const data = await resp.json();
                const tbody = document.getElementById('tablaUsuarios');

                if (!data.ok || !data.usuarios.length) {
                    tbody.innerHTML = '<tr><td colspan="8" class="placeholder-msg">No hay usuarios registrados.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.usuarios.map(u => {
                    const esAdmin = u.rol === 'administrador';
                    const btnClase = u.activo == 1 ? 'desactivar' : 'activar';
                    const btnTexto = u.activo == 1 ? 'Desactivar' : 'Activar';
                    const btnDisabled = esAdmin ? 'disabled style="opacity:0.4;cursor:not-allowed;"' : '';

                    return `<tr>
                    <td><strong>${u.nombre} ${u.apellido}</strong></td>
                    <td>${u.documento}</td>
                    <td>${u.email}</td>
                    <td><span class="badge-rol ${u.rol}">${u.rol}</span></td>
                    <td><span class="badge-estado ${u.activo == 1 ? 'activo' : 'inactivo'}">${u.activo == 1 ? 'Activo' : 'Inactivo'}</span></td>
                    <td>
                        <span class="badge-online ${u.online ? 'si' : 'no'}">
                            <span class="dot"></span> ${u.online ? 'En línea' : 'Desconectado'}
                        </span>
                    </td>
                    <td>${formatearFechaActividad(u.ultima_actividad)}</td>
                    <td>
                        ${esAdmin ? '<small style="color:#9ca3af;">—</small>' : 
                        `<button class="btn-toggle ${btnClase}" onclick="toggleEstado(${u.id_usuario}, ${u.activo == 1 ? 0 : 1})" ${btnDisabled}>${btnTexto}</button>`}
                    </td>
                </tr>`;
                }).join('');
            } catch (e) {
                console.error(e);
            }
        }

        async function toggleEstado(idUsuario, nuevoEstado) {
            const accion = nuevoEstado === 0 ? 'desactivar' : 'activar';
            if (!confirm(`¿Desea ${accion} este usuario?`)) return;

            try {
                const resp = await fetch('../../app/logica_usuarios.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `accion=cambiar_estado&id_usuario=${idUsuario}&activo=${nuevoEstado}`
                });
                const data = await resp.json();
                if (data.ok) {
                    cargarUsuarios();
                    cargarConectados();
                } else {
                    alert(data.error || 'Error al cambiar estado.');
                }
            } catch (e) {
                alert('Error de conexión.');
            }
        }

        // Carga inicial
        cargarConectados();
        cargarUsuarios();

        // Auto-refresh cada 15 segundos
        setInterval(() => {
            cargarConectados();
            cargarUsuarios();
        }, 15000);
    </script>

</body>

</html>
