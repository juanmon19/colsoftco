// ✅ AGREGA ESTO — bloquea el bfcache del navegador
window.addEventListener('pageshow', function(e) {
    if (e.persisted) { // la página viene del caché del botón atrás
        const esPanel = window.location.pathname.includes('panel_');
        if (esPanel && localStorage.getItem('logueado') !== 'true') {
            window.location.replace('../login/login.php');
        }
    }
});

/**
 * app.js - Utilidades generales, navegación e UI unificada.
 */

// Navegación
function navegar(ruta) {
    if (typeof Auth !== 'undefined') {
        const basePath = Auth.getBasePath();
        window.location.href = basePath + ruta;
    } else {
        window.location.href = "../" + ruta;
    }
}

// Mapa de funciones de navegación antiguas a las nuevas rutas
function mostrar(modulo) {

    const rutas = {

        // =========================
        // PROVEEDORES
        // =========================
        'Proveedores': '../lista_proveedores/lista_proveedores.php',
        'Lista de Proveedores': '../lista_proveedores/lista_proveedores.php',
        'Registrar Proveedor': 'registro_proveedores/registroproveedores.php',

        // =========================
        // MATERIAS PRIMAS
        // =========================
        'Materia': '../inventario_materia_prima/inventario_materia_prima.php',
        'Inventario Materia Prima': '../inventario_materia_prima/inventario_materia_prima.php',
        'Registrar Materia Prima': '../registro_materiaprima/registro_materiaprima.php',

        // =========================
        // PRODUCTOS
        // =========================
        'Productos': '../inventario_productos_terminados/inventario_productos_terminados.php',
        'Inventario Productos': '../inventario_productos_terminados/inventario_productos_terminados.php',

        // =========================
        // STOCK
        // =========================
        'Stock': '../control_de_stock/control_stock.php',

        // =========================
        // INFORMES
        // =========================
        'Informe': '../generar_informe/generar_informe.php',

        // =========================
        // VERIFICACIÓN
        // =========================
        'Verificación': '../lista_verificacion/lista_verificacion.php',
        'Lista Verificación': '../lista_verificacion/lista_verificacion.php',
        'Lista de Verificacion': '../lista_verificacion/lista_verificacion.php',

        // =========================
        // PANELES
        // =========================
        'Panel Admin': '../panel_admin/panel_admin.html',
        'Panel Bodeguero': '../panel_bodeguero/panel_bodeguero.html',
        'Panel Operario': '../panel_operario/panel_operario.html'
    };

    if (rutas[modulo]) {
        window.location.href = rutas[modulo];
    } else {
        alert("Módulo " + modulo + " en construcción.");
    }
}

// ===============================
// FUNCION CERRAR SESIÓN
// ===============================
function cerrarSesion() {
    if (confirm("¿Desea cerrar la sesión?")) {
        window.location.href = "../../app/logout.php";
    }
} 


// Configurar elementos comunes en el DOM
document.addEventListener("DOMContentLoaded", () => {
    
    // Inyectar botón de retorno dinámico en páginas secundarias
    const isMainPanel = window.location.pathname.includes("panel_admin") || 
                        window.location.pathname.includes("panel_bodeguero") || 
                        window.location.pathname.includes("panel_operario") ||
                        window.location.pathname.includes("login") ||
                        window.location.pathname.includes("cambio_contrasena");

    // ===============================
    // LOGO REDIRECCIÓN
    // ===============================
/*     const logos = document.querySelectorAll(".logo img, .logo");

    logos.forEach(logo => {
        logo.style.cursor = "pointer";

        logo.addEventListener("click", (e) => {
            e.preventDefault();

            if (typeof Auth !== 'undefined' && Auth.isAuthenticated()) {
                const user = Auth.getUser();

                window.location.href =
                    Auth.getBasePath() + ROLES[user.role].dashboard;

            } else {
                window.location.href =
                    typeof Auth !== 'undefined'
                        ? Auth.getBasePath() + "login/login.php"
                        : "../login/login.php";
            }
        });
    }); */

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

        // Acción cerrar sesión
        logoutBtn.addEventListener("click", cerrarSesion);

        header.appendChild(logoutBtn);
    }

    // ===============================
    // TOAST NOTIFICACIONES
    // ===============================
    window.showToast = function(message, type = 'success') {

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
            type === 'error' ? '#d9534f' : '#0A1F44';

        toast.style.borderLeft =
            `4px solid ${type === 'error' ? '#c9302c' : '#D4AF37'}`;

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