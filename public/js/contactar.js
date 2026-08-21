document.addEventListener("DOMContentLoaded", () => {
    // Botones principales
    const btnContactar = document.getElementById("btnContactar");
    
    // Alerta Toast
    const alertaPedido = document.getElementById("alertaPedido");
    const mensajeDinamicoAlerta = document.getElementById("mensajeDinamicoAlerta");
    
    // Elementos del Modal
    const modalPedidoOverlay = document.getElementById("modalPedidoOverlay");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const btnEnviarPedido = document.getElementById("btnEnviarPedido");
    const inputMateria = document.getElementById("materiaPrima");
    const inputCantidad = document.getElementById("cantidadPedido");

    // 1. Abrir modal al hacer clic en "Realizar Pedido"
    btnContactar.addEventListener("click", () => {
        modalPedidoOverlay.classList.add("show");
    });

    // 2. Cerrar modal al hacer clic en "Cancelar" (o en el fondo oscuro)
    btnCerrarModal.addEventListener("click", () => {
        modalPedidoOverlay.classList.remove("show");
        inputCantidad.value = ""; // Limpia el campo
    });

    modalPedidoOverlay.addEventListener("click", (e) => {
        if (e.target === modalPedidoOverlay) {
            modalPedidoOverlay.classList.remove("show");
        }
    });

    // 3. Confirmar pedido desde el modal
    btnEnviarPedido.addEventListener("click", () => {
        const materia = inputMateria.value;
        const cantidad = inputCantidad.value;

        // Validamos que se haya puesto un número
        if (!cantidad || cantidad <= 0) {
            alert("Por favor, ingresa una cantidad válida.");
            return;
        }

        // Cerramos la ventanita
        modalPedidoOverlay.classList.remove("show");
        
        // Bloqueamos el botón principal como si estuviera cargando
        btnContactar.disabled = true;
        btnContactar.textContent = "Procesando pedido...";

        // Simulamos un retraso de 1 segundo cargando el envío
        setTimeout(() => {
            btnContactar.disabled = false;
            btnContactar.textContent = "Realizar Pedido";
            
            // Personalizamos el mensaje de la alerta flotante
            mensajeDinamicoAlerta.innerHTML = `Acabas de solicitar <b>${cantidad}</b> de <b>${materia}</b> al proveedor.`;

            // Mostramos la alerta verde
            alertaPedido.classList.add("mostrar");
            
            // Limpiamos el número del modal por si quiere hacer otro pedido
            inputCantidad.value = "";

            // Ocultamos la alerta después de 5 segundos
            setTimeout(() => {
                alertaPedido.classList.remove("mostrar");
            }, 5000);

        }, 1000);
    });
});