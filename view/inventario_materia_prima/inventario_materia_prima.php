<?php

require_once('../../config/conexion.php');

/* Evita que el navegador restaure esta página desde su caché al
   presionar "atrás", lo que mostraría materiales ya eliminados o
   datos desactualizados. */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$db = new Conexion();
$conn = $db->getConnection();

$sql = "
SELECT id_material, nombre_material, stock_actual, stock_minimo, estado
FROM materias_primas
ORDER BY id_material ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link rel="stylesheet" href="lista_inventario.css">
    <style>
        .filtros-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            padding: 14px 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filtro-busqueda {
            flex: 1;
            min-width: 220px;
        }

        .filtro-busqueda input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d7dee7;
            border-radius: 6px;
            font-size: 14px;
            background: #f8fafc;
        }

        .filtro-busqueda input:focus {
            outline: none;
            border-color: #1e3a8a;
        }

        .filtro-orden {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .filtro-orden label {
            font-weight: 700;
            font-size: 14px;
            color: #1e293b;
        }

        .filtro-orden select {
            padding: 9px 12px;
            border: 1px solid #d7dee7;
            border-radius: 6px;
            font-size: 14px;
            background: #f8fafc;
        }

        .btn-limpiar-filtros {
            padding: 10px 16px;
            border: 1px solid #1e293b;
            border-radius: 6px;
            background: #fff;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-limpiar-filtros:hover {
            background: #f1f5f9;
        }

        .mensaje-sin-resultados {
            text-align: center;
            padding: 16px;
            color: #64748b;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Inventario de Materias Primas</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>


    <div class="container">

  

        <!-- =========================
             BARRA DE FILTROS
        ========================== -->
        <div class="filtros-container">

            <div class="filtro-busqueda">
                <input
                    type="text"
                    id="inputBusquedaMaterial"
                    placeholder="Buscar por nombre del material...">
            </div>

            <select id="filtroEstado" onchange="filtrarPorEstado()" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;">
                <option value="todos">Todos</option>
                <option value="activo" selected>Solo activos</option>
                <option value="inactivo">Solo inactivos</option>
            </select>

            <div class="filtro-orden">
                <label for="selectOrden">   Ordenar:</label>
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
            No se encontraron materiales que coincidan con la búsqueda.
        </p>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Material</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Alerta Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="listaMateriales">

                    <?php foreach ($materiales as $m): ?>
                        <tr data-nombre="<?php echo htmlspecialchars(strtolower($m['nombre_material'])); ?>">
                            <td><?= $m['id_material'] ?></td>
                            <td><?= htmlspecialchars($m['nombre_material']) ?></td>
                            <td><?= $m['stock_actual'] ?></td>
                            <td><?= $m['stock_minimo'] ?></td>
                            <td>
                                <?php if ($m['stock_actual'] <= $m['stock_minimo']): ?>
                                    <span class="alerta">STOCK BAJO</span>
                                <?php else: ?>
                                    <span class="normal">DISPONIBLE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="display:inline-block;padding:4px 10px;border-radius:12px;font-size:11px;font-weight:bold;
                                             background:<?= $m['estado'] === 'activo' ? '#e5f7ec' : '#fdecea' ?>;
                                             color:<?= $m['estado'] === 'activo' ? '#1e7e42' : '#c0392b' ?>;">
                                    <?= $m['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="acciones">
                                <a class="btn-editar" href="../control_de_stock/inventario/editar_inventario.php?id=<?= $m['id_material'] ?>">
                                    Editar
                                </a>
                                <a href="../control_de_stock/inventario/eliminar_inventario.php?id=<?= $m['id_material'] ?>" class="btn-eliminar">
                                    Eliminar
                                </a>
                                <button class="btn-toggle-estado"
                                        data-id="<?= $m['id_material'] ?>"
                                        data-estado="<?= $m['estado'] ?>"
                                        style="padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:bold;
                                               background:<?= $m['estado'] === 'activo' ? '#fdecea' : '#e5f7ec' ?>;
                                               color:<?= $m['estado'] === 'activo' ? '#c0392b' : '#1e7e42' ?>;">
                                    <?= $m['estado'] === 'activo' ? 'Deshabilitar' : 'Habilitar' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>

            </table>
        </div>

    </div>

    <footer>
        <div class="footer-divider"></div>
        <div class="footer-top">
            <div>
                <p class="footer-brand-name">COLSOFTCO</p>
                <p class="footer-brand-sub">Sistema de Gestión</p>
                <p class="footer-brand-desc">Sistema de gestión y administración de materias primas para Max&Flex. Eficiencia en inventarios y movimientos empresariales.</p>
            </div>
            <div>
                <p class="footer-col-title">Contacto</p>
                <div class="footer-contact-item">📍 Bogotá, Colombia</div>
                <div class="footer-contact-item">✉ contacto@colsoftco.com</div>
                <div class="footer-contact-item">📞 +57 (1) 234-5678</div>
                <div class="footer-contact-item">🕐 Lun – Vie: 8:00 am – 6:00 pm</div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.</span>
            <span>Desarrollado por <strong>Equipo SENA</strong></span>
        </div>
    </footer>
    <script>
        // =========================
        // FILTROS: BÚSQUEDA + ORDEN ALFABÉTICO
        // =========================
        (function() {

            const inputBusqueda = document.getElementById('inputBusquedaMaterial');
            const selectOrden = document.getElementById('selectOrden');
            const btnLimpiar = document.getElementById('btnLimpiarFiltros');
            const contenedorLista = document.getElementById('listaMateriales');
            const mensajeSinResultados = document.getElementById('mensajeSinResultados');

            if (!contenedorLista) return;

            const filas = Array.from(
                contenedorLista.querySelectorAll('tr[data-nombre]')
            );

            function aplicarFiltros() {

                const texto = inputBusqueda.value.trim().toLowerCase();
                let visibles = 0;

                filas.forEach(fila => {

                    const nombre = fila.dataset.nombre || '';

                    const coincide =
                        texto === '' ||
                        nombre.includes(texto);

                    fila.style.display = coincide ? '' : 'none';

                    if (coincide) visibles++;
                });

                mensajeSinResultados.style.display = visibles === 0 ? 'block' : 'none';
            }

            function aplicarOrden() {

                const orden = selectOrden.value;

                const filasOrdenadas = [...filas].sort((a, b) => {

                    const nombreA = a.dataset.nombre || '';
                    const nombreB = b.dataset.nombre || '';

                    return orden === 'az'
                        ? nombreA.localeCompare(nombreB)
                        : nombreB.localeCompare(nombreA);
                });

                filasOrdenadas.forEach(fila => {
                    contenedorLista.appendChild(fila);
                });
            }

            inputBusqueda.addEventListener('input', aplicarFiltros);
            selectOrden.addEventListener('change', aplicarOrden);

            btnLimpiar.addEventListener('click', () => {
                inputBusqueda.value = '';
                selectOrden.value = 'az';
                aplicarFiltros();
                aplicarOrden();
            });

            // Orden inicial A-Z al cargar la página
            aplicarOrden();

        })();
    </script>

    <script>
    /* Detectamos si esta carga de la página viene de la bfcache
       (botón "atrás/adelante"), combinando dos señales para mayor
       confiabilidad entre navegadores:
       1) event.persisted en el evento pageshow
       2) el "type" reportado por la Navigation Timing API
       Si cualquiera de las dos indica que venimos de bfcache, forzamos
       una recarga real para que PHP vuelva a consultar el estado
       actual de la base de datos en vez de mostrar una copia vieja. */
    window.addEventListener('pageshow', function (event) {
        const entradasNav = performance.getEntriesByType('navigation');
        const tipoNav = entradasNav.length ? entradasNav[0].type : null;

        // TEMPORAL: para diagnosticar, borrar esta línea después de probar
        console.log('[diagnóstico bfcache] persisted:', event.persisted, '| tipo navegación:', tipoNav);

        if (event.persisted || tipoNav === 'back_forward') {
            console.log('[diagnóstico bfcache] Recargando por venir de bfcache...');
            window.location.reload();
        }
    });
    </script>

    <script>
        // Toggle estado via AJAX
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-toggle-estado');
            if (!btn) return;

            const id = btn.dataset.id;
            const estadoActual = btn.dataset.estado;
            const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
            const accion = estadoActual === 'activo' ? 'deshabilitar' : 'habilitar';

            if (!confirm(`¿Desea ${accion} esta materia prima?`)) return;

            try {
                const resp = await fetch('../../../app/logica_inventario_api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `accion=cambiar_estado&id=${id}&estado=${nuevoEstado}`
                });
                const data = await resp.json();
                if (data.ok) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al cambiar estado.');
                }
            } catch(e) {
                alert('Error de conexión.');
            }
        });

        // Filtrar por estado
        function filtrarPorEstado() {
            const filtro = document.getElementById('filtroEstado').value;
            const filas = document.querySelectorAll('#listaMateriales tr');
            filas.forEach(fila => {
                const estado = fila.querySelector('.btn-toggle-estado')?.dataset.estado;
                if (!estado) return;
                if (filtro === 'todos') fila.style.display = '';
                else fila.style.display = (estado === filtro) ? '' : 'none';
            });
        }

        // Apply default filter on load
        window.addEventListener('DOMContentLoaded', filtrarPorEstado);
    </script>

</body>
</html>