<?php /* Token CSRF para los fetch (p. ej. foto de perfil). Solo si hay sesión. */
if (function_exists('generarTokenCSRF') && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['documento'])) {
    echo '<meta name="csrf-token" content="' . htmlspecialchars(generarTokenCSRF()) . '">' . "\n";
}
?>
<!-- Preconexión temprana al chat (LCP: descubrimiento de solicitudes) -->
<link rel="preconnect" href="https://cdn.botpress.cloud" crossorigin>
<link rel="preconnect" href="https://files.bpcontent.cloud" crossorigin>
<link rel="dns-prefetch" href="https://cdn.botpress.cloud">
<link rel="dns-prefetch" href="https://files.bpcontent.cloud">
<script>
    /* Aplica el tema guardado ANTES de pintar la página, para evitar parpadeo.
       Este bloque va en el <head>, los demás van antes de </body>. */
    (function () {
        const temaGuardado = localStorage.getItem('colsoftco_tema');
        if (temaGuardado === 'oscuro') {
            document.documentElement.setAttribute('data-tema', 'oscuro');
        }
    })();
</script>
