<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/ReciboPDF.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    die('Recibo no válido.');
}

$conexion = new Conexion();
$dbConn = $conexion->getConnection();

$stmt = $dbConn->prepare(
    "SELECT h.id, m.nombre_modelo, h.cantidad, h.fecha_fabricacion
     FROM historial_produccion h
     INNER JOIN modelos_colchon m ON m.id_modelo = h.id_modelo
     WHERE h.id = :id"
);
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    http_response_code(404);
    die('No se encontró ese recibo.');
}

$fechaTexto = date('d/m/Y H:i', strtotime($registro['fecha_fabricacion']));

$pdf = new ReciboPDF($registro['id'], $fechaTexto);
$pdf->AddPage();
$pdf->agregarDetalle($registro['nombre_modelo'], (int) $registro['cantidad']);
$pdf->agregarMensajeExito();

$nombreArchivo = 'Recibo_' . str_pad($registro['id'], 6, '0', STR_PAD_LEFT) . '.pdf';

/* 'I' = vista previa dentro del navegador (el propio visor de PDF trae
   su botón de descarga/imprimir), en vez de forzar la descarga directa. */
$pdf->Output('I', $nombreArchivo);
