<?php
require_once __DIR__ . '/../../app/verificar_sesion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../config/conexion.php';

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

// =========================================================================
// DÍAS / SEMANAS / MESES CON MOVIMIENTOS REALES (para bloquear en el
// selector de "Generar informe PDF" cualquier período sin actividad).
// Se combinan historial_movimientos + historial_produccion (colchones),
// porque un mes puede tener solo fabricación y cero entradas/salidas.
// =========================================================================
$fechasHistorial = $historial->obtenerFechasDisponibles();

$conexionFechas = new Conexion();
$dbFechas = $conexionFechas->getConnection();
$fechasProduccion = $dbFechas->query(
    "SELECT DISTINCT DATE(fecha_fabricacion) AS fecha FROM historial_produccion"
)->fetchAll(PDO::FETCH_COLUMN);

$fechasDisponibles = array_unique(array_merge($fechasHistorial, $fechasProduccion));
rsort($fechasDisponibles);

$MESES_NOMBRE = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];

$semanasDisponibles = []; // valor "YYYY-Www" => etiqueta
$mesesDisponibles = [];   // valor "YYYY-MM" => etiqueta

foreach ($fechasDisponibles as $fechaTexto) {
    if (!$fechaTexto) {
        continue;
    }
    $dt = new DateTime($fechaTexto);

    $valorSemana = $dt->format('o') . '-W' . $dt->format('W');
    if (!isset($semanasDisponibles[$valorSemana])) {
        $semanasDisponibles[$valorSemana] = 'Semana ' . $dt->format('W') . ' de ' . $dt->format('o');
    }

    $valorMes = $dt->format('Y-m');
    if (!isset($mesesDisponibles[$valorMes])) {
        $mesesDisponibles[$valorMes] = $MESES_NOMBRE[(int) $dt->format('n')] . ' de ' . $dt->format('Y');
    }
}

// Semanas y meses más recientes primero
krsort($semanasDisponibles);
krsort($mesesDisponibles);

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
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Movimientos - COLSOFTCO</title>
    <link rel="stylesheet" href="historial.css">
</head>

<body>

    <header>
        <div class="logo">
            <a href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Historial de Movimientos</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="page-body">
        <main class="content">

            <div class="tarjeta">
                <h2>Registro de movimientos del sistema</h2>
                <p class="subtitulo">
                    Aquí se registran automáticamente las acciones realizadas en los
                    demás módulos: materia prima, proveedores, stock, alertas y producción.
                </p>
            </div>

            <!-- GENERAR INFORME PDF -->
            <div class="tarjeta">
                <h2>Generar informe en PDF</h2>
                <p class="subtitulo">
                    Agrupa <strong>entradas y salidas de materia prima</strong>,
                    <strong>proveedores registrados</strong>, <strong>colchones fabricados</strong>
                    y <strong>usuarios registrados</strong> en el rango que elijas.
                    Solo se muestran los días, semanas y meses en los que sí hubo actividad registrada.
                </p>

                <?php if (empty($fechasDisponibles)): ?>

                    <p class="subtitulo" style="color:#b91c1c; font-weight:700;">
                        Todavía no hay ningún movimiento registrado en el sistema, así que no
                        se puede generar un informe por ahora.
                    </p>

                <?php else: ?>

                <form id="formInforme" class="filtros" action="historial_informe_pdf.php" method="GET">

                    <div class="campo-filtro">
                        <label for="tipoDia">Rango</label>
                        <div class="grupo-rango">
                            <label class="opcion-rango">
                                <input type="radio" name="tipo" value="dia" id="tipoDia" checked>
                                Día
                            </label>
                            <label class="opcion-rango">
                                <input type="radio" name="tipo" value="semana">
                                Semana
                            </label>
                            <label class="opcion-rango">
                                <input type="radio" name="tipo" value="mes">
                                Mes
                            </label>
                        </div>
                    </div>

                    <div class="campo-filtro" data-para="dia">
                        <label for="fechaDia">Selecciona el día</label>
                        <select id="fechaDia" name="fecha_dia">
                            <?php foreach ($fechasDisponibles as $fecha): ?>
                                <option value="<?= htmlspecialchars($fecha) ?>">
                                    <?= date('d/m/Y', strtotime($fecha)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-filtro oculto" data-para="semana">
                        <label for="fechaSemana">Selecciona la semana</label>
                        <select id="fechaSemana" name="fecha_semana">
                            <?php foreach ($semanasDisponibles as $valor => $etiqueta): ?>
                                <option value="<?= htmlspecialchars($valor) ?>">
                                    <?= htmlspecialchars($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-filtro oculto" data-para="mes">
                        <label for="fechaMes">Selecciona el mes</label>
                        <select id="fechaMes" name="fecha_mes">
                            <?php foreach ($mesesDisponibles as $valor => $etiqueta): ?>
                                <option value="<?= htmlspecialchars($valor) ?>">
                                    <?= htmlspecialchars($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-filtro acciones-filtro">
                        <button type="submit" class="btn btn-informe" id="btnGenerarInforme">Generar informe PDF</button>
                    </div>

                </form>

                <?php endif; ?>
            </div>

            <!-- FILTROS DE LA TABLA -->
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($movimientos) === 0): ?>
                                <tr>
                                    <td colspan="5" class="sin-resultados">
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
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <div class="paginacion">
                        <?php if ($pagina > 1): ?>
                            <a href="?<?= htmlspecialchars(construirQuery($filtros, $pagina - 1)) ?>" class="pagina-link">&laquo; Anterior</a>
                        <?php endif; ?>

                        <span class="pagina-actual">Página <?= $pagina ?> de <?= $totalPaginas ?></span>

                        <?php if ($pagina < $totalPaginas): ?>
                            <a href="?<?= htmlspecialchars(construirQuery($filtros, $pagina + 1)) ?>" class="pagina-link">Siguiente &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <p class="total-resultados"><?= $totalRegistros ?> movimiento(s) encontrado(s)</p>
            </div>

        </main>
    </div>

    <footer>
        <div class="footer-divider"></div>
        <div class="footer-top">
            <div>
                <p class="footer-brand-name">COLSOFTCO</p>
                <p class="footer-brand-sub">Sistema de Gestión</p>
                <p class="footer-brand-desc">
                    Sistema de gestión y administración de materias primas para Max&Flex.
                    Eficiencia en inventarios y movimientos empresariales.
                </p>
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

    <script src="../../public/js/app.js"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

    <script>
        const radios = document.querySelectorAll('input[name="tipo"]');
        const campos = document.querySelectorAll('.campo-filtro[data-para]');

        function actualizarCampoFecha() {
            const tipoSeleccionadoEl = document.querySelector('input[name="tipo"]:checked');
            if (!tipoSeleccionadoEl) return;
            const tipoSeleccionado = tipoSeleccionadoEl.value;

            campos.forEach(campo => {
                const input = campo.querySelector('input, select');
                if (campo.dataset.para === tipoSeleccionado) {
                    campo.classList.remove('oculto');
                    if (input) input.required = true;
                } else {
                    campo.classList.add('oculto');
                    if (input) input.required = false;
                }
            });
        }

        radios.forEach(radio => radio.addEventListener('change', actualizarCampoFecha));
        actualizarCampoFecha();
    </script>
</body>

</html>