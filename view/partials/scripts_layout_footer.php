<script src="../../public/js/app.js"></script>

<script>
    // ================= SIDEBAR MÓVIL =================
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

    if (menuButton) {
        menuButton.addEventListener('click', () => {
            nav.classList.toggle('open');
            menuButton.setAttribute('aria-expanded', nav.classList.contains('open'));
        });
    }

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
            nav.classList.remove('open');
            document.body.classList.remove('menu-open');
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
            const resp = await fetch('../../app/perfil_usuario.php?accion=obtener');
            const data = await resp.json();
            if (!data.ok) return;

            const u = data.usuario;
            const nombreCompleto = `${u.nombre} ${u.apellido}`;

            if (nombreHeaderCorto) nombreHeaderCorto.textContent = u.nombre;
            if (nombreCompletoPerfil) nombreCompletoPerfil.textContent = nombreCompleto;
            if (emailPerfil) emailPerfil.textContent = u.email || '';
            if (telefonoPerfil) telefonoPerfil.textContent = u.telefono || '';

            if (u.foto) {
                const url = `../../public/imagenes/perfiles/${u.foto}`;
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
            const resp = await fetch('../../app/perfil_usuario.php', { method: 'POST', body: formData });
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
