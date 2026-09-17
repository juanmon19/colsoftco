<?php
/**
 * Partial: Sidebar de navegación (COLSOFTCO)
 *
 * Cómo incluirlo desde cualquier vista en view/<modulo>/<archivo>.php:
 *   <?php include __DIR__ . '/../partials/sidebar.php'; ?>
 *
 * Desde subniveles (view/<modulo>/<sub>/<archivo>.php):
 *   <?php include __DIR__ . '/../../partials/sidebar.php'; ?>
 *   (las rutas se auto-ajustan con $prefijo, no hay que hacer nada más)
 *
 * $prefijo se calcula solo según la profundidad de la vista:
 * view/<modulo>/ -> '' ; view/<modulo>/<sub>/ -> '../', etc.
 */
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
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../historial_movimientos/historial.php'">Historial de Movimientos</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../generar_informe/generar_informe.php'">Generar Informe</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../registromp/registromp.php'">Registrar Materia Prima</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../control_de_stock/control_de_stock.php'">Control de Stock</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../Receta_de_colchones/receta_colchones.php'">Receta de Colchones</button>
        <button class="nav-item" onclick="window.location.href='<?= $prefijo ?>../mensajeria/mensajeria.php'">📨 Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span></button>
    </nav>

   
</aside>