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
