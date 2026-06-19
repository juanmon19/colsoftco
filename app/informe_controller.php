<?php
// view/generar_informe/informe_controller.php
// Recibe peticiones AJAX desde generar_informe.html y devuelve JSON

header('Content-Type: application/json; charset=utf-8');

// Ruta al archivo de lógica (ajusta si tu proyecto tiene otra ruta base)
require_once __DIR__ . '/../../app/logicaInforme.php';

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

try {
    $logica = new InformeLogica();

    switch ($accion) {

        // ── GET materias primas para la tabla del panel ──
        case 'materias':
            $data = $logica->getMateriasPrimas();
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        // ── GET movimientos (detalle) para informe ───────
        case 'movimientos':
            $mesInicio = (int)($_GET['mes_inicio'] ?? 1);
            $mesFin    = (int)($_GET['mes_fin']    ?? 12);
            $anio      = (int)($_GET['anio']       ?? date('Y'));

            if ($mesInicio < 1 || $mesInicio > 12 ||
                $mesFin    < 1 || $mesFin    > 12 ||
                $mesInicio > $mesFin) {
                echo json_encode(['ok' => false, 'msg' => 'Rango de meses inválido.']);
                break;
            }

            $data = $logica->getMovimientos($mesInicio, $mesFin, $anio);
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        // ── GET resumen por materia prima ────────────────
        case 'resumen':
            $mesInicio = (int)($_GET['mes_inicio'] ?? 1);
            $mesFin    = (int)($_GET['mes_fin']    ?? 12);
            $anio      = (int)($_GET['anio']       ?? date('Y'));

            $data = $logica->getResumen($mesInicio, $mesFin, $anio);
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        // ── GET comparación entre dos meses específicos ──
        case 'comparar':
            $mesA = (int)($_GET['mes_inicio'] ?? 1);
            $mesB = (int)($_GET['mes_fin']    ?? 12);
            $anio = (int)($_GET['anio']       ?? date('Y'));

            if ($mesA < 1 || $mesA > 12 || $mesB < 1 || $mesB > 12) {
                echo json_encode(['ok' => false, 'msg' => 'Mes inválido.']);
                break;
            }

            $data = $logica->getComparacionMeses($mesA, $mesB, $anio);
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        // ── GET comparativo: mes A vs mes B ──────────────
        case 'comparativo':
            $mesA = (int)($_GET['mes_a'] ?? 0);
            $mesB = (int)($_GET['mes_b'] ?? 0);
            $anio = (int)($_GET['anio']  ?? date('Y'));

            if ($mesA < 1 || $mesA > 12 || $mesB < 1 || $mesB > 12) {
                echo json_encode(['ok' => false, 'msg' => 'Meses inválidos.']);
                break;
            }

            $data = $logica->getComparativo($mesA, $anio, $mesB, $anio);
            echo json_encode(['ok' => true, 'data' => $data]);
            break;

        default:
            echo json_encode(['ok' => false, 'msg' => 'Acción no reconocida.']);
    }

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error del servidor: ' . $e->getMessage()]);
}
