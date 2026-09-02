<?php
/**
 * Partial: Topbar superior + modal "Editar mis datos" (COLSOFTCO)
 *
 * Cómo incluirlo, dentro de <div class="main">, antes de <main>:
 *   <?php include __DIR__ . '/../partials/topbar.php'; ?>
 *
 * Opcional: define $rolActual ANTES del include para mostrar un rol
 * distinto a "Administrador", ej:
 *   <?php $rolActual = 'Bodeguero'; include __DIR__ . '/../partials/topbar.php'; ?>
 */
$rolActual = $rolActual ?? 'Administrador';
?>
<header class="topbar">
    <button class="mobile-open" id="mobileOpen" type="button" aria-label="Abrir menú">☰</button>

    <div class="welcome">
        <h1>BIENVENIDO</h1>
        <p><?= htmlspecialchars($rolActual) ?></p>
    </div>

    <div class="header-actions">
        <!-- MENÚ DE PERFIL DESPLEGABLE ÚNICO -->
        <div class="perfil-menu" id="perfilMenu">
            <button class="perfil-trigger" id="btnPerfilMenu" type="button" aria-haspopup="true" aria-expanded="false">
                <img id="avatarHeader" class="avatar-header" src="../../public/imagenes/usuario.png" alt="Foto de perfil">
                <span class="perfil-trigger-text">
                    <strong id="nombreHeaderCorto">Usuario</strong>
                    <small><?= htmlspecialchars($rolActual) ?></small>
                </span>
                <span class="perfil-caret">⌄</span>
            </button>

            <div class="perfil-dropdown" id="perfilDropdown">
                <button type="button" onclick="window.location.href='../registro/registro.php'">
                    ➕ Registrar usuario
                </button>
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

<!-- MODAL EDITAR PERFIL (lo abre el botón "Editar mis datos" de arriba) -->
<div class="modal-overlay" id="modalPerfilOverlay">
    <div class="modal-tarea">
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
