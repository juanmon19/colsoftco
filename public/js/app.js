/**
 * app.js - Utilidades generales de la interfaz.
 */

// ===============================
// CERRAR SESIÓN
// ===============================
function cerrarSesion() {
    if (confirm("¿Desea cerrar la sesión?")) {
        window.location.href = "../../app/logout.php";
    }
}

// ===============================
// CONFIGURACIÓN GENERAL
// ===============================
document.addEventListener("DOMContentLoaded", () => {

    // ===============================
    // BOTÓN CERRAR SESIÓN
    // ===============================
    const header = document.querySelector(".header");

    if (header && !document.getElementById("btnLogout")) {

        const logoutBtn = document.createElement("button");

        logoutBtn.id = "btnLogout";
        logoutBtn.textContent = "Cerrar Sesión";

        logoutBtn.style.position = "absolute";
        logoutBtn.style.right = "20px";
        logoutBtn.style.top = "50%";
        logoutBtn.style.transform = "translateY(-50%)";
        logoutBtn.style.padding = "8px 15px";
        logoutBtn.style.backgroundColor = "#D4AF37";
        logoutBtn.style.color = "#0A1F44";
        logoutBtn.style.border = "none";
        logoutBtn.style.borderRadius = "5px";
        logoutBtn.style.cursor = "pointer";
        logoutBtn.style.fontWeight = "bold";
        logoutBtn.style.fontSize = "14px";

        logoutBtn.addEventListener("mouseover", () => {
            logoutBtn.style.backgroundColor = "white";
        });

        logoutBtn.addEventListener("mouseout", () => {
            logoutBtn.style.backgroundColor = "#D4AF37";
        });

        logoutBtn.addEventListener("click", cerrarSesion);

        header.appendChild(logoutBtn);
    }

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