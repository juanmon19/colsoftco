<?php

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/conexion.php';
require_once __DIR__ . '/permisos.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$conexion = new Conexion();
$db = $conexion->getConnection();

$inventarioTotal  = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM materias_primas")->fetchColumn();
$proveedoresTotal = (int) $db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
$productosTotal   = (float) $db->query("SELECT COALESCE(SUM(stock_actual), 0) FROM productos_terminados")->fetchColumn();

/* Tareas pendientes: cada usuario ve las SUYAS; el admin ve el total. */
try {
    if (es_admin()) {
        $tareasPendientes = (int) $db->query("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente'")->fetchColumn();
    } else {
        $me = (int) ($_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? 0);
        $stmt = $db->prepare("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente' AND id_usuario = :me");
        $stmt->execute([':me' => $me]);
        $tareasPendientes = (int) $stmt->fetchColumn();
    }
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
