<?php
header('Content-Type: application/json');
session_start();
require_once '../config/conexion.php';
require_once __DIR__ . '/logica_inventario.php';
require_once __DIR__ . '/HistorialMovimientos.php';

if (!isset($_SESSION['documento'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$logica = new InventarioLogica();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($accion === 'cambiar_estado') {
    $id = (int) ($_POST['id'] ?? 0);
    $estado = $_POST['estado'] ?? '';
    
    if ($id <= 0 || !in_array($estado, ['activo', 'inactivo'], true)) {
        echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos.']);
        exit();
    }
    
    $ok = $logica->cambiarEstadoMaterial($id, $estado);
    
    if ($ok) {
        $usuarioNombre = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';
        (new HistorialMovimientos())->registrar([
            'modulo' => 'materia_prima',
            'accion' => $estado === 'activo' ? 'habilitar' : 'deshabilitar',
            'id_registro' => $id,
            'descripcion' => "Se cambió el estado de la materia prima ID {$id} a '{$estado}'",
            'datos_nuevos' => ['estado' => $estado],
            'usuario_nombre' => $usuarioNombre,
        ]);
    }
    
    echo json_encode(['ok' => $ok]);
    exit();
}

echo json_encode(['ok' => false, 'error' => 'Acción no reconocida.']);
