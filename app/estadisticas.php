<?php
/**
 * Endpoint AJAX del módulo view/estadisticas/.
 * Estilo $_REQUEST['accion'] como app/logica_tareas.php.
 * Acciones: resumen | rendimiento
 */
header('Content-Type: application/json');
session_start();
require_once '../config/conexion.php';
require_once __DIR__ . '/EstadisticasLogica.php';

// Solo administradores (el módulo vive en el menú del panel admin).
if (!isset($_SESSION['documento']) || (($_SESSION['rol'] ?? '') !== 'administrador')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado.']);
    exit();
}

$accion = $_REQUEST['accion'] ?? '';

try {
    $log = new EstadisticasLogica();

    /* ══ Resumen del rango + comparativa periodo anterior ══ */
    // rango: dia | semana | mes — fecha: 'Y-m-d' (mes: anio+mes opcionales)
    if ($accion === 'resumen') {
        $rango = $_REQUEST['rango'] ?? 'semana';
        $fecha = trim($_REQUEST['fecha'] ?? '');

        if ($rango === 'dia') {
            $data = $log->obtenerPorDia($fecha);
        } elseif ($rango === 'mes') {
            $anio = isset($_REQUEST['anio']) ? (int) $_REQUEST['anio'] : null;
            $mes  = isset($_REQUEST['mes']) ? (int) $_REQUEST['mes'] : null;
            if ($anio === null && $fecha !== '') {
                [$anio, $mes] = array_map('intval', explode('-', $fecha) + [0, 0]);
            }
            $data = $log->obtenerPorMes($anio, $mes);
        } else {
            $data = $log->obtenerPorSemana($fecha);
        }

        echo json_encode(['ok' => true, 'resumen' => $data]);
        exit();
    }

    /* ══ Panel de rendimiento (3 indicadores) ══ */
    // Recibe inicio/fin 'Y-m-d H:i:s' ya calculados por resumen, o los deriva del rango.
    if ($accion === 'rendimiento') {
        $inicio = trim($_REQUEST['inicio'] ?? '');
        $fin    = trim($_REQUEST['fin'] ?? '');

        if ($inicio === '' || $fin === '') {
            $rango = $_REQUEST['rango'] ?? 'semana';
            $fecha = trim($_REQUEST['fecha'] ?? '');
            $r = $rango === 'dia' ? $log->rangoDia($fecha)
               : ($rango === 'mes'
                   ? $log->rangoMes(
                         isset($_REQUEST['anio']) ? (int) $_REQUEST['anio'] : null,
                         isset($_REQUEST['mes']) ? (int) $_REQUEST['mes'] : null)
                   : $log->rangoSemana($fecha));
            $inicio = $r['inicio'];
            $fin    = $r['fin'];
        }

        echo json_encode(['ok' => true, 'rendimiento' => $log->calcularRendimiento($inicio, $fin)]);
        exit();
    }

    echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
} catch (Throwable $e) {
    // Patrón errores.php: ante fallo interno se responde error 500 controlado.
    http_response_code(500);
    error_log('estadisticas.php: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error interno al calcular estadísticas.']);
}
