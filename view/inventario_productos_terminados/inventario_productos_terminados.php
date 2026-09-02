<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$db = new Conexion();
$conn = $db->getConnection();

$usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';

/**
 * ==== ENDPOINT AJAX ====
 * Esta misma página atiende las acciones de agregar / actualizar / eliminar
 * que dispara el JavaScript de abajo. Siempre responde JSON y termina.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');

    $accion = $_POST['ajax_action'];

    try {
        switch ($accion) {

            case 'add': {
                    $nombre = trim($_POST['nombre'] ?? '');
                    $cantidad = $_POST['cantidad'] ?? '';

                    if ($nombre === '' || $cantidad === '' || !is_numeric($cantidad) || $cantidad < 0) {
                        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
                        exit();
                    }

                    $stmt = $conn->prepare(
                        "INSERT INTO productos_terminados (nombre_producto, stock_actual) VALUES (:nombre, :cantidad)"
                    );
                    $stmt->execute([':nombre' => $nombre, ':cantidad' => $cantidad]);
                    $idProducto = $conn->lastInsertId();

                    (new HistorialMovimientos())->registrar([
                        'modulo'         => 'producto_terminado',
                        'accion'         => 'crear',
                        'id_registro'    => $idProducto,
                        'descripcion'    => "Se agregó el producto terminado '{$nombre}' con stock {$cantidad}",
                        'datos_nuevos'   => ['nombre_producto' => $nombre, 'stock_actual' => $cantidad],
                        'usuario_nombre' => $usuarioNombre,
                    ]);

                    echo json_encode(['ok' => true, 'id' => (int) $idProducto]);
                    exit();
                }

            case 'edit': {
                    $id = $_POST['id'] ?? '';
                    $nombre = trim($_POST['nombre'] ?? '');
                    $cantidad = $_POST['cantidad'] ?? '';

                    if ($id === '' || $nombre === '' || $cantidad === '' || !is_numeric($cantidad) || $cantidad < 0) {
                        echo json_encode(['ok' => false, 'error' => 'Datos inválidos.']);
                        exit();
                    }

                    $stmt = $conn->prepare(
                        "UPDATE productos_terminados SET nombre_producto = :nombre, stock_actual = :cantidad WHERE id_producto = :id"
                    );
                    $stmt->execute([':nombre' => $nombre, ':cantidad' => $cantidad, ':id' => $id]);

                    (new HistorialMovimientos())->registrar([
                        'modulo'         => 'producto_terminado',
                        'accion'         => 'actualizar',
                        'id_registro'    => $id,
                        'descripcion'    => "Se actualizó el producto terminado '{$nombre}' a stock {$cantidad}",
                        'datos_nuevos'   => ['nombre_producto' => $nombre, 'stock_actual' => $cantidad],
                        'usuario_nombre' => $usuarioNombre,
                    ]);

                    echo json_encode(['ok' => true]);
                    exit();
                }

            case 'delete': {
                    $id = $_POST['id'] ?? '';

                    if ($id === '') {
                        echo json_encode(['ok' => false, 'error' => 'ID inválido.']);
                        exit();
                    }

                    $stmt = $conn->prepare("SELECT nombre_producto FROM productos_terminados WHERE id_producto = :id");
                    $stmt->execute([':id' => $id]);
                    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                    $stmt = $conn->prepare("DELETE FROM productos_terminados WHERE id_producto = :id");
                    $stmt->execute([':id' => $id]);

                    (new HistorialMovimientos())->registrar([
                        'modulo'         => 'producto_terminado',
                        'accion'         => 'eliminar',
                        'id_registro'    => $id,
                        'descripcion'    => "Se eliminó el producto terminado '" . ($producto['nombre_producto'] ?? $id) . "'",
                        'usuario_nombre' => $usuarioNombre,
                    ]);

                    echo json_encode(['ok' => true]);
                    exit();
                }

            default:
                echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
                exit();
        }
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

// ==== CARGA INICIAL DE LA TABLA ====
$productosDb = $conn->query(
    "SELECT id_producto, nombre_producto, stock_actual FROM productos_terminados ORDER BY nombre_producto"
)->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Max & Flex - Inventario Productos Terminados</title>
    <link rel="stylesheet" href="inventario_productos_terminados.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php
            $rolActual = 'Administrador';
            include __DIR__ . '/../partials/topbar.php';
            ?>

            <main class="content">
<div class="container">

        <a class="btn-volver" href="../panel_admin/panel_admin.php">
            ← Volver
        </a>

        <!-- =========================
             BARRA DE FILTROS
        ========================== -->
        <div class="filtros-container">

            <div class="filtro-busqueda">
                <input
                    type="text"
                    id="inputBusquedaProducto"
                    placeholder="Buscar por nombre del producto...">
            </div>

            <div class="filtro-orden">
                <label for="selectOrden">Ordenar:</label>
                <select id="selectOrden">
                    <option value="az">Nombre (A-Z)</option>
                    <option value="za">Nombre (Z-A)</option>
                </select>
            </div>

            <button type="button" id="btnLimpiarFiltros" class="btn-limpiar-filtros">
                Limpiar filtros
            </button>

        </div>

        <p id="mensajeSinResultados" class="mensaje-sin-resultados" style="display:none;">
            No se encontraron productos que coincidan con la búsqueda.
        </p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tabla-body"></tbody>

            </table>
        </div>

    </div>

    <!-- ══ MODAL AGREGAR / EDITAR ══ -->
    <div class="modal-overlay" id="modal-form">
        <div class="modal">
            <h3 id="modal-title">Agregar Producto</h3>
            <label>Producto</label>
            <input
                type="text"
                id="input-producto"
                placeholder="Nombre del producto" />
            <label>Cantidad</label>
            <input
                type="number"
                id="input-cantidad"
                min="0"
                step="0.01"
                placeholder="Ej: 23" />
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

    <!-- ══ MODAL CONFIRMAR ELIMINACIÓN ══ -->
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
                    onclick="confirmarEliminacion()">
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
<script>
        // ===== DATA =====
        // Los productos ya no viven solo en memoria: se cargan desde la
        // base de datos (tabla productos_terminados) al renderizar la página.
        let productos = <?= json_encode(array_map(function ($p) {
                            return [
                                'id' => (int) $p['id_producto'],
                                'nombre' => $p['nombre_producto'],
                                'cantidad' => (float) $p['stock_actual'],
                            ];
                        }, $productosDb), JSON_UNESCAPED_UNICODE) ?>;

        let editingId = null; // Identificador numérico del objeto en edición (Permanece en null en inserciones)
        let deletingId = null; // Almacenador temporal del ID agendado para descarte definitivo

        const inputBusqueda = document.getElementById('inputBusquedaProducto');
        const selectOrden = document.getElementById('selectOrden');
        const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
        const mensajeSinResultados = document.getElementById('mensajeSinResultados');

        function formatCantidad(n) {
            const num = Number(n);
            const texto = Number.isInteger(num) ? num : num.toFixed(2);
            return `${texto} Unidades`;
        }

        // ===== RENDER (aplica búsqueda + orden sobre la lista en memoria) =====
        function renderTabla() {
            const tbody = document.getElementById("tabla-body");
            tbody.innerHTML = "";

            const texto = inputBusqueda.value.trim().toLowerCase();
            const orden = selectOrden.value;

            let visibles = productos.filter(p =>
                texto === '' || p.nombre.toLowerCase().includes(texto)
            );

            visibles = visibles.sort((a, b) =>
                orden === 'az'
                    ? a.nombre.localeCompare(b.nombre)
                    : b.nombre.localeCompare(a.nombre)
            );

            visibles.forEach((p) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>${p.nombre}</td>
                    <td>${formatCantidad(p.cantidad)}</td>
                    <td class="acciones">
                        <div class="action-cell">
                            <button class="btn-actualizar" onclick="openEditModal(${p.id})">Actualizar</button>
                            <button class="btn-eliminar"   onclick="openDeleteModal(${p.id})">Eliminar</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            mensajeSinResultados.style.display = visibles.length === 0 ? 'block' : 'none';
        }

        inputBusqueda.addEventListener('input', renderTabla);
        selectOrden.addEventListener('change', renderTabla);

        btnLimpiarFiltros.addEventListener('click', () => {
            inputBusqueda.value = '';
            selectOrden.value = 'az';
            renderTabla();
        });

        // ===== MODALS =====
        function openAddModal() {
            editingId = null;
            document.getElementById("modal-title").textContent =
                "Agregar Producto";
            document.getElementById("input-producto").value = "";
            document.getElementById("input-cantidad").value = "";
            document.getElementById("modal-form").classList.add("active");
        }

        function openEditModal(id) {
            const p = productos.find((x) => x.id === id);
            if (!p) return;
            editingId = id;
            document.getElementById("modal-title").textContent =
                "Actualizar Producto";
            document.getElementById("input-producto").value = p.nombre;
            document.getElementById("input-cantidad").value = p.cantidad;
            document.getElementById("modal-form").classList.add("active");
        }

        function openDeleteModal(id) {
            const p = productos.find((x) => x.id === id);
            if (!p) return;
            deletingId = id;
            document.getElementById("confirm-msg").textContent =
                `¿Estás seguro de eliminar "${p.nombre}"?`;
            document
                .getElementById("modal-confirm")
                .classList.add("active");
        }

        function closeModal() {
            document
                .querySelectorAll(".modal-overlay")
                .forEach((m) => m.classList.remove("active"));
        }

        // ===== HELPER AJAX =====
        async function llamarServidor(datos) {
            const body = new URLSearchParams(datos);
            const res = await fetch(window.location.pathname, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body,
            });
            return res.json();
        }

        // ===== ACCIONES =====
        async function guardarProducto() {
            const nombre = document
                .getElementById("input-producto")
                .value.trim();
            const cantidad = document
                .getElementById("input-cantidad")
                .value.trim();

            if (!nombre || cantidad === "" || isNaN(cantidad) || Number(cantidad) < 0) {
                showToast("Complete todos los campos con datos válidos.");
                return;
            }

            let resultado;
            if (editingId === null) {
                resultado = await llamarServidor({
                    ajax_action: "add",
                    nombre,
                    cantidad,
                });
            } else {
                resultado = await llamarServidor({
                    ajax_action: "edit",
                    id: editingId,
                    nombre,
                    cantidad,
                });
            }

            if (!resultado.ok) {
                showToast(resultado.error || "No se pudo guardar el producto.");
                return;
            }

            if (editingId === null) {
                productos.push({ id: resultado.id, nombre, cantidad: Number(cantidad) });
                showToast("Producto agregado.");
            } else {
                const p = productos.find((x) => x.id === editingId);
                if (p) {
                    p.nombre = nombre;
                    p.cantidad = Number(cantidad);
                }
                showToast("Producto actualizado.");
            }

            closeModal();
            renderTabla();
        }

        async function confirmarEliminacion() {
            const resultado = await llamarServidor({
                ajax_action: "delete",
                id: deletingId,
            });

            if (!resultado.ok) {
                showToast(resultado.error || "No se pudo eliminar el producto.");
                closeModal();
                return;
            }

            productos = productos.filter((x) => x.id !== deletingId);
            closeModal();
            renderTabla();
            showToast("Producto eliminado.");
        }

        // ===== TOAST =====
        function showToast(msg) {
            const t = document.getElementById("toast");
            t.textContent = msg;
            t.classList.add("show");
            setTimeout(() => t.classList.remove("show"), 2500);
        }

        document.querySelectorAll(".modal-overlay").forEach((overlay) => {
            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) closeModal();
            });
        });

        renderTabla();
    </script>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script src="../../public/js/app.js"></script>
</body>

</html>