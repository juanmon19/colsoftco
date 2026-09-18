<?php
/**
 * Matriz central de permisos por rol — COLSOFTCO
 * ============================================================
 * El administrador SIEMPRE tiene acceso a todo (bypass automático).
 * Para cada vista (basename) se listan los roles NO-admin permitidos.
 * Lo no listado queda ABIERTO para cualquier rol logueado.
 *
 * Reglas de negocio (2026):
 * - Bodeguero y Operario: sin registro de usuarios, sin generar informes,
 *   sin historial de movimientos, proveedores en solo lectura.
 * - Bodeguero: control de stock total, receta oculta.
 * - Operario: control de stock oculto, receta solo lectura.
 * ============================================================
 */

if (!defined('PERMISOS_MODULOS')) {
    define('PERMISOS_MODULOS', [
        // ---- Paneles (cada rol solo el suyo; admin entra a todos por bypass) ----
        'panel_admin.php'      => [],
        'panel_bodeguero.php'  => ['bodeguero'],
        'panel_operario.php'   => ['operario'],
        'gestion_usuarios.php' => [],

        // ---- Usuarios: solo admin ----
        'registro.php'         => [],

        // ---- Informes y trazabilidad: solo admin ----
        'index.php'                  => [], // view/estadisticas/index.php
        'generar_informe.php'        => [],
        'generar_informe_excel.php'  => [],
        'generar_informe_pdf.php'    => [],
        'generar_auditoria.php'      => [],
        'obtener_comparativo.php'    => [],
        'historial.php'              => [],
        'historial_informe_pdf.php'  => [],

        // ---- Recetas ----
        // Ver recetas: operario (solo lectura) + admin. Bodeguero bloqueado.
        'receta_colchones.php' => ['operario'],
        // Crear/editar recetas: solo admin.
        'registrar_modelo.php' => [],
        'editar_receta.php'    => [],

        // ---- Proveedores: ver lista + solicitar (todos), gestionar solo admin ----
        'lista_proveedores.php'       => ['bodeguero', 'operario'],
        'contactar_proveedor.php'     => ['bodeguero', 'operario'],
        'registro_proveedores.php'    => [],
        'editar_proveedor.php'        => [],
        'cambiar_estado_proveedor.php' => [],
        'ambiar_estado_proveedor.php' => [], // compat nombre antiguo con typo

        // ---- Inventarios y stock ----
        // Control de stock: solo bodeguero (+admin). Operario bloqueado.
        // Inventarios de consulta y registro de MP: bodeguero + operario
        // (el operario los ve en solo lectura: sin editar, deshabilitar ni eliminar).
        'control_de_stock.php'              => ['bodeguero'],
        'registromp.php'                    => ['bodeguero', 'operario'],
        'registro_producto_terminado.php'   => ['bodeguero'],
        'inventario_materia_prima.php'      => ['bodeguero', 'operario'],
        'inventario_productos_terminados.php' => ['bodeguero', 'operario'],
        'lista_inventario.php'              => ['bodeguero'],
        'editar_inventario.php'             => ['bodeguero'],
        'eliminar_inventario.php'           => ['bodeguero'],
        'registrar_stock.php'               => ['bodeguero'],
        'lista_alertas.php'                 => ['bodeguero'],
        'editar_alerta.php'                 => ['bodeguero'],
        'eliminar_alerta.php'               => ['bodeguero'],

        // ---- Producción ----
        'historial_fabricacion.php' => ['bodeguero', 'operario'],
        'ver_recibo.php'            => ['bodeguero', 'operario'],

        // ---- Transversales ----
        'tareas.php'     => ['bodeguero', 'operario'], // módulo de tareas (Kanban + calendario)
        'mensajeria.php' => ['bodeguero', 'operario'],
        'cambio_contrasena.php' => ['bodeguero', 'operario'],
        'recuperar_contrasena.php' => ['bodeguero', 'operario'],
    ]);
}

if (!function_exists('rol_actual')) {
    function rol_actual(): string {
        return strtolower(trim($_SESSION['rol'] ?? ''));
    }
}
if (!function_exists('es_admin')) {
    function es_admin(?string $rol = null): bool {
        return ($rol ?? rol_actual()) === 'administrador';
    }
}
if (!function_exists('es_bodeguero')) {
    function es_bodeguero(?string $rol = null): bool {
        return ($rol ?? rol_actual()) === 'bodeguero';
    }
}
if (!function_exists('es_operario')) {
    function es_operario(?string $rol = null): bool {
        return ($rol ?? rol_actual()) === 'operario';
    }
}
if (!function_exists('rol_legible')) {
    function rol_legible(?string $rol = null): string {
        $r = $rol ?? rol_actual();
        return match ($r) {
            'administrador' => 'Administrador',
            'bodeguero' => 'Bodeguero',
            'operario' => 'Operario',
            default => $r !== '' ? ucfirst($r) : 'Usuario',
        };
    }
}

/**
 * ¿Puede $rol abrir la vista $archivo? Acepta basename o ruta relativa
 * a view/. Admin siempre sí.
 */
if (!function_exists('puede_acceder')) {
    function puede_acceder(string $archivo, string $rol): bool {
        $rol = strtolower(trim($rol));
        if ($rol === 'administrador') {
            return true;
        }
        if (isset(PERMISOS_MODULOS[$archivo])) {
            return in_array($rol, PERMISOS_MODULOS[$archivo], true);
        }
        $base = basename($archivo);
        if (isset(PERMISOS_MODULOS[$base])) {
            return in_array($rol, PERMISOS_MODULOS[$base], true);
        }
        return true; // fuera del mapa = abierto
    }
}

/**
 * Bloquea la vista actual si el rol no tiene permiso (redirige a su panel).
 */
if (!function_exists('requerir_permiso_modulo')) {
    function requerir_permiso_modulo(?string $archivo = null): void {
        if ($archivo === null) {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $archivo = ($pos = strpos($script, '/view/')) !== false
                ? substr($script, $pos + 6)
                : basename($script);
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $rol = rol_actual();
        if (!puede_acceder($archivo, $rol)) {
            header("Location: /colsoftco/app/ir_panel.php?denegado=1");
            exit();
        }
    }
}
