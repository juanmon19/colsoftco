<?php
/* Prefijo de rutas (lo normal es que sidebar.php ya lo haya calculado;
   este fallback cubre cualquier otro orden de includes). Sirve también
   para cargar los .js a cualquier profundidad de vista. */
if (!isset($prefijo)) {
    $prefijo = '';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (($pos = strpos($script, '/view/')) !== false) {
        $segs = array_values(array_filter(explode('/', dirname(substr($script, $pos + 6)))));
        if (count($segs) > 1) {
            $prefijo = str_repeat('../', count($segs) - 1);
        }
    }
}
$prefijoJs = json_encode($prefijo . '../../');
?>
<script src="<?= $prefijo ?>../../public/js/app.js"></script>
<script src="<?= $prefijo ?>../../public/js/menu_activo.js"></script>
<?php if (!empty($_SESSION['aviso_rol'])): ?>
<script>document.addEventListener('DOMContentLoaded', () => { if (window.showToast) showToast(<?= json_encode($_SESSION['aviso_rol']) ?>, 'error'); else alert(<?= json_encode($_SESSION['aviso_rol']) ?>); });</script>
<?php unset($_SESSION['aviso_rol']); endif; ?>

<script>
    // Base para los endpoints del perfil, válida a cualquier profundidad de vista
    const PREFIJO_APP = <?= $prefijoJs ?>;
    // ================= SIDEBAR MÓVIL =================
    // FIX: al abrir el drawer el logo queda visible (CSS z-index) y el
    // menú interno queda DESPLEGADO y se mantiene así entre vistas.
    const sidebar = document.getElementById('sidebar');
    const nav = document.getElementById('navMenu');
    const openButton = document.getElementById('mobileOpen');
    const menuButton = document.getElementById('btnMenuToggle');
    const menuOverlay = document.getElementById('menuOverlay');
    const NAV_KEY = 'colsoftco_nav_open';

    function pintarMenuButton() {
        if (!menuButton || !nav) return;
        menuButton.setAttribute('aria-expanded', nav.classList.contains('open') ? 'true' : 'false');
    }

    function openSidebar() {
        sidebar.classList.add('mobile-visible');
        document.body.classList.add('menu-open');
        // El menú siempre abre desplegado (como en tu 2da captura)
        if (nav) nav.classList.add('open');
        try { sessionStorage.setItem(NAV_KEY, '1'); } catch (e) {}
        pintarMenuButton();
        if (menuOverlay) menuOverlay.classList.add('show');
    }
    function closeSidebar() {
        sidebar.classList.remove('mobile-visible');
        document.body.classList.remove('menu-open');
        // OJO: NO colapsamos nav aquí para que "se quede desplegado"
        if (menuOverlay) menuOverlay.classList.remove('show');
    }
    function toggleSidebar() {
        if (sidebar.classList.contains('mobile-visible')) closeSidebar();
        else openSidebar();
    }

    // Estado inicial: si ya estaba desplegado, mantenerlo (móvil y desktop)
    try {
        if (sessionStorage.getItem(NAV_KEY) === '1' && nav) nav.classList.add('open');
    } catch (e) {}
    // En móvil, si el drawer abre por cualquier vía, el nav va abierto
    if (window.innerWidth <= 900 && sidebar.classList.contains('mobile-visible') && nav) {
        nav.classList.add('open');
    }
    pintarMenuButton();

    if (openButton) openButton.addEventListener('click', (e) => { e.stopPropagation(); toggleSidebar(); });

    if (menuButton) {
        menuButton.addEventListener('click', (e) => {
            e.stopPropagation();
            nav.classList.toggle('open');
            try { sessionStorage.setItem(NAV_KEY, nav.classList.contains('open') ? '1' : '0'); } catch (err) {}
            pintarMenuButton();
            // Si el drawer estaba cerrado y estamos en móvil, abrirlo al desplegar
            if (window.innerWidth <= 900 && nav.classList.contains('open')) {
                sidebar.classList.add('mobile-visible');
                document.body.classList.add('menu-open');
                if (menuOverlay) menuOverlay.classList.add('show');
            }
        });
    }

    if (menuOverlay) menuOverlay.addEventListener('click', closeSidebar);

    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 900 &&
            sidebar.classList.contains('mobile-visible') &&
            !sidebar.contains(e.target) &&
            e.target !== openButton &&
            !openButton.contains(e.target)) {
            closeSidebar();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('mobile-visible')) closeSidebar();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) {
            sidebar.classList.remove('mobile-visible');
            document.body.classList.remove('menu-open');
            if (menuOverlay) menuOverlay.classList.remove('show');
            // En desktop el nav siempre visible por CSS; limpiamos .open para estado neutro
            if (nav) nav.classList.remove('open');
            if (menuButton) menuButton.setAttribute('aria-expanded', 'false');
        }
    });

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
    // Estos dos solo existen en el panel principal (hero con foto grande).
    // En los demás módulos no están, por eso se comprueba antes de usarlos.
    const fotoPerfilGrande = document.getElementById('fotoPerfilGrande');
    const nombreCompletoPerfil = document.getElementById('nombreCompletoPerfil');
    const emailPerfil = document.getElementById('emailPerfil');
    const telefonoPerfil = document.getElementById('telefonoPerfil');
    const fotoPerfilModal = document.getElementById('fotoPerfilModal');
    const nombreHeaderCorto = document.getElementById('nombreHeaderCorto');

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
    document.addEventListener('click', () => cerrarPerfilDropdown());

    async function cargarPerfil() {
        try {
            const resp = await fetch(PREFIJO_APP + 'app/perfil_usuario.php?accion=obtener');
            const data = await resp.json();
            if (!data.ok) return;

            const u = data.usuario;
            const nombreCompleto = `${u.nombre} ${u.apellido}`;

            if (nombreHeaderCorto) nombreHeaderCorto.textContent = u.nombre;
            if (nombreCompletoPerfil) nombreCompletoPerfil.textContent = nombreCompleto;
            if (emailPerfil) emailPerfil.textContent = u.email || '';
            if (telefonoPerfil) telefonoPerfil.textContent = u.telefono || '';

            if (u.foto) {
                const url = `${PREFIJO_APP}public/imagenes/perfiles/${u.foto}`;
                if (avatarHeader) avatarHeader.src = url;
                if (fotoPerfilGrande) fotoPerfilGrande.src = url;
                if (fotoPerfilModal) fotoPerfilModal.src = url;
            }

            if (perfilNombre) perfilNombre.value = u.nombre || '';
            if (perfilApellido) perfilApellido.value = u.apellido || '';
            if (perfilEmail) perfilEmail.value = u.email || '';
            if (perfilTelefono) perfilTelefono.value = u.telefono || '';
        } catch (e) {
            console.error('No se pudo cargar el perfil', e);
        }
    }

    function abrirSelectorFoto() {
        cerrarPerfilDropdown();
        inputFotoPerfil.click();
    }
    btnCambiarFoto.addEventListener('click', abrirSelectorFoto);
    if (btnCambiarFotoModal) btnCambiarFotoModal.addEventListener('click', abrirSelectorFoto);

    inputFotoPerfil.addEventListener('change', async () => {
        const archivo = inputFotoPerfil.files[0];
        if (!archivo) return;

        const formData = new FormData();
        formData.append('accion', 'foto');
        formData.append('foto', archivo);

        try {
            const resp = await fetch(PREFIJO_APP + 'app/perfil_usuario.php', { method: 'POST', body: formData });
            const data = await resp.json();
            if (data.ok) {
                if (avatarHeader) avatarHeader.src = data.url;
                if (fotoPerfilGrande) fotoPerfilGrande.src = data.url;
                if (fotoPerfilModal) fotoPerfilModal.src = data.url;
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
            const resp = await fetch(PREFIJO_APP + 'app/perfil_usuario.php', {
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
</script>
