/**
 * menu_activo.js - Marca en dorado el módulo del sidebar en el que está el usuario.
 *
 * Funciona con los dos tipos de sidebar del sistema:
 *   - Vistas de módulo y panel admin:  .sidebar .nav-item  (botones con onclick)
 *   - Panel bodeguero y operario:      .sidebar .sidebar-links button
 *
 * No hay que tocar el HTML de cada módulo: el script lee el destino
 * del onclick (o del href) y lo compara con la página actual.
 */
(function () {

    /** Convierte cualquier ruta relativa en una ruta absoluta comparable. */
    function normalizar(url) {
        try {
            return new URL(url, window.location.href).pathname
                .toLowerCase()
                .replace(/\/+/g, '/');
        } catch (e) {
            return '';
        }
    }

    /** Devuelve la carpeta de módulo de una ruta: /view/<modulo>/... */
    function modulo(ruta) {
        var m = ruta.match(/\/view\/([^\/]+)\//);
        return m ? m[1] : '';
    }

    /** Saca el destino de un botón del menú. */
    function destino(boton) {
        if (boton.dataset && boton.dataset.href) return boton.dataset.href;

        var onclick = boton.getAttribute('onclick') || '';
        var m = onclick.match(/location\.href\s*=\s*['"]([^'"]+)['"]/);
        if (m) return m[1];

        if (boton.tagName === 'A' && boton.getAttribute('href')) {
            return boton.getAttribute('href');
        }
        return null;
    }

    function marcarActivo() {
        var items = document.querySelectorAll(
            '.sidebar .nav-item, .sidebar .sidebar-links button, .sidebar .nav a'
        );
        if (!items.length) return;

        var actual = normalizar(window.location.href);
        var moduloActual = modulo(actual);
        var enPanel = /panel_(admin|bodeguero|operario)\.php$/.test(actual) ||
                      /ir_panel\.php$/.test(actual);

        var rutas = [];
        items.forEach(function (item) {
            item.classList.remove('activo');
            var d = destino(item);
            rutas.push(d ? normalizar(d) : '');
        });

        // 1) Coincidencia exacta de archivo
        var marcado = false;
        items.forEach(function (item, i) {
            if (rutas[i] && rutas[i] === actual) {
                item.classList.add('activo');
                marcado = true;
            }
        });

        // 2) Misma carpeta de módulo (para subpáginas: editar, registrar, etc.)
        if (!marcado && moduloActual) {
            items.forEach(function (item, i) {
                if (!marcado && rutas[i] && modulo(rutas[i]) === moduloActual) {
                    item.classList.add('activo');
                    marcado = true;
                }
            });
        }

        // 3) Si estamos en un panel, se marca "Panel Principal"
        if (!marcado && enPanel) {
            items.forEach(function (item, i) {
                if (!marcado && rutas[i] && /ir_panel\.php$/.test(rutas[i])) {
                    item.classList.add('activo');
                    marcado = true;
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', marcarActivo);
    } else {
        marcarActivo();
    }
})();
