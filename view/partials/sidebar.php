<?php
/**
 * Partial: Sidebar de navegación (COLSOFTCO)
 *
 * Cómo incluirlo desde cualquier vista en view/<modulo>/<archivo>.php:
 *   <?php include __DIR__ . '/../partials/sidebar.php'; ?>
 *
 * Funciona sin cambios porque todos los módulos viven al mismo nivel
 * de profundidad: view/<modulo>/archivo.php  ->  view/partials/sidebar.php
 */
?>
<div class="menu-overlay" id="menuOverlay"></div>

<aside class="sidebar" id="sidebar">
    <a class="brand" href="../../app/ir_panel.php" style="text-decoration:none;">
        <img src="../../public/imagenes/logo.png" alt="COLSOFTCO">
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
        <button class="nav-item" onclick="window.location.href='../../app/ir_panel.php'">🏠 Panel Principal</button>
        <button class="nav-item" onclick="window.location.href='../lista_proveedores/lista_proveedores.php'">Lista de Proveedores</button>
        <button class="nav-item" onclick="window.location.href='../historial_movimientos/historial.php'">Historial de Movimientos</button>
        <button class="nav-item" onclick="window.location.href='../generar_informe/generar_informe.php'">Generar Informe</button>
        <button class="nav-item" onclick="window.location.href='../registromp/registromp.php'">Registrar Materia Prima</button>
        <button class="nav-item" onclick="window.location.href='../control_de_stock/control_de_stock.php'">Control de Stock</button>
        <button class="nav-item" onclick="window.location.href='../inventario_materia_prima/inventario_materia_prima.php'">Inventario de Materia Prima</button>
        <button class="nav-item" onclick="window.location.href='../inventario_productos_terminados/inventario_productos_terminados.php'">Inventario de Productos</button>
        <button class="nav-item" onclick="window.location.href='../registro_de_producto_terminado/registro_producto_terminado.php'">Registrar Producto Terminado</button>
        <button class="nav-item" onclick="window.location.href='../Receta_de_colchones/receta_colchones.php'">Receta de Colchones</button>
        <button class="nav-item" onclick="window.location.href='../mensajeria/mensajeria.php'">📨 Mensajes <span id="badgeMensajesNoLeidos" style="display:none;"></span></button>
    </nav>

    <div class="help-box">
        <div class="headset">♧</div>
        <div>
            <strong>¿Necesitas ayuda?</strong>
            <p>Nuestro equipo está<br>para apoyarte.</p>
            <a href="mailto:juanjosemon19@gmail.com">Contáctanos</a>
        </div>
    </div>
</aside>