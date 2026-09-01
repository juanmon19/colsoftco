<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Bodeguero - Max & Flex</title>
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="panelbodeguero.css">
    
    <script>
        /* Aplica el tema guardado ANTES de pintar la página, para evitar parpadeo */
        (function () {
            const temaGuardado = localStorage.getItem('colsoftco_tema');
            if (temaGuardado === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        })();
    </script>
</head>

<body>
<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="../../public/imagenes/logo.png" alt="COLSOFTCO">
            <div>
                <strong>COLSOFTCO</strong>
                <span>Sistema de Gestión</span>
            </div>
        </div>

        <button class="mobile-menu" id="btnMenuToggle" type="button"
                aria-expanded="false" aria-controls="sidebarLinks">
            <span>☰ Menú</span>
            <span class="toggle-icono">⌄</span>
        </button>

        <nav class="sidebar-links" id="sidebarLinks">
            <button onclick="window.location.href='../historial_movimientos/historial.php'">
                <span>☑</span> Historial de Movimientos
            </button>
            <button onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">
                <span>♙</span> Lista de Proveedores
            </button>
            <button onclick="window.location.href='../registromp/registromp.php'">
                <span>＋</span> Registrar Materia Prima
            </button>
            <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                <span>▥</span> Generar Informe
            </button>
            <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                <span>◇</span> Inventario de Materia Prima
            </button>
            <button onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">
                <span>□</span> Inventario de Productos
            </button>
            <button onclick="window.location.href='../receta_de_colchones/receta_colchones.php'">
                <span>⚙</span> Receta de Colchones
            </button>
            <button onclick="window.location.href='../mensajeria/mensajeria.php'">
                <span>📨</span> Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span>
            </button>
        </nav>

        <div class="help-box">
            <div class="help-icon">♧</div>
            <div>
                <strong>¿Necesitas ayuda?</strong>
                <p>Nuestro equipo está<br>para apoyarte.</p>
                <a href="mailto:contacto@colsoftco.com">Contáctanos</a>
            </div>
        </div>
    </aside>

    <div class="main">

        <!-- HEADER -->
        <header class="header">
            <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

            <div class="header-left">
                <div>
                    <h1 id="saludoHeader">BIENVENIDO, NICOLÁS SANTIAGO</h1>
                    <p>Bodeguero</p>
                </div>
            </div>

            <div class="header-actions">

                <!-- MENÚ DE PERFIL DESPLEGABLE -->
                <div class="perfil-menu" id="perfilMenu">
                    <button class="perfil-trigger" id="btnPerfilMenu" type="button" aria-haspopup="true" aria-expanded="false">
                        <img id="avatarHeader" class="avatar-header" src="../../public/imagenes/bodeguero.jpeg" alt="Foto de perfil">
                        <span class="perfil-trigger-text">
                            <strong id="nombreHeaderCorto">Nicolás Santiago</strong>
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

            <!-- PERFIL -->
            <section class="profile-card">
                <img id="fotoPerfilGrande" src="../../public/imagenes/bodeguero.jpeg" alt="Foto de perfil">

                <div class="profile-info">
                    <div class="profile-heading">
                        <div>
                            <h2 id="nombreCompletoPerfil">Nicolás Santiago Polo Moreno</h2>
                            <p><strong>Rol:</strong> Bodeguero</p>
                        </div>
                        <span class="online"><i></i> Activo</span>
                    </div>

                    <div class="profile-details">
                        <span>▣ Gestión de bodega</span>
                        <span>◇ Control de inventario</span>
                        <span>⌖ Bogotá, Colombia</span>
                    </div>
                </div>
            </section>

            <!-- RESUMEN DEL BODEGUERO (datos reales) -->
            <section class="summary-grid">
                <article class="summary-card">
                    <div class="summary-icon yellow">☑</div>
                    <span>Tareas pendientes</span>
                    <strong id="statTareas">0</strong>
                    <small>Actividades por realizar</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon green">◇</div>
                    <span>Materia prima</span>
                    <strong id="statInventario">0</strong>
                    <small>Existencias registradas</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon purple">▣</div>
                    <span>Productos terminados</span>
                    <strong id="statProductos">0</strong>
                    <small>Unidades en bodega</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon blue">♙</div>
                    <span>Proveedores</span>
                    <strong id="statProveedores">0</strong>
                    <small>Registrados en el sistema</small>
                </article>
            </section>

            <!-- TAREAS + ACCIONES -->
            <section class="dashboard-grid">

                <article class="tasks card">
                    <div class="section-title section-title-row">
                        <div>
                            <span class="section-icon">☑</span>
                            <div>
                                <h3>Tareas Pendientes</h3>
                                <p>Actividades de la bodega que requieren seguimiento</p>
                            </div>
                        </div>
                        <button id="btnNuevaTarea" class="btn-nueva-tarea" type="button">+ Nueva tarea</button>
                    </div>

                    <div class="tasks-list" id="tasksListBody">
                        <p class="placeholder">Cargando tareas...</p>
                    </div>
                </article>

                <aside class="side-content">

                    <!-- ACCIONES -->
                    <article class="quick-actions card">
                        <div class="section-title compact">
                            <div>
                                <span class="section-icon">ϟ</span>
                                <div>
                                    <h3>Acciones rápidas</h3>
                                    <p>Funciones frecuentes del bodeguero</p>
                                </div>
                            </div>
                        </div>

                        <div class="quick-grid">
                            <button onclick="window.location.href='../registromp/registromp.php'">
                                <span class="quick-icon green">＋</span>
                                <strong>Registrar materia prima</strong>
                            </button>

                            <button onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">
                                <span class="quick-icon blue">◇</span>
                                <strong>Ver inventario</strong>
                            </button>

                            <button onclick="window.location.href='../historial_movimientos/historial.php'">
                                <span class="quick-icon yellow">☑</span>
                                <strong>Historial de movimientos</strong>
                            </button>

                            <button onclick="window.location.href='../generar_informe/generar_informe.php'">
                                <span class="quick-icon purple">▤</span>
                                <strong>Generar informe</strong>
                            </button>
                        </div>
                    </article>

                    <!-- INFORMACIÓN -->
                    <article class="contact-card card">
                        <div class="section-title compact">
                            <div>
                                <span class="section-icon">⌕</span>
                                <div>
                                    <h3>Información de contacto</h3>
                                    <p>Soporte y atención</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-list">
                            <p><span>⌖</span> Bogotá, Colombia</p>
                            <p><span>✉</span> contacto@colsoftco.com</p>
                            <p><span>⌕</span> +57 (1) 234-5678</p>
                            <p><span>◷</span> Lun - Vie: 8:00 am - 6:00 pm</p>
                        </div>
                    </article>

                </aside>
            </section>
        </main>

        <!-- FOOTER -->
        <footer>
            <div class="footer-main">
                <div>
                    <strong class="footer-brand">COLSOFTCO</strong>
                    <span class="footer-sub">SISTEMA DE GESTIÓN</span>
                    <p>
                        Sistema de gestión y administración de materias primas para Max&Flex.
                        Eficiencia en inventarios y movimientos empresariales.
                    </p>
                </div>

                <div class="footer-contact">
                    <strong>CONTACTO</strong>
                    <span>📍 Bogotá, Colombia</span>
                    <span>✉ contacto@colsoftco.com</span>
                    <span>📞 +57 (1) 234-5678</span>
                    <span>◷ Lun - Vie: 8:00 am - 6:00 pm</span>
                </div>
            </div>

            <div class="footer-bottom">
                <span>© 2026 <b>COLSOFTCO</b> · Max&Flex. Todos los derechos reservados.</span>
                <span>Desarrollado por <b>Equipo SENA</b></span>
            </div>
        </footer>
    </div>
</div>

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
            <img id="fotoPerfilModal" src="../../public/imagenes/bodeguero.jpeg" alt="Foto de perfil">
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

<div class="menu-overlay" id="menuOverlay"></div>

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

<script src="../../public/js/app.js"></script>

<script>
const sidebar = document.getElementById('sidebar');
const sidebarLinks = document.getElementById('sidebarLinks');
const menuToggle = document.getElementById('btnMenuToggle');
const mobileOpen = document.getElementById('mobileOpen');

function openSidebar() {
    sidebar.classList.add('mobile-visible');
    document.body.classList.add('menu-open');
}

function closeSidebar() {
    sidebar.classList.remove('mobile-visible');
    document.body.classList.remove('menu-open');
}

mobileOpen.addEventListener('click', openSidebar);

menuToggle.addEventListener('click', function () {
    sidebarLinks.classList.toggle('abierto');
    menuToggle.classList.toggle('abierto');
    menuToggle.setAttribute(
        'aria-expanded',
        sidebarLinks.classList.contains('abierto')
    );
});

document.addEventListener('click', function (event) {
    if (
        window.innerWidth <= 900 &&
        sidebar.classList.contains('mobile-visible') &&
        !sidebar.contains(event.target) &&
        event.target !== mobileOpen
    ) {
        closeSidebar();
    }
});

window.addEventListener('resize', function () {
    if (window.innerWidth > 900) {
        closeSidebar();
        sidebarLinks.classList.remove('abierto');
        menuToggle.classList.remove('abierto');
    }
});
</script>

<script src="../../public/js/tareas.js"></script>
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

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>
</html>