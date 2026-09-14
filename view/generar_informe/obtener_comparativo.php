<?php
// Vista previa JSON del informe comparativo (una fila por material + diferencias).
// GET: mes_a=2026-8&mes_b=2026-9
header('Content-Type: application/json');
require_once "../../config/conexion.php";
require_once __DIR__ . '/../../app/logica_informes.php';

try {
    $anioActual = (int) date('Y');
    [$anioA, $mesA] = InformeLogica::parseAnioMes($_GET['mes_a'] ?? 1, $anioActual, 1);
    [$anioB, $mesB] = InformeLogica::parseAnioMes($_GET['mes_b'] ?? 12, $anioActual, 12);

    $logica = new InformeLogica();
    echo json_encode($logica->obtenerComparativo($mesA, $mesB, $anioA, $anioB));
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
