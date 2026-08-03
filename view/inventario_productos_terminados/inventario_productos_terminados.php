<?php
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Max & Flex - Inventario Productos Terminados</title>
        <link href="inventariopt.css" rel="stylesheet" />
    </head>

    <body>
        <div class="header">
            <a class="logo" href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo" />
            </a>
        <div class="header-title"> PRODUCTOS TERMINADOS </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>

        </div>

        <div class="page-title-bar">
            <span class="page-title">PRODUCTOS TERMINADOS</span>
        </div>

        <div class="main-content">
            <div class="table-wrapper">
                <div class="toolbar">
                    <button class="btn-add" onclick="openAddModal()">
                        + Agregar Producto
                    </button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body"></tbody>
                </table>
            </div>
        </div>

        <div class="modal-overlay" id="modal-form">
            <div class="modal">
                <h3 id="modal-title">Agregar Producto</h3>
                <label>Producto</label>
                <input
                    type="text"
                    id="input-producto"
                    placeholder="Nombre del producto"
                />
                <label>Cantidad</label>
                <input
                    type="text"
                    id="input-cantidad"
                    placeholder="Ej: 23 Unidades"
                />
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeModal()">
                        Cancelar
                    </button>
                    <button class="btn-save" onclick="guardarProducto()">
                        Guardar
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-overlay" id="modal-confirm">
            <div class="modal">
                <h3>Confirmar eliminación</h3>
                <p class="confirm-msg" id="confirm-msg">
                    ¿Estás seguro de eliminar este producto?
                </p>
                <div class="modal-actions">
                    <button class="btn-cancel" onclick="closeModal()">
                        Cancelar
                    </button>
                    <button
                        class="btn-delete-confirm"
                        onclick="confirmarEliminacion()"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <div class="toast" id="toast"></div>

        <script>
            // ===== DATA =====
            // Estructura de persistencia local temporal (Simulación de base de datos relacional mediante objetos)
            let productos = [
                { id: 1, nombre: "Colchon Queen", cantidad: "x23 Unidades" },
                {
                    id: 2,
                    nombre: "Colchon Semidobile",
                    cantidad: "x29 Unidades",
                },
                { id: 3, nombre: "Colchon Doble", cantidad: "x33 Unidades" },
                { id: 4, nombre: "Colchon Sencillo", cantidad: "x43 Unidades" },
            ];

            // Apuntadores de estado globales para control de operaciones concurrentes
            let nextId = 5; // Llave primaria auto-incremental asignada a nuevos registros
            let editingId = null; // Identificador numérico del objeto en edición (Permanece en null en inserciones)
            let deletingId = null; // Almacenador temporal del ID agendado para descarte definitivo

            // ===== RENDER =====
            // Función dedicada a la reconstrucción adaptativa de la tabla en el documento HTML
            function renderTabla() {
                const tbody = document.getElementById("tabla-body");
                tbody.innerHTML = ""; // Limpieza de nodos hijos para evitar concatenaciones redundantes

                // Iteración secuencial de cada elemento del almacén de productos
                productos.forEach((p, i) => {
                    const tr = document.createElement("tr");
                    // Estructuración sintáctica de celdas con template literals e inyección de botones
                    tr.innerHTML = `
                    <td>${i + 1}. ${p.nombre}</td>
                    <td>${p.cantidad}</td>
                    <td>
                        <div class="action-cell">
                            <button class="btn-actualizar" onclick="openEditModal(${p.id})">Actualizar</button>
                            <button class="btn-eliminar"   onclick="openDeleteModal(${p.id})">Eliminar</button>
                        </div>
                    </td>
                `;
                    tbody.appendChild(tr); // Vinculación física del tr estructurado al contenedor tbody
                });

                // ===== FILAS VACÍAS DECORATIVAS =====
                // Bloque lógico para asegurar un mínimo visual estricto de 10 filas fijas en la interfaz
                const emptyRows = Math.max(0, 10 - productos.length);
                for (let i = 0; i < emptyRows; i++) {
                    const tr = document.createElement("tr");
                    tr.className = "empty-row"; // Identificador CSS especializado para filas sin datos (.empty-row)
                    tr.innerHTML = `<td>&nbsp;</td><td></td><td></td>`;
                    tbody.appendChild(tr);
                }
            }

            // ===== MODALS =====
            // Prepara los componentes del formulario para la adición de un nuevo artículo
            function openAddModal() {
                editingId = null; // Restablece estado global a inserción pura
                document.getElementById("modal-title").textContent =
                    "Agregar Producto";
                document.getElementById("input-producto").value = "";
                document.getElementById("input-cantidad").value = "";
                document.getElementById("modal-form").classList.add("active"); // Inyecta la propiedad visible (.active)
            }

            // Recupera datos de un producto específico y reconfigura el modal en modalidad de edición
            function openEditModal(id) {
                const p = productos.find((x) => x.id === id); // Localización por comparación estricta de ID
                if (!p) return;
                editingId = id; // Setea el identificador global de edición
                document.getElementById("modal-title").textContent =
                    "Actualizar Producto";
                document.getElementById("input-producto").value = p.nombre;
                document.getElementById("input-cantidad").value = p.cantidad;
                document.getElementById("modal-form").classList.add("active"); // Muestra ventana flotante de datos
            }

            // Gestiona la asignación y apertura de confirmación previo a la baja del registro
            function openDeleteModal(id) {
                const p = productos.find((x) => x.id === id);
                if (!p) return;
                deletingId = id; // Retiene en memoria el ID a eliminar
                document.getElementById("confirm-msg").textContent =
                    `¿Estás seguro de eliminar "${p.nombre}"?`;
                document
                    .getElementById("modal-confirm")
                    .classList.add("active"); // Despliega modal de confirmación
            }

            // Cierra masivamente todas las ventanas modales del entorno removiendo la clase activa
            function closeModal() {
                document
                    .querySelectorAll(".modal-overlay")
                    .forEach((m) => m.classList.remove("active"));
            }

            // ===== ACCIONES =====
            // Centraliza la persistencia o reescritura de los datos capturados del formulario
            function guardarProducto() {
                const nombre = document
                    .getElementById("input-producto")
                    .value.trim();
                const cantidad = document
                    .getElementById("input-cantidad")
                    .value.trim();

                // Validación restrictiva contra strings vacíos o con puros espacios
                if (!nombre || !cantidad) {
                    showToast("Complete todos los campos.");
                    return;
                }

                if (editingId === null) {
                    // Bifurcación: Registro e inserción de ítem nuevo
                    productos.push({ id: nextId++, nombre, cantidad });
                    showToast("Producto agregado.");
                } else {
                    // Bifurcación: Sobreescritura del ítem mutado en edición
                    const p = productos.find((x) => x.id === editingId);
                    if (p) {
                        p.nombre = nombre;
                        p.cantidad = cantidad;
                    }
                    showToast("Producto actualizado.");
                }
                closeModal(); // Oculta modales activos
                renderTabla(); // Re-renderiza el árbol de datos
            }

            // Ejecuta la depuración del producto del array mediante exclusión controlada
            function confirmarEliminacion() {
                // Filtra y genera una nueva colección omitiendo la id coincidente
                productos = productos.filter((x) => x.id !== deletingId);
                closeModal(); // Cierra modales
                renderTabla(); // Actualiza la vista tabular de datos
                showToast("Producto eliminado.");
            }

            // ===== TOAST =====
            // Inicializa y temporiza los cuadros emergentes asíncronos de estado
            function showToast(msg) {
                const t = document.getElementById("toast");
                t.textContent = msg;
                t.classList.add("show"); // Hace emerger visualmente el cuadro mediante CSS (.show)
                // Desactiva la clase e invisibiliza el elemento transcurridos 2.5 segundos (2500 ms)
                setTimeout(() => t.classList.remove("show"), 2500);
            }

            // Agrega un escuchador para cerrar las ventanas modales si el usuario da clic fuera de ellas
            document.querySelectorAll(".modal-overlay").forEach((overlay) => {
                overlay.addEventListener("click", (e) => {
                    if (e.target === overlay) closeModal();
                });
            });
        </script>
        <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
        <script
            src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js"
            defer
        ></script>
        <script src="../js/auth.js"></script>
        <script src="../js/app.js"></script>
    </body>
</html>
