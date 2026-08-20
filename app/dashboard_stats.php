<?php

header('Content-Type: application/json');
require_once '../config/conexion.php';

$conexion = new Conexion();
$db = $conexion->getConnection();

$inventarioTotal  = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM materias_primas")->fetchColumn();
$proveedoresTotal = (int) $db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
$productosTotal   = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM productos_terminados")->fetchColumn();

/* Si todavía no has ejecutado crear_tabla_tareas.sql, esto no truena la página */
try {
    $tareasPendientes = (int) $db->query("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente'")->fetchColumn();
} catch (Exception $e) {
    $tareasPendientes = 0;
}

echo json_encode([
    'ok'                => true,
    'inventario_total'  => $inventarioTotal,
    'proveedores'       => $proveedoresTotal,
    'productos'         => $productosTotal,
    'tareas_pendientes' => $tareasPendientes,
]);
