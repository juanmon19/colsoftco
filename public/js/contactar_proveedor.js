document.addEventListener("DOMContentLoaded", () => {
    const btnContactar = document.getElementById("btnContactar");
    const modalPedidoOverlay = document.getElementById("modalPedidoOverlay");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const selectMateria = document.getElementById("materiaPrima");
    const unidadCantidad = document.getElementById("unidadCantidad");

    // Abrir modal al hacer clic en "Realizar Pedido"
    if (btnContactar) {
        btnContactar.addEventListener("click", () => {
            modalPedidoOverlay.classList.add("show");
        });
    }

    // Cerrar modal con el botón "Cancelar"
    if (btnCerrarModal) {
        btnCerrarModal.addEventListener("click", () => {
            modalPedidoOverlay.classList.remove("show");
        });
    }

    // Cerrar modal al hacer clic en el fondo oscuro
    if (modalPedidoOverlay) {
        modalPedidoOverlay.addEventListener("click", (e) => {
            if (e.target === modalPedidoOverlay) {
                modalPedidoOverlay.classList.remove("show");
            }
        });
    }

    // Muestra la unidad de medida del material seleccionado junto a "Cantidad"
    if (selectMateria && unidadCantidad) {
        selectMateria.addEventListener("change", () => {
            const opcionSeleccionada = selectMateria.options[selectMateria.selectedIndex];
            const unidad = opcionSeleccionada ? opcionSeleccionada.dataset.unidad : "";

            unidadCantidad.textContent = unidad ? `(${unidad})` : "";
        });
    }

    // Si el formulario se envía y hay un error de validación, el servidor
    // recarga la página mostrando el mensaje en la tarjeta principal.
    // No se necesita JS extra: el <form method="POST"> del modal hace el envío real.
});