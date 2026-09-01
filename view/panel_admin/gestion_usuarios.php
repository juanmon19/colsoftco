<?php
require_once "../../app/verificar_sesion.php";

if (($_SESSION['rol'] ?? '') !== 'administrador') {
    header('Location: ../login/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - COLSOFTCO</title>
    <link rel="stylesheet" href="../../public/css/global.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
        }

        header {
            background: linear-gradient(105deg, #061d3c, #062047);
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            color: white;
            border-bottom: 3px solid #D4AF37;
        }

        header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        header .logo img {
            width: 45px;
        }

        header h1 {
            font-size: 16px;
            letter-spacing: 1px;
        }

        .btn-volver {
            background: #D4AF37;
            color: #0A1F44;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }

        .btn-volver:hover {
            background: #fff;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5eaf0;
            padding: 24px;
            margin-bottom: 24px;
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

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #eef1f8;
            color: #0A1F44;
            text-align: left;
            padding: 12px 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        footer {
            background: #0D1B3E;
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            padding: 12px;
            font-size: 11px;
            margin-top: 40px;
        }

        footer strong {
            color: #D4AF37;
        }
    </style>
</head>

<body>

    <header>
        <a class="logo" href="panel_admin.php">
            <img src="../../public/imagenes/logo.png" alt="Logo">
            <h1>Gestión de Usuarios</h1>
        </a>
        <button class="btn-volver" onclick="window.location.href='panel_admin.php'">← Volver al Panel</button>
    </header>

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
            <div style="overflow-x:auto;">
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

    <footer>
        © 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.
    </footer>

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