<?php
/**
 * Partial: Sidebar de navegación (COLSOFTCO) — filtrado por rol.
 * El rol siempre sale de $_SESSION, nunca de $rolActual hardcodeado.
 */
require_once __DIR__ . '/../../app/permisos.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rolKey = rol_actual();
if (!isset($prefijo)) {
    $prefijo = '';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (($pos = strpos($script, '/view/')) !== false) {
        $segs = array_values(array_filter(explode('/', dirname(substr($script, $pos + 6)))));
        if (count($segs) > 1) {
            $prefijo = str_repeat('../', count($segs) - 1);
        }
    }
}
$ver = fn(string $archivo) => puede_acceder($archivo, $rolKey ?: 'administrador');
?>
<div class="menu-overlay" id="menuOverlay"></div>

<aside class="sidebar" id="sidebar">
    <a class="brand" href="<?= $prefijo ?>../../app/ir_panel.php" style="text-decoration:none;">
        <img src="<?= $prefijo ?>../../public/imagenes/logo.png" alt="COLSOFTCO">
        <div class="brand-text">
            <strong>COLSOFTCO</strong>
            <span>Sistema de Gestión</span>
        </div>
    </a>

    <button class="mobile-menu" id="btnMenuToggle" type="button"
            aria-expanded="false" aria-controls="sidebarLinks">
        <span>☰ Menú</span>
        <span class="toggle-icono">⌄</span>
    </button>

    <nav class="nav" id="navMenu">
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../../app/ir_panel.php'">🏠 Panel Principal</button>
        <?php if ($ver('lista_proveedores.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
        <?php endif; ?>
        <?php if ($ver('historial.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../historial_movimientos/historial.php'">Historial de Movimientos</button>
        <?php endif; ?>
        <?php if ($ver('generar_informe.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../generar_informe/generar_informe.php'">Generar Informe</button>
        <?php endif; ?>
        <?php if ($ver('registromp.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../registromp/registromp.php'">Registrar Materia Prima</button>
        <?php endif; ?>
        <?php if ($ver('control_de_stock.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../control_de_stock/control_de_stock.php'">Control de Stock</button>
        <?php endif; ?>
        <?php if ($ver('inventario_materia_prima.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
        <?php endif; ?>
        <?php if ($ver('inventario_productos_terminados.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
        <?php endif; ?>
        <?php if ($ver('registro_producto_terminado.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
        <?php endif; ?>
        <?php if ($ver('receta_colchones.php')): ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../Receta_de_colchones/receta_colchones.php'">Receta de Colchones</button>
        <?php endif; ?>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../mensajeria/mensajeria.php'">📨 Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span></button>
    </nav>

</aside>
