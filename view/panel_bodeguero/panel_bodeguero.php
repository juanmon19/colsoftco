<?php

require_once "../../app/verificar_sesion.php";
require_once "../../config/conexion.php";

// Datos reales del panel (igual que panel_admin, pero con alcance de bodega).
try {
    $dbPanel = (new Conexion())->getConnection();
    $inventarioBodega = (float) $dbPanel->query("SELECT COALESCE(SUM(stock_actual), 0) FROM materias_primas")->fetchColumn();
    $productosBodega = (float) $dbPanel->query("SELECT COALESCE(SUM(stock_actual), 0) FROM productos_terminados")->fetchColumn();
    $proveedoresBodega = (int) $dbPanel->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
    try {
        $meBodega = (int) ($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
        $stmtBodega = $dbPanel->prepare("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente' AND id_usuario = :me");
        $stmtBodega->execute([':me' => $meBodega]);
        $tareasBodega = (int) $stmtBodega->fetchColumn();
    } catch (Throwable $e) {
        $tareasBodega = 0;
    }
} catch (Throwable $e) {
    $inventarioBodega = $productosBodega = 0;
    $proveedoresBodega = $tareasBodega = 0;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Bodeguero - Max & Flex</title>
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="panelbodeguero.css">
    <link rel="stylesheet" href="../panel_admin/paneladmin.css">
    <link rel="stylesheet" href="../../public/css/kanban.css">

    <?php require_once __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>

<body>
<?php if (!empty($_SESSION['aviso_rol'])): ?><script>document.addEventListener('DOMContentLoaded',()=>{alert(<?= json_encode($_SESSION['aviso_rol']) ?>);});</script><?php unset($_SESSION['aviso_rol']); endif; ?>
<div class="menu-overlay" id="menuOverlay"></div>
<div class="app">

    <!-- SIDEBAR COMPARTIDO (misma estructura que módulos y panel_admin) -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="../../public/imagenes/logo.png" alt="COLSOFTCO">
            <div class="brand-text">
                <strong>COLSOFTCO</strong>
                <span>Sistema de Gestión</span>
            </div>
        </div>

        <button class="mobile-menu" id="btnMenuToggle" type="button"
                aria-expanded="false" aria-controls="sidebarLinks">
            <span>☰ Menú</span>
            <span class="toggle-icono">⌄</span>
        </button>

        <nav class="nav" id="navMenu">
            <button class="nav-item active" onclick="window.location.href='panel_bodeguero.php'">🏠 Panel Principal</button>
            <button class="nav-item" onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
            <button class="nav-item" onclick="window.location.href='../control_de_stock/control_de_stock.php'">Control de Stock</button>
            <button class="nav-item" onclick="window.location.href='../registromp/registromp.php'">Registrar Materia Prima</button>
            <button class="nav-item" onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
            <button class="nav-item" onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
            <button class="nav-item" onclick="window.location.href='../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
            <button class="nav-item" onclick="window.location.href='../historial_fabricacion/historial_fabricacion.php'">Historial de Fabricación</button>
            <button class="nav-item" onclick="window.location.href='../mensajeria/mensajeria.php'">📨 Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span></button>
        </nav>
    </aside>

    <div class="main">

        <!-- HEADER COMPARTIDO (misma estructura que módulos y panel_admin) -->
        <header class="topbar">
            <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

            <div class="welcome">
                <h1 id="saludoHeader">BIENVENIDO</h1>
                <p>Bodeguero</p>
            </div>

            <div class="header-actions">

                <!-- MENÚ DE PERFIL DESPLEGABLE -->
                <div class="perfil-menu" id="perfilMenu">
                    <button class="perfil-trigger" id="btnPerfilMenu" type="button" aria-haspopup="true" aria-expanded="false">
                        <img id="avatarHeader" class="avatar-header" src="../../public/imagenes/usuario.png" alt="Foto de perfil">
                        <span class="perfil-trigger-text">
                            <strong id="nombreHeaderCorto">Usuario</strong>
                            <small>Bodeguero</small>
                        </span>
                        <span class="perfil-caret">⌄</span>
                    </button>

                    <div class="perfil-dropdown" id="perfilDropdown">
                        <button type="button" id="btnCambiarFoto">🖼 Cambiar foto de perfil</button>
                        <button type="button" id="btnEditarDatos">✎ Editar mis datos</button>
                        <button type="button" id="btnTemaOscuro">
                            <span id="temaIconoTexto">🌙 Activar tema oscuro</span>
                        </button>
                        <div class="perfil-dropdown-divider"></div>
                        <button type="button" class="perfil-dropdown-logout" onclick="cerrarSesion()">⏻ Cerrar sesión</button>
                    </div>
                </div>

                <input type="file" id="inputFotoPerfil" accept="image/png, image/jpeg, image/webp" hidden>

            </div>
        </header>

        <main class="content">

                <!-- PERFIL + ESTADÍSTICAS (mismo diseño que panel_admin) -->
                <section class="hero-grid">

                    <article class="profile">
                        <img id="fotoPerfilGrande" src="../../public/imagenes/usuario.png" alt="Foto de perfil">

                        <div class="profile-data">
                            <h2 id="nombreCompletoPerfil">Cargando…</h2>
                            <p class="role"><b>Rol:</b> Bodeguero</p>
                            <p><span class="small-icon">✉</span> <span id="emailPerfil">—</span></p>
                            <p><span class="small-icon">☏</span> <span id="telefonoPerfil">—</span></p>
                            <p><span class="small-icon">⌕</span> Bogotá, Colombia</p>
                        </div>
                    </article>

                    <div class="stats">

                        <article class="stat">
                            <div class="stat-content">
                                <span>Tareas pendientes</span>
                                <strong id="statTareas"><?= (int) $tareasBodega ?></strong>
                                <i class="stat-icon yellow">▣</i>
                            </div>
                            <a href="#tareas">Ver detalles <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Inventario total</span>
                                <strong id="statInventario"><?= number_format($inventarioBodega, 0, ',', '.') ?></strong>
                                <i class="stat-icon green">◇</i>
                            </div>
                            <a href="../inventario_materia_prima/inventario_materia_prima.php">Ver inventario
                                <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Proveedores</span>
                                <strong id="statProveedores"><?= (int) $proveedoresBodega ?></strong>
                                <i class="stat-icon purple">♙</i>
                            </div>
                            <a href="../lista_proveedores/lista_proveedores.php">Ver proveedores <b>›</b></a>
                        </article>

                        <article class="stat">
                            <div class="stat-content">
                                <span>Productos</span>
                                <strong id="statProductos"><?= number_format($productosBodega, 0, ',', '.') ?></strong>
                                <i class="stat-icon blue">◇</i>
                            </div>
                            <a href="../inventario_productos_terminados/inventario_productos_terminados.php">Ver
                                productos <b>›</b></a>
                        </article>

                    </div>
                </section>

                <!-- TAREAS + DERECHA (mismo diseño que panel_admin) -->
                <section class="dashboard-grid">

                    <article class="tasks card" id="tareas">
                        <div class="title-row">
                            <h3><span>▣</span> Tareas Pendientes</h3>
                            <div class="title-actions">
                                <div class="view-tabs">
                                    <button type="button" data-vista="kanban" class="active">Kanban</button>
                                    <button type="button" data-vista="calendario">Calendario</button>
                                </div>
                                <button id="btnNuevaTarea" class="btn-nueva-tarea" type="button">+ Nueva tarea</button>
                            </div>
                        </div>

                        <div class="kanban" id="taskTableBody">
                            <p class="placeholder">Cargando tareas...</p>
                        </div>

                        <div class="calendario-tareas" id="calTareas" hidden></div>

                        <div class="tasks-summary" id="tasksSummary">
                            <span class="sum-loading">Calculando resumen…</span>
                        </div>
                    </article>

                    <aside class="right">

                        <article class="quick card">
                            <div class="title-row">
                                <h3><span>ϟ</span> Acciones rápidas</h3>
                            </div>

                            <div class="quick-grid">
                                <button onclick="window.location.href='../registromp/registromp.php'">
                                    <span class="quick-icon green-icon">＋</span>
                                    <b>Registrar materia prima</b>
                                </button>

                                <button onclick="window.location.href='../control_de_stock/control_de_stock.php'">
                                    <span class="quick-icon yellow-icon">📦</span>
                                    <b>Control de stock</b>
                                </button>

                                <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                                    <span class="quick-icon green-icon">↓</span>
                                    <b>Inventario de Materia Prima</b>
                                </button>

                                <button onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">
                                    <span class="quick-icon purple-icon">♙</span>
                                    <b>Proveedores</b>
                                </button>

                                <button onclick="window.location.href='../tareas/tareas.php'">
                                    <span class="quick-icon blue-icon">📅</span>
                                    <b>Calendario de tareas</b>
                                </button>
                            </div>
                        </article>

                        <article class="contact card">
                            <div class="title-row">
                                <h3><span>⌕</span> Información de contacto</h3>
                            </div>
                            <p>⌖ <span>Bogotá, Colombia</span></p>
                            <p>✉ <span>contacto@colsoftco.com</span></p>
                            <p>⌕ <span>+57 (1) 234-5678</span></p>
                            <p>◷ <span>Lun - Vie: 8:00 am - 6:00 pm</span></p>
                        </article>

                    </aside>
                </section>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>
        </div><!-- /.main -->
    </div><!-- /.app -->

<!-- ══ MODAL NUEVA TAREA ══ -->
<div class="modal-overlay" id="modalTareaOverlay">
    <div class="modal-box">
        <h3>Registrar nueva tarea</h3>
        <form id="formNuevaTarea">
            <label for="tareaTitulo">Título</label>
            <input type="text" id="tareaTitulo" list="sugerenciasTareas" placeholder="Ej. Recibir mercancía" autocomplete="off" required>
            <datalist id="sugerenciasTareas"></datalist>

            <label for="tareaPrioridad">Prioridad</label>
            <select id="tareaPrioridad">
                <option value="low">Baja</option>
                <option value="medium" selected>Media</option>
                <option value="high">Alta</option>
            </select>

            <label for="tareaVencimiento">Fecha de vencimiento</label>
            <input type="date" id="tareaVencimiento">

            <div id="wrapAsignado" style="display:none">
                <label for="tareaAsignado">Asignar a (solo admin)</label>
                <select id="tareaAsignado"></select>
            </div>

            <div class="modal-actions">
                <button type="button" id="btnCancelarTarea" class="btn-outline">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar tarea</button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL EDITAR PERFIL ══ -->
<div class="modal-overlay" id="modalPerfilOverlay">
    <div class="modal-box">
        <h3>Editar mis datos</h3>

        <div class="modal-perfil-foto">
            <img id="fotoPerfilModal" src="../../public/imagenes/usuario.png" alt="Foto de perfil">
            <button type="button" id="btnCambiarFotoModal">Cambiar foto</button>
        </div>

        <form id="formEditarPerfil">
            <label for="perfilNombre">Nombre</label>
            <input type="text" id="perfilNombre" required>

            <label for="perfilApellido">Apellido</label>
            <input type="text" id="perfilApellido" required>

            <label for="perfilEmail">Correo electrónico</label>
            <input type="email" id="perfilEmail" required>

            <label for="perfilTelefono">Teléfono</label>
            <input type="text" id="perfilTelefono" placeholder="Ej. 3001234567">

            <div class="modal-actions">
                <button type="button" id="btnCancelarPerfil" class="btn-outline">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<script src="../../public/js/app.js"></script>
<script src="../../public/js/menu_activo.js"></script>

<script>
    // Sidebar compartido (igual que módulos y panel_admin).
    const sidebar = document.getElementById('sidebar');
    const nav = document.getElementById('navMenu');
    const openButton = document.getElementById('mobileOpen');
    const menuButton = document.getElementById('btnMenuToggle');

    function openSidebar() {
        sidebar.classList.add('mobile-visible');
        document.body.classList.add('menu-open');
    }
    function closeSidebar() {
        sidebar.classList.remove('mobile-visible');
        document.body.classList.remove('menu-open');
    }
    if (openButton) openButton.addEventListener('click', openSidebar);
    if (menuButton) menuButton.addEventListener('click', () => {
        nav.classList.toggle('open');
        menuButton.setAttribute('aria-expanded', nav.classList.contains('open'));
    });
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 900 &&
            sidebar.classList.contains('mobile-visible') &&
            !sidebar.contains(e.target) &&
            e.target !== openButton) {
            closeSidebar();
        }
    });
    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            sidebar.classList.remove('mobile-visible');
            if (nav) nav.classList.remove('open');
            document.body.classList.remove('menu-open');
        }
    });

    // Refresca las 4 stats con datos reales sin recargar.
    async function refrescarStatsBodega() {
        try {
            const resp = await fetch('/colsoftco/app/dashboard_stats.php');
            const data = await resp.json();
            if (!data.ok) return;
            const fmt = (n) => Number(n).toLocaleString('es-CO');
            if (document.getElementById('statInventario')) document.getElementById('statInventario').textContent = fmt(data.inventario_total);
            if (document.getElementById('statProductos')) document.getElementById('statProductos').textContent = fmt(data.productos);
            if (document.getElementById('statProveedores')) document.getElementById('statProveedores').textContent = fmt(data.proveedores);
        } catch (e) { /* stats server-side ya visibles */ }
    }
    refrescarStatsBodega();
    setInterval(refrescarStatsBodega, 30000);
    // El resumen de tareas lo pinta tareas.js (misma carga de datos).
