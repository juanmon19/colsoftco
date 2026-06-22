/**
 * auth.js - Sistema de autenticación con localStorage
 */

const ROLES = {
    administrador: { dashboard: 'panel_admin/panel_admin.html' },
    bodeguero:     { dashboard: 'panel_bodeguero/panel_bodeguero.html' },
    operario:      { dashboard: 'panel_operario/panel_operario.html' }
};

const Auth = {

    // Guarda el usuario en localStorage al hacer login
    setUser: function(documento, rol) {
        localStorage.setItem('documento', documento);
        localStorage.setItem('rol', rol);
        localStorage.setItem('logueado', 'true');
    },

    // Obtiene los datos del usuario actual
    getUser: function() {
        const logueado = localStorage.getItem('logueado');
        if (!logueado) return null;
        return {
            documento: localStorage.getItem('documento'),
            role:    localStorage.getItem('rol')
        };
    },

    // Verifica si hay sesión activa
    isAuthenticated: function() {
        return localStorage.getItem('logueado') === 'true';
    },

    // Devuelve la ruta base hacia la carpeta view/
    getBasePath: function() {
        const path = window.location.pathname;
        if (path.includes('/view/')) {
            return '../';
        }
        return 'view/';
    }
};

// Al cargar un panel, marcar sesión activa
const esPanel = window.location.pathname.includes('panel_');

if (esPanel) {
    // Detectar qué rol según el panel actual
    const path = window.location.pathname;
    let rol = '';

    if (path.includes('panel_admin'))      rol = 'administrador';
    else if (path.includes('panel_bodeguero')) rol = 'bodeguero';
    else if (path.includes('panel_operario'))  rol = 'operario';

    // Si no hay sesión guardada, guardarla ahora
    if (!localStorage.getItem('logueado') && rol) {
        localStorage.setItem('logueado', 'true');
        localStorage.setItem('rol', rol);
    }

    // Anti-backtrack
    window.addEventListener('pageshow', function(e) {
        if (e.persisted && localStorage.getItem('logueado') !== 'true') {
            window.location.replace('../login/login.html');
        }
    });
}