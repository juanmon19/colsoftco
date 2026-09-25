/**
 * app.js - Utilidades generales de la interfaz.
 */

// ===============================
// CERRAR SESIÓN (válido a cualquier profundidad: calcula la raíz
// de la app a partir de /view/ en la URL en vez de usar ../../ fijo)
// ===============================
function cerrarSesion() {
    if (confirm("¿Desea cerrar la sesión?")) {
        var m = window.location.pathname.match(/^(.*\/)view\//);
        window.location.href = (m ? m[1] : '../../') + "app/logout.php";
    }
}

// ===============================
// CONFIGURACIÓN GENERAL
// ===============================
document.addEventListener("DOMContentLoaded", () => {

    // ===============================

    // ===============================
    // TOAST NOTIFICACIONES
    // ===============================
    window.showToast = function(message, type = "success") {

        let toast = document.getElementById("app-toast");

        if (!toast) {

            toast = document.createElement("div");

            toast.id = "app-toast";

            toast.style.position = "fixed";
            toast.style.bottom = "30px";
            toast.style.right = "30px";
            toast.style.padding = "14px 24px";
            toast.style.borderRadius = "8px";
            toast.style.color = "white";
            toast.style.fontSize = "14px";
            toast.style.fontWeight = "500";
            toast.style.boxShadow = "0 6px 24px rgba(0,0,0,0.2)";
            toast.style.zIndex = "9999";
            toast.style.transition = "opacity 0.3s, transform 0.3s";
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";

            document.body.appendChild(toast);
        }

        toast.style.backgroundColor =
            type === "error" ? "#d9534f" : "#0A1F44";

        toast.style.borderLeft =
            `4px solid ${type === "error" ? "#c9302c" : "#D4AF37"}`;

        toast.textContent = message;

        setTimeout(() => {
            toast.style.opacity = "1";
            toast.style.transform = "translateY(0)";
        }, 10);

        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
        }, 3000);
    };

});

// ===============================
// ACCESIBILIDAD: widget de terceros (Botpress)
// El botón flotante del chat se inyecta sin alt ni roles válidos y
// Lighthouse lo penaliza. Como no controlamos su código, se corrige
// al vuelo: alt descriptivo + roles compatibles.
// ===============================
(function () {
    'use strict';

    function corregirBotpress() {
        document.querySelectorAll('img.bpFabImage').forEach(function (img) {
            if (!img.getAttribute('alt')) {
                img.setAttribute('alt', 'Abrir chat de ayuda');
            }
            // Solo roles compatibles con <img>: presentation/none o ninguno.
            var rol = img.getAttribute('role');
            if (rol && rol !== 'presentation' && rol !== 'none' && rol !== 'img') {
                img.removeAttribute('role');
            }
        });
    }

    corregirBotpress();

    try {
        var obs = new MutationObserver(function () { corregirBotpress(); });
        obs.observe(document.documentElement, { childList: true, subtree: true });
        setTimeout(function () { obs.disconnect(); }, 30000);
    } catch (e) { /* sin observer: se aplicó una vez al cargar */ }
})();