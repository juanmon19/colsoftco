(() => {
    'use strict';

    const tipos = { success: 'Éxito', error: 'Error', warning: 'Atención', info: 'Información' };
    let confirmando = false;
    const pendientes = [];

    // El diálogo local mantiene la confirmación operativa si el CDN tarda o falla.
    function confirmarLocal(titulo, texto, aceptar) {
        const dialogo = document.createElement('dialog');
        dialogo.className = 'aviso-modal aviso-local';
        const encabezado = document.createElement('h2');
        encabezado.id = 'aviso-titulo';
        encabezado.textContent = titulo;
        dialogo.setAttribute('aria-labelledby', encabezado.id);
        const detalle = document.createElement('p');
        detalle.textContent = texto;
        const acciones = document.createElement('div');
        acciones.className = 'aviso-acciones';
        for (const [etiqueta, valor] of [['Cancelar', 'cancelar'], [aceptar, 'aceptar']]) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'aviso-' + valor;
            boton.textContent = etiqueta;
            boton.addEventListener('click', () => dialogo.close(valor));
            acciones.append(boton);
        }
        dialogo.append(encabezado, detalle, acciones);
        document.body.append(dialogo);
        return new Promise(resolve => {
            dialogo.addEventListener('close', () => {
                dialogo.remove();
                resolve(dialogo.returnValue === 'aceptar');
            }, { once: true });
            dialogo.showModal();
            acciones.firstElementChild.focus();
        });
    }

    function toastLocal(tipo, mensaje) {
        document.querySelector('.aviso-toast-local')?.remove();
        const toast = document.createElement('div');
        toast.className = 'aviso-toast aviso-toast-local aviso-' + tipo;
        toast.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
        const etiqueta = document.createElement('strong');
        etiqueta.textContent = tipos[tipo];
        toast.append(etiqueta, document.createTextNode(mensaje));
        document.body.append(toast);
        setTimeout(() => toast.remove(), 5000);
    }

    window.mostrarNotificacion = function (tipo, mensaje) {
        tipo = Object.hasOwn(tipos, tipo) ? tipo : 'info';
        mensaje = String(mensaje ?? '');
        if (confirmando) {
            pendientes.push([tipo, mensaje]);
            return;
        }
        if (!window.Swal) return toastLocal(tipo, mensaje);
        return Swal.fire({
            toast: true, position: 'top-end', icon: tipo, titleText: mensaje,
            timer: 5000, timerProgressBar: true, showConfirmButton: false,
            iconColor: 'var(--aviso-acento)',
            customClass: { container: 'avisos', popup: 'aviso-toast aviso-' + tipo }
        });
    };

    async function confirmar(titulo, texto, aceptar) {
        if (confirmando) return false;
        confirmando = true;
        try {
            if (!window.Swal) return await confirmarLocal(titulo, texto, aceptar);
            const respuesta = await Swal.fire({
                titleText: titulo, text: texto, icon: 'question',
                iconColor: '#D4AF37', heightAuto: false, showCancelButton: true,
                confirmButtonText: aceptar, cancelButtonText: 'Cancelar',
                buttonsStyling: false, reverseButtons: true, focusCancel: true,
                customClass: {
                    container: 'avisos', popup: 'aviso-modal', actions: 'aviso-acciones',
                    confirmButton: 'aviso-aceptar', cancelButton: 'aviso-cancelar'
                }
            });
            return respuesta.isConfirmed;
        } finally {
            confirmando = false;
            for (const aviso of pendientes.splice(0)) window.mostrarNotificacion(...aviso);
        }
    }

    window.confirmarAccion = async function (titulo, texto, callback) {
        if (!await confirmar(titulo, texto, 'Confirmar')) return false;
        if (callback) await callback();
        return true;
    };

    window.confirmarCerrarSesion = async function (evento, url) {
        evento?.preventDefault();
        if (await confirmar('Cerrar sesión', '¿Deseas salir de ColSoftCO?', 'Cerrar sesión')) {
            window.location.assign(url);
        }
    };
})();
