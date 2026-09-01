(function() {
    'use strict';
    async function actualizarBadgeMensajes() {
        try {
            const resp = await fetch('../../app/logica_mensajes.php?accion=no_leidos');
            const data = await resp.json();
            const badge = document.getElementById('badgeMensajesNoLeidos');
            if (!badge) return;

            if (data.ok && data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline-block';
                badge.style.background = '#ef4444';
                badge.style.color = 'white';
                badge.style.borderRadius = '10px';
                badge.style.padding = '2px 6px';
                badge.style.fontSize = '10px';
                badge.style.marginLeft = '5px';
            } else {
                badge.style.display = 'none';
            }
        } catch(e) { }
    }
    
    // Carga inicial y auto-refresh cada 30s
    actualizarBadgeMensajes();
    setInterval(actualizarBadgeMensajes, 30000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) actualizarBadgeMensajes();
    });
})();

