<?php
require_once __DIR__ . '/../../app/permisos.php';
/* Token CSRF para los fetch (p. ej. foto de perfil). Solo si hay sesión. */
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['documento'])) {
    echo '<meta name="csrf-token" content="' . htmlspecialchars(generarTokenCSRF()) . '">' . "\n";
}
$publicoUI = htmlspecialchars(colsoftco_base_path() . '/public', ENT_QUOTES, 'UTF-8');
?>
<link rel="stylesheet" href="<?= $publicoUI ?>/css/notificaciones.css">
<?php /* InfinityFree bloquea las URLs que contienen "chat" (error 403);
         por eso el asistente de soporte usa soporte.css y soporte.js. */ ?>
<link rel="stylesheet" href="<?= $publicoUI ?>/css/soporte.css">
<script async src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.22/dist/sweetalert2.all.min.js"
    integrity="sha384-OLnrIp3B0fRuH8K0s/vh746qSGKTchpCz55yPK3+G+NjYoSA03u2PMieJ0yTQ+nE" crossorigin="anonymous"></script>
<script defer src="<?= $publicoUI ?>/js/notificaciones.js"></script>
<script defer src="<?= $publicoUI ?>/js/soporte.js"></script>
<script>
    /* Aplica el tema guardado ANTES de pintar la página, para evitar parpadeo.
       Este bloque va en el <head>, los demás van antes de </body>. */
    (function () {
        try {
            if (localStorage.getItem('colsoftco_tema') === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        } catch { /* Almacenamiento deshabilitado en este navegador. */ }
    })();
</script>
