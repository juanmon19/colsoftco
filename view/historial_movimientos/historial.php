<?php
require_once __DIR__ . '/../../app/verificar_sesion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$historial = new HistorialMovimientos();

// --- Filtros recibidos por GET ---
$filtros = [
    'modulo'       => $_GET['modulo'] ?? '',
    'accion'       => $_GET['accion'] ?? '',
    'usuario'      => $_GET['usuario'] ?? '',
    'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
    'fecha_fin'    => $_GET['fecha_fin'] ?? '',
    'buscar'       => $_GET['buscar'] ?? '',
];

// --- Paginación ---
$porPagina = 15;
$pagina    = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;

$totalRegistros = $historial->contarRegistros($filtros);
$totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));
$movimientos    = $historial->obtener($filtros, $pagina, $porPagina);

$modulosDisponibles  = $historial->obtenerModulos();
$accionesDisponibles = $historial->obtenerAcciones();

function claseAccion(string $accion): string
{
    return match ($accion) {
        'crear'    => 'badge badge-crear',
        'editar'   => 'badge badge-editar',
        'eliminar' => 'badge badge-eliminar',
        'entrada'  => 'badge badge-entrada',
        'salida'   => 'badge badge-salida',
        default    => 'badge badge-default',
    };
}

function construirQuery(array $filtros, int $pagina): string
{
    $params = array_filter($filtros);
    $params['pagina'] = $pagina;
    return http_build_query($params);
}
?>

    <link rel="stylesheet" href="historial.css">


    <div class="page-body">
        <main class="content">

            <div class="tarjeta">
                <h2>Registro de movimientos del sistema</h2>
                <p class="subtitulo">
                    Aquí se registran automáticamente las acciones realizadas en los
                    demás módulos: materia prima, proveedores, stock, alertas y producción.
                </p>
            </div>

            <!-- FILTROS -->
            <div class="tarjeta">
                <form class="filtros" method="GET" action="historial.php">

                    <div class="campo-filtro">
                        <label for="modulo">Módulo</label>
                        <select name="modulo" id="modulo">
                            <option value="">Todos</option>
                            <?php foreach ($modulosDisponibles as $mod): ?>
                                <option value="<?= htmlspecialchars($mod) ?>"
                                    <?= $filtros['modulo'] === $mod ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $mod))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-filtro">
                        <label for="accion">Acción</label>
                        <select name="accion" id="accion">
                            <option value="">Todas</option>
                            <?php foreach ($accionesDisponibles as $acc): ?>
                                <option value="<?= htmlspecialchars($acc) ?>"
                                    <?= $filtros['accion'] === $acc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst($acc)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-filtro">
                        <label for="usuario">Usuario</label>
                        <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario"
                            value="<?= htmlspecialchars($filtros['usuario']) ?>">
                    </div>

                    <div class="campo-filtro">
                        <label for="fecha_inicio">Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                            value="<?= htmlspecialchars($filtros['fecha_inicio']) ?>">
                    </div>

                    <div class="campo-filtro">
                        <label for="fecha_fin">Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                            value="<?= htmlspecialchars($filtros['fecha_fin']) ?>">
                    </div>

                    <div class="campo-filtro campo-buscar">
                        <label for="buscar">Buscar en descripción</label>
                        <input type="text" name="buscar" id="buscar" placeholder="Ej: espuma HR-28"
                            value="<?= htmlspecialchars($filtros['buscar']) ?>">
                    </div>

                    <div class="campo-filtro acciones-filtro">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="historial.php" class="btn btn-outline">Limpiar</a>
                    </div>
                </form>
            </div>

            <!-- TABLA -->
            <div class="tarjeta">
                <div class="tabla-wrapper">
                    <table class="tabla-historial">
                        <thead>
                            <tr>
                                <th>Fecha y hora</th>
                                <th>Módulo</th>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>Usuario</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($movimientos) === 0): ?>
                                <tr>
                                    <td colspan="6" class="sin-resultados">
                                        No se encontraron movimientos con los filtros seleccionados.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($mov['fecha_hora'])) ?></td>
                                        <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $mov['modulo']))) ?></td>
                                        <td><span class="<?= claseAccion($mov['accion']) ?>"><?= htmlspecialchars(ucfirst($mov['accion'])) ?></span></td>
                                        <td><?= htmlspecialchars($mov['descripcion']) ?></td>
                                        <td><?= htmlspecialchars($mov['usuario_nombre']) ?></td>
                                        <td>
                                            <?php if ($mov['datos_anteriores'] || $mov['datos_nuevos']): ?>
                                                <button type="button" class="btn-detalle"
                                                    onclick="mostrarDetalle(<?= (int) $mov['id'] ?>)">
                                                    Ver
                                                </button>
                                                <div id="detalle-<?= (int) $mov['id'] ?>" class="detalle-oculto">
                                                    <?php if ($mov['datos_anteriores']): ?>
                                                        <strong>Antes:</strong>
                                                        <pre><?= htmlspecialchars(json_encode(json_decode($mov['datos_anteriores']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                                    <?php endif; ?>
                                                    <?php if ($mov['datos_nuevos']): ?>
                                                        <strong>Después:</strong>
                                                        <pre><?= htmlspecialchars(json_encode(json_decode($mov['datos_nuevos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <div class="paginacion">
                        <?php if ($pagina > 1): ?>
                            <a href="?<?= construirQuery($filtros, $pagina - 1) ?>" class="pagina-link">&laquo; Anterior</a>
                        <?php endif; ?>

                        <span class="pagina-actual">Página <?= $pagina ?> de <?= $totalPaginas ?></span>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a href="?<?= construirQuery($filtros, $pagina + 1) ?>" class="pagina-link">Siguiente &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <p class="total-resultados"><?= $totalRegistros ?> movimiento(s) encontrado(s)</p>
            </div>

        </main>
    </div>


    <script>
        function mostrarDetalle(id) {
            const el = document.getElementById('detalle-' + id);
            el.classList.toggle('detalle-visible');
        }
    </script>