</script>

<script src="../../public/js/tareas.js"></script>
<script src="../../public/js/calendario_tareas.js"></script>
<script src="../../public/js/mensajes_badge.js"></script>

<script>
    // ================= MENÚ DE PERFIL: DATOS, FOTO Y TEMA =================

    const btnPerfilMenu = document.getElementById('btnPerfilMenu');
    const perfilDropdown = document.getElementById('perfilDropdown');
    const btnCambiarFoto = document.getElementById('btnCambiarFoto');
    const btnCambiarFotoModal = document.getElementById('btnCambiarFotoModal');
    const inputFotoPerfil = document.getElementById('inputFotoPerfil');
    const btnEditarDatos = document.getElementById('btnEditarDatos');
    const btnTemaOscuro = document.getElementById('btnTemaOscuro');
    const temaIconoTexto = document.getElementById('temaIconoTexto');

    const modalPerfilOverlay = document.getElementById('modalPerfilOverlay');
    const formEditarPerfil = document.getElementById('formEditarPerfil');
    const btnCancelarPerfil = document.getElementById('btnCancelarPerfil');
    const perfilNombre = document.getElementById('perfilNombre');
    const perfilApellido = document.getElementById('perfilApellido');
    const perfilEmail = document.getElementById('perfilEmail');
    const perfilTelefono = document.getElementById('perfilTelefono');

    const avatarHeader = document.getElementById('avatarHeader');
    const fotoPerfilGrande = document.getElementById('fotoPerfilGrande');
    const fotoPerfilModal = document.getElementById('fotoPerfilModal');
    const nombreHeaderCorto = document.getElementById('nombreHeaderCorto');
    const nombreCompletoPerfil = document.getElementById('nombreCompletoPerfil');
    const saludoHeader = document.getElementById('saludoHeader');
    const emailPerfil = document.getElementById('emailPerfil');
    const telefonoPerfil = document.getElementById('telefonoPerfil');

    function togglePerfilDropdown() {
        const abierto = perfilDropdown.classList.contains('open');
        perfilDropdown.classList.toggle('open', !abierto);
        btnPerfilMenu.setAttribute('aria-expanded', String(!abierto));
    }
    function cerrarPerfilDropdown() {
        perfilDropdown.classList.remove('open');
        btnPerfilMenu.setAttribute('aria-expanded', 'false');
    }

    btnPerfilMenu.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePerfilDropdown();
    });
    perfilDropdown.addEventListener('click', (e) => e.stopPropagation());

    // --- Cargar datos reales del usuario logueado ---
    async function cargarPerfil() {
        try {
            const resp = await fetch('../../app/perfil_usuario.php?accion=obtener');
            const data = await resp.json();
            if (!data.ok) return;

            const u = data.usuario;
            const nombreCorto = u.nombre;
            const nombreCompleto = `${u.nombre} ${u.apellido}`;

            nombreHeaderCorto.textContent = nombreCorto;
            nombreCompletoPerfil.textContent = nombreCompleto;
            saludoHeader.textContent = `BIENVENIDO, ${nombreCorto.toUpperCase()}`;
            if (emailPerfil) emailPerfil.textContent = u.email || '—';
            if (telefonoPerfil) telefonoPerfil.textContent = u.telefono || '—';

            if (u.foto) {
                const url = `../../public/imagenes/perfiles/${u.foto}`;
                avatarHeader.src = url;
                fotoPerfilGrande.src = url;
                fotoPerfilModal.src = url;
            }

            perfilNombre.value = u.nombre || '';
            perfilApellido.value = u.apellido || '';
            perfilEmail.value = u.email || '';
            perfilTelefono.value = u.telefono || '';
        } catch (e) {
            console.error('No se pudo cargar el perfil', e);
        }
    }

    // --- Cambiar foto ---
    function abrirSelectorFoto() {
        cerrarPerfilDropdown();
        inputFotoPerfil.click();
    }
    btnCambiarFoto.addEventListener('click', abrirSelectorFoto);
    btnCambiarFotoModal.addEventListener('click', abrirSelectorFoto);

    inputFotoPerfil.addEventListener('change', async () => {
        const archivo = inputFotoPerfil.files[0];
        if (!archivo) return;

        const formData = new FormData();
        formData.append('accion', 'foto');
        formData.append('foto', archivo);

        try {
            const resp = await fetch('../../app/perfil_usuario.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.ok) {
                avatarHeader.src = data.url;
                fotoPerfilGrande.src = data.url;
                fotoPerfilModal.src = data.url;
            } else {
                alert(data.error || 'No se pudo actualizar la foto.');
            }
        } catch (e) {
            alert('Error de conexión al subir la foto.');
            console.error(e);
        } finally {
            inputFotoPerfil.value = '';
        }
    });

    // --- Editar datos ---
    btnEditarDatos.addEventListener('click', () => {
        cerrarPerfilDropdown();
        modalPerfilOverlay.classList.add('show');
    });
    btnCancelarPerfil.addEventListener('click', () => modalPerfilOverlay.classList.remove('show'));
    modalPerfilOverlay.addEventListener('click', (e) => {
        if (e.target === modalPerfilOverlay) modalPerfilOverlay.classList.remove('show');
    });

    formEditarPerfil.addEventListener('submit', async (e) => {
        e.preventDefault();
        try {
            const resp = await fetch('../../app/perfil_usuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=actualizar&nombre=${encodeURIComponent(perfilNombre.value.trim())}` +
                      `&apellido=${encodeURIComponent(perfilApellido.value.trim())}` +
                      `&email=${encodeURIComponent(perfilEmail.value.trim())}` +
                      `&telefono=${encodeURIComponent(perfilTelefono.value.trim())}`
            });
            const data = await resp.json();
            if (data.ok) {
                modalPerfilOverlay.classList.remove('show');
                cargarPerfil();
            } else {
                alert(data.error || 'No se pudieron guardar los cambios.');
            }
        } catch (e) {
            alert('Error de conexión al guardar los cambios.');
            console.error(e);
        }
    });

    // --- Tema oscuro (persistente en todo el sistema vía localStorage) ---
    function aplicarTextoTema() {
        const esOscuro = document.documentElement.getAttribute('data-tema') === 'oscuro';
        temaIconoTexto.textContent = esOscuro ? '☀ Activar tema claro' : '🌙 Activar tema oscuro';
    }

    btnTemaOscuro.addEventListener('click', () => {
        const esOscuro = document.documentElement.getAttribute('data-tema') === 'oscuro';
        if (esOscuro) {
            document.documentElement.removeAttribute('data-tema');
            localStorage.setItem('colsoftco_tema', 'claro');
        } else {
            document.documentElement.setAttribute('data-tema', 'oscuro');
            localStorage.setItem('colsoftco_tema', 'oscuro');
        }
        aplicarTextoTema();
    });

    aplicarTextoTema();
    cargarPerfil();

    document.addEventListener('click', () => cerrarPerfilDropdown());
</script>

<?php require_once __DIR__ . '/../partials/botpress.php'; ?>
</body>
</html>