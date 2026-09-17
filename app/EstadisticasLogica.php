<?php
/**
 * EstadisticasLogica — agregaciones del módulo view/estadisticas/.
 * Patrón: misma estructura que InventarioLogica / ProveedorLogica
 * (PDO vía Conexion, métodos públicos por rango + rendimiento).
 *
 * Rangos:
 *  - Día:    fecha exacta (00:00:00–23:59:59).
 *  - Semana: lunes–domingo ISO que contiene la fecha de referencia.
 *  - Mes:    mes calendario (anio, mes).
 * El "periodo anterior equivalente" es el bloque anterior de igual
 * duración (día previo, 7 días previos, mes previo).
 *
 * NOTA tareas.fecha_cierre: columna añadida por
 * view/estadisticas/migracion.sql y alimentada desde
 * app/logica_tareas.php (accion=actualizar). Las tareas terminadas
 * ANTES de esa migración tienen fecha_cierre NULL y se excluyen
 * de los promedios de tiempo (se reportan como base_sin_fecha).
 */
require_once __DIR__ . '/../config/conexion.php';

class EstadisticasLogica
{
    private PDO $conn;

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    /* ═══════════════ RANGOS ═══════════════ */

    /** Día exacto. $fecha: 'Y-m-d' (por defecto hoy). */
    public function rangoDia(string $fecha = ''): array
    {
        $d = $this->validarFecha($fecha) ?? date('Y-m-d');
        return [
            'etiqueta' => $d,
            'inicio'   => "$d 00:00:00",
            'fin'      => "$d 23:59:59",
            'prev_inicio' => date('Y-m-d 00:00:00', strtotime("$d -1 day")),
            'prev_fin'    => date('Y-m-d 23:59:59', strtotime("$d -1 day")),
        ];
    }

    /** Semana ISO (lunes–domingo) que contiene $fecha. */
    public function rangoSemana(string $fecha = ''): array
    {
        $d = $this->validarFecha($fecha) ?? date('Y-m-d');
        $lunes = date('Y-m-d', strtotime("$d monday this week"));
        // strtotime('monday this week') el domingo devuelve el lunes SIGUIENTE.
        if (strtotime($lunes) > strtotime($d)) {
            $lunes = date('Y-m-d', strtotime("$d last monday"));
        }
        $domingo = date('Y-m-d', strtotime("$lunes +6 days"));
        return [
            'etiqueta' => "$lunes / $domingo",
            'inicio'   => "$lunes 00:00:00",
            'fin'      => "$domingo 23:59:59",
            'prev_inicio' => date('Y-m-d 00:00:00', strtotime("$lunes -7 days")),
            'prev_fin'    => date('Y-m-d 23:59:59', strtotime("$lunes -1 day")),
        ];
    }

    /** Mes calendario. */
    public function rangoMes(?int $anio = null, ?int $mes = null): array
    {
        $anio = $anio ?: (int) date('Y');
        $mes  = $mes ? max(1, min(12, $mes)) : (int) date('n');
        $inicio = sprintf('%04d-%02d-01 00:00:00', $anio, $mes);
        $fin    = date('Y-m-t 23:59:59', strtotime($inicio));
        $prev   = strtotime("$inicio -1 month");
        return [
            'etiqueta' => sprintf('%04d-%02d', $anio, $mes),
            'inicio'   => $inicio,
            'fin'      => $fin,
            'prev_inicio' => date('Y-m-01 00:00:00', $prev),
            'prev_fin'    => date('Y-m-t 23:59:59', $prev),
        ];
    }

    /* ═══════════════ RESÚMENES POR RANGO ═══════════════ */

    public function obtenerPorDia(string $fecha = ''): array
    {
        $r = $this->rangoDia($fecha);
        return $this->resumenConComparativa($r);
    }

    public function obtenerPorSemana(string $fecha = ''): array
    {
        $r = $this->rangoSemana($fecha);
        return $this->resumenConComparativa($r);
    }

    public function obtenerPorMes(?int $anio = null, ?int $mes = null): array
    {
        $r = $this->rangoMes($anio, $mes);
        return $this->resumenConComparativa($r);
    }

    /* ═══════════════ RENDIMIENTO ═══════════════ */

    /**
     * Tres indicadores independientes para el mismo periodo:
     *  - pct_a_tiempo: % terminadas con cierre <= vencimiento.
     *  - por_operario / por_turno: colchones fabricados.
     *  - tiempo_promedio_cierre_horas: AVG(fecha_cierre - fecha_creacion).
     */
    public function calcularRendimiento(string $inicio, string $fin): array
    {
        return [
            'a_tiempo'  => $this->tareasATiempo($inicio, $fin),
            'operario'  => $this->colchonesPorOperario($inicio, $fin),
            'turno'     => $this->colchonesPorTurno($inicio, $fin),
            'cierre'    => $this->tiempoPromedioCierre($inicio, $fin),
        ];
    }

    /* ═══════════════ PRIVADOS ═══════════════ */

    private function resumenConComparativa(array $r): array
    {
        $actual = $this->resumen($r['inicio'], $r['fin']);
        $previo = $this->resumen($r['prev_inicio'], $r['prev_fin']);
        return [
            'periodo' => ['etiqueta' => $r['etiqueta'], 'inicio' => $r['inicio'], 'fin' => $r['fin']],
            'actual'  => $actual,
            'previo'  => $previo,
            'variacion' => [
                'produccion'   => $this->pctVar($previo['produccion']['total'], $actual['produccion']['total']),
                'cumplimiento' => round($actual['tareas']['pct_cumplimiento'] - $previo['tareas']['pct_cumplimiento'], 1),
            ],
        ];
    }

    private function resumen(string $inicio, string $fin): array
    {
        return [
            'produccion' => $this->produccion($inicio, $fin),
            'tareas'     => $this->tareas($inicio, $fin),
            'consumo'    => $this->consumoTop($inicio, $fin, 5),
        ];
    }

    /** SQL: producción total + desglose por modelo. */
    private function produccion(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(cantidad), 0) AS total,
                    COUNT(*) AS registros
             FROM historial_produccion
             WHERE fecha_fabricacion BETWEEN :ini AND :fin"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        $tot = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->conn->prepare(
            "SELECT m.nombre_modelo AS modelo, SUM(h.cantidad) AS cantidad
             FROM historial_produccion h
             LEFT JOIN modelos_colchon m ON m.id_modelo = h.id_modelo
             WHERE h.fecha_fabricacion BETWEEN :ini AND :fin
             GROUP BY h.id_modelo, m.nombre_modelo
             ORDER BY cantidad DESC"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);

        return [
            'total'      => (int) $tot['total'],
            'registros'  => (int) $tot['registros'],
            'por_modelo' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    /** SQL: tareas creadas en el periodo + % cumplimiento + vencidas. */
    private function tareas(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(estado = 'terminado') AS terminadas,
                    SUM(estado IN ('pendiente','por-hacer')) AS pendientes,
                    SUM(estado != 'terminado'
                        AND fecha_vencimiento IS NOT NULL
                        AND fecha_vencimiento < CURDATE()) AS vencidas
             FROM tareas
             WHERE fecha_creacion BETWEEN :ini AND :fin"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int) $t['total'];
        $term  = (int) $t['terminadas'];
        return [
            'total'      => $total,
            'terminadas' => $term,
            'pendientes' => (int) $t['pendientes'],
            'vencidas'   => (int) $t['vencidas'],
            'pct_cumplimiento' => $total > 0 ? round($term * 100 / $total, 1) : 0.0,
        ];
    }

    /** SQL: materias primas más consumidas (producción × receta). */
    private function consumoTop(string $inicio, string $fin, int $limite = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT mp.nombre_material AS material,
                    ROUND(SUM(h.cantidad * r.cantidad_requerida), 2) AS consumo
             FROM historial_produccion h
             INNER JOIN receta_colchon r ON r.id_modelo = h.id_modelo
             INNER JOIN materias_primas mp ON mp.id_material = r.id_material
             WHERE h.fecha_fabricacion BETWEEN :ini AND :fin
             GROUP BY mp.id_material, mp.nombre_material
             ORDER BY consumo DESC
             LIMIT " . (int) $limite
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** SQL: % terminadas a tiempo (cierre <= vencimiento). */
    private function tareasATiempo(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS terminadas,
                    SUM(fecha_cierre IS NOT NULL
                        AND (fecha_vencimiento IS NULL
                             OR DATE(fecha_cierre) <= fecha_vencimiento)) AS a_tiempo,
                    SUM(fecha_cierre IS NULL) AS sin_fecha
             FROM tareas
             WHERE estado = 'terminado'
               AND fecha_creacion BETWEEN :ini AND :fin"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        $term = (int) $t['terminadas'];
        $base = $term - (int) $t['sin_fecha'];
        return [
            'terminadas' => $term,
            'a_tiempo'   => (int) $t['a_tiempo'],
            'sin_fecha'  => (int) $t['sin_fecha'],
            'pct' => $base > 0 ? round((int) $t['a_tiempo'] * 100 / $base, 1) : 0.0,
        ];
    }

    /** SQL: colchones por operario (columna usuario = nombre). */
    private function colchonesPorOperario(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT usuario AS operario, SUM(cantidad) AS colchones
             FROM historial_produccion
             WHERE fecha_fabricacion BETWEEN :ini AND :fin
             GROUP BY usuario
             ORDER BY colchones DESC"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** SQL: colchones por turno (derivado de la hora: 6–14, 14–22, resto noche). */
    private function colchonesPorTurno(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT CASE WHEN HOUR(fecha_fabricacion) >= 6 AND HOUR(fecha_fabricacion) < 14 THEN 'Mañana (6–14)'
                        WHEN HOUR(fecha_fabricacion) >= 14 AND HOUR(fecha_fabricacion) < 22 THEN 'Tarde (14–22)'
                        ELSE 'Noche (22–6)' END AS turno,
                    SUM(cantidad) AS colchones
             FROM historial_produccion
             WHERE fecha_fabricacion BETWEEN :ini AND :fin
             GROUP BY turno
             ORDER BY colchones DESC"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** SQL: horas promedio entre creación y cierre de tareas terminadas. */
    private function tiempoPromedioCierre(string $inicio, string $fin): array
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS base,
                    ROUND(AVG(TIMESTAMPDIFF(SECOND, fecha_creacion, fecha_cierre)) / 3600, 1) AS horas
             FROM tareas
             WHERE estado = 'terminado'
               AND fecha_cierre IS NOT NULL
               AND fecha_creacion BETWEEN :ini AND :fin"
        );
        $stmt->execute([':ini' => $inicio, ':fin' => $fin]);
        $t = $stmt->fetch(PDO::FETCH_ASSOC);
        return ['base' => (int) $t['base'], 'horas' => $t['horas'] !== null ? (float) $t['horas'] : null];
    }

    private function pctVar(float $previo, float $actual): ?float
    {
        if ($previo == 0) {
            return $actual > 0 ? 100.0 : 0.0;
        }
        return round(($actual - $previo) * 100 / $previo, 1);
    }

    private function validarFecha(string $fecha): ?string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            [$a, $m, $d] = array_map('intval', explode('-', $fecha));
            if (checkdate($m, $d, $a)) {
                return sprintf('%04d-%02d-%02d', $a, $m, $d);
            }
        }
        return null;
    }
}
