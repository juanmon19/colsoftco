

const USERS = [
    { username: "admin", password: "123", role: "admin", name: "Duvan Esteban Martínez Velandia" },
    { username: "bodeguero", password: "123", role: "bodeguero", name: "Nicolás Santiago Polo Moreno" },
    { username: "operario", password: "123", role: "operario", name: "Operario Prueba" }
];

const ROLES = {
    admin: {
        dashboard: "panel_admin/panel_admin.html"
    },
    bodeguero: {
        dashboard: "panel_bodeguero/panel_bodeguero.html"
    },
    operario: {
        dashboard: "panel_operario/panel_operario.html"
    }
};

class Auth {
    static login(username, password) {
        const user = USERS.find(u => u.username === username && u.password === password);
        if (user) {
            sessionStorage.setItem("authUser", JSON.stringify({
                username: user.username,
                role: user.role,
                name: user.name
            }));
            return user;
        }
        return null;
    }

    static logout() {
        sessionStorage.removeItem("authUser");
        window.location.href = this.getBasePath() + "login/login.html";
    }

    static getUser() {
        const userStr = sessionStorage.getItem("authUser");
        return userStr ? JSON.parse(userStr) : null;
    }

    static isAuthenticated() {
        return this.getUser() !== null;
    }

    
    static getBasePath() {
      
        const path = window.location.pathname;
        const parts = path.split('/').filter(p => p !== '');
        
  
        if (path.endsWith('.html')) {
           
            return "../";
        }
        
        return "./";
    }

    static checkAuth() {
        const currentPath = window.location.pathname.toLowerCase();
        const isLoginPage = currentPath.includes("login.html");
        const isPublicPage = isLoginPage || currentPath.includes("cambio_contrasena.html") || currentPath.includes("cambiocontrase%c3%b1a");
        const basePath = this.getBasePath();

        // Si está en la página de login y está autenticado, redirigir a su panel
        if (isLoginPage) {
            if (this.isAuthenticated()) {
                const user = this.getUser();
                window.location.href = basePath + ROLES[user.role].dashboard;
            }
            return;
        }

        // Si no está autenticado y no está en una página pública, redirigir a login
        if (!this.isAuthenticated()) {
            if (!isPublicPage) {
                window.location.href = basePath + "login/login.html";
            }
            return;
        }

        const user = this.getUser();
        
        // Rutas que son exclusivas de administrador
        const adminRoutes = ["panel_admin", "registro", "cambiocontraseña"];
        
        // Rutas compartidas o de bodeguero
        const bodegueroRoutes = ["panel_bodeguero", "inventario_materiaprima", "inventario_productosterminados", "registro de materias primas", "registro proveedores", "generar_informe", "control de stock", "lista_verificacion"];
        
        // Rutas compartidas o de operario
        const operarioRoutes = ["panel_operario", "inventario_materiaprima", "inventario_productosterminados", "registro de materias primas", , "generar_informe", "control de stock", "lista_verificacion"];

        if (user.role === 'bodeguero') {
            const isAllowed = bodegueroRoutes.some(route => currentPath.includes(route.toLowerCase().replace(/ /g, '%20'))) || 
                              bodegueroRoutes.some(route => currentPath.includes(route.toLowerCase().replace(/ /g, '-'))) ||
                              bodegueroRoutes.some(route => currentPath.includes(route.toLowerCase()));
            if (!isAllowed) {
                alert("Acceso denegado. Se requiere otro rol.");
                window.location.href = basePath + ROLES[user.role].dashboard;
            }
        } else if (user.role === 'operario') {
            const isAllowed = operarioRoutes.some(route => currentPath.includes(route.toLowerCase().replace(/ /g, '%20'))) ||
                              operarioRoutes.some(route => currentPath.includes(route.toLowerCase()));
            if (!isAllowed) {
                alert("Acceso denegado. Se requiere otro rol.");
                window.location.href = basePath + ROLES[user.role].dashboard;
            }
        }
        // Admin tiene acceso a todo
    }
}

// Ejecutar protección de ruta inmediatamente
Auth.checkAuth();

// Una vez cargado el DOM, actualizar datos de usuario en la UI
document.addEventListener("DOMContentLoaded", () => {
    const user = Auth.getUser();
    if (user) {
        document.querySelectorAll(".user-name-display, .user-name").forEach(el => el.textContent = user.name);
        document.querySelectorAll(".user-role-display, .user-role").forEach(el => el.textContent = user.role.toUpperCase());
        
        // Específico para los paneles
        document.querySelectorAll(".info-usuario h2, .user-meta h2").forEach(el => {
            el.innerHTML = `<strong>Usuario:</strong> ${user.name}`;
        });
        document.querySelectorAll(".info-usuario p, .user-meta p.role").forEach(el => {
            el.innerHTML = `<strong>Rol:</strong> ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}`;
        });
        
        // Actualizar la imagen del usuario según el rol en los paneles
        document.querySelectorAll(".bienvenida img, .user-photo img").forEach(img => {
            if (user.role === 'admin') {
                img.src = "../imagenes/usuario.jpg";
            } else if (user.role === 'bodeguero') {
                img.src = "../imagenes/bodeguero.jpeg";
            } else if (user.role === 'operario') {
                img.src = "../imagenes/operario.jpg";
            }
        });
        
        // Específico para control de stock y otros
        document.querySelectorAll(".user-badge span").forEach(el => el.textContent = user.name);
        
        // Actualizar avatares con la primera letra del nombre
        document.querySelectorAll(".user-avatar, .avatar-sm").forEach(el => {
            el.textContent = user.name.charAt(0).toUpperCase();
        });
    }
});
