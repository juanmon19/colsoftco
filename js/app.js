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
    const basePath = typeof Auth !== 'undefined' ? Auth.getBasePath() : "../";
    
    const rutas = {
        'Proveedores': 'Registro Proveedores/index.html',
        'Registro Proveedor': 'Registro Proveedores/index.html',
        'Lista de Proveedores': 'Registro Proveedores/index.html',
        
        'Materia': 'inventario_MateriaPrima/inventario_materia_prima.html',
        'Inventario Materia Prima': 'inventario_MateriaPrima/inventario_materia_prima.html',
        'Registrar Materia Prima': 'Registro de materias primas/registros de materias prima.html',
        
        'Productos': 'inventario_ProductosTerminados/inventario_productosterminados.html',
        'Inventario Productos': 'inventario_ProductosTerminados/inventario_productosterminados.html',
        'Producto': 'inventario_ProductosTerminados/inventario_productosterminados.html',
        
        'Stock': 'control de stock/Control de stock.html',
        
        'Informe': 'generar_informe/generar_informe.html',
        
        'Verificación': 'lista_verificacion/lista_verificacion.html',
        'Lista Verificación': 'lista_verificacion/lista_verificacion.html',
        'Lista de Verificacion': 'lista_verificacion/lista_verificacion.html',
        'Lista de verificacion': 'lista_verificacion/lista_verificacion.html',
        'Lista de Verificaciòn': 'lista_verificacion/lista_verificacion.html',
        
        'Panel': Auth && Auth.getUser() ? ROLES[Auth.getUser().role].dashboard : 'login/login.html'
    };

    if (rutas[modulo]) {
        window.location.href = basePath + rutas[modulo];
    } else {
        alert("Módulo " + modulo + " en construcción.");
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

    if (!isMainPanel && typeof Auth !== 'undefined' && Auth.isAuthenticated()) {
        const header = document.querySelector("header") || document.querySelector(".header") || document.querySelector(".banner");
        if (header) {
            const user = Auth.getUser();
            const basePath = Auth.getBasePath();
            const returnUrl = basePath + ROLES[user.role].dashboard;
            
            const returnBtn = document.createElement("button");
            returnBtn.className = "btn-return";
            returnBtn.innerHTML = `&#8592; Volver al Panel`;
            returnBtn.style.position = "absolute";
            
            // Adjust position based on header type
            if (header.classList.contains("banner")) {
                returnBtn.style.left = "20px";
                header.style.position = "relative"; // Ensure relative positioning for absolute children
            } else {
                returnBtn.style.left = "100px"; // Next to logo in standard headers
            }
            
            returnBtn.style.top = "50%";
            returnBtn.style.transform = "translateY(-50%)";
            returnBtn.style.padding = "8px 15px";
            returnBtn.style.backgroundColor = "transparent";
            returnBtn.style.color = "white";
            returnBtn.style.border = "1px solid white";
            returnBtn.style.borderRadius = "5px";
            returnBtn.style.cursor = "pointer";
            returnBtn.style.fontWeight = "bold";
            returnBtn.style.fontSize = "13px";
            returnBtn.style.transition = "all 0.3s ease";
            
            returnBtn.addEventListener("mouseover", () => {
                returnBtn.style.backgroundColor = "white";
                returnBtn.style.color = "#0A1F44";
            });
            returnBtn.addEventListener("mouseout", () => {
                returnBtn.style.backgroundColor = "transparent";
                returnBtn.style.color = "white";
            });
            
            returnBtn.addEventListener("click", () => {
                window.location.href = returnUrl;
            });
            
            header.appendChild(returnBtn);
        }
    }

    // Configurar logo para que redirija al panel principal del usuario
    const logos = document.querySelectorAll(".logo img, .logo");
    logos.forEach(logo => {
        logo.style.cursor = "pointer";
        logo.addEventListener("click", (e) => {
            e.preventDefault();
            if (typeof Auth !== 'undefined' && Auth.isAuthenticated()) {
                const user = Auth.getUser();
                window.location.href = Auth.getBasePath() + ROLES[user.role].dashboard;
            } else {
                window.location.href = typeof Auth !== 'undefined' ? Auth.getBasePath() + "login/login.html" : "../login/login.html";
            }
        });
    });

    // Añadir botón de cerrar sesión si estamos logueados y hay un header
    if (typeof Auth !== 'undefined' && Auth.isAuthenticated()) {
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
            
            logoutBtn.addEventListener("mouseover", () => logoutBtn.style.backgroundColor = "white");
            logoutBtn.addEventListener("mouseout", () => logoutBtn.style.backgroundColor = "#D4AF37");
            
            logoutBtn.addEventListener("click", () => Auth.logout());
            
            header.appendChild(logoutBtn);
        }
    }
    
    // Reemplazar alerts nativos por notificaciones más amigables (Toast UI)
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
        
        toast.style.backgroundColor = type === 'error' ? '#d9534f' : '#0A1F44';
        toast.style.borderLeft = `4px solid ${type === 'error' ? '#c9302c' : '#D4AF37'}`;
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
