<?php

require_once __DIR__ . '/../config/conexion.php';

class InformeLogica
{
    private PDO $conn;

    /* ══════════════════════════════════════════════════════════════
       Debe coincidir exactamente con MODULO_HISTORIAL en
       app/logica_inventario.php
       ══════════════════════════════════════════════════════════════ */
    private array $modulosMateriaPrima = [
        'materia_prima',
    ];

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    /**
     * Meses con movimientos (cualquier módulo), como pares año-mes.
     * Devuelve [['anio'=>2026,'mes'=>8],...] ordenado. Así el comparativo
     * deja elegir cualquier mes con actividad, no solo materia_prima
     * ni solo el año actual.
     */
    public function obtenerMesesDisponibles(?int $anio = null): array
    {
        if ($anio !== null) {
            $stmt = $this->conn->prepare(
                "SELECT DISTINCT MONTH(fecha_hora) as mes
                 FROM historial_movimientos
                 WHERE YEAR(fecha_hora) = ?
                 ORDER BY mes ASC"
            );
            $stmt->execute([$anio]);
            $out = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
                $out[] = ['anio' => $anio, 'mes' => (int) $fila['mes']];
            }
            return $out;
        }

        $stmt = $this->conn->query(
            "SELECT DISTINCT YEAR(fecha_hora) as anio, MONTH(fecha_hora) as mes
             FROM historial_movimientos
             ORDER BY anio ASC, mes ASC"
        );

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $out[] = ['anio' => (int) $fila['anio'], 'mes' => (int) $fila['mes']];
        }
        return $out;
    }

    /**
     * Devuelve los movimientos calculados por cada material
     */
    public function obtenerMovimientosPorMes(int $mesInicio, int $mesFin, ?int $anio = null): array
    {
        $anio = $anio ?? (int) date('Y');

        $placeholders = implode(',', array_fill(0, count($this->modulosMateriaPrima), '?'));

        $sql = "
            SELECT id_registro, descripcion, datos_anteriores, datos_nuevos, fecha_hora
            FROM historial_movimientos
            WHERE modulo IN ($placeholders)
              AND YEAR(fecha_hora) = ?
              AND MONTH(fecha_hora) BETWEEN ? AND ?
            ORDER BY fecha_hora ASC
        ";

        $params = array_merge($this->modulosMateriaPrima, [$anio, $mesInicio, $mesFin]);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nombresMateriales = $this->obtenerNombresMateriales();
        $acumulado = [];

        foreach ($filas as $fila) {
            $mes = (int) date('n', strtotime($fila['fecha_hora']));
            $idMaterial = $fila['id_registro'];

            $anteriores = json_decode($fila['datos_anteriores'] ?? '{}', true) ?: [];
            $nuevos     = json_decode($fila['datos_nuevos'] ?? '{}', true) ?: [];

            $stockAntes = $anteriores['stock_actual'] ?? null;
            $stockDespues = $nuevos['stock_actual'] ?? null;

            if ($stockAntes === null || $stockDespues === null) {
                continue;
            }

            // SOLUCIÓN: Usamos round() con 2 decimales para matar los números raros como 0.0000000007
            $diferencia = round((float) $stockDespues - (float) $stockAntes, 2);
            
            $clave = $mes . '-' . $idMaterial;

            if (!isset($acumulado[$clave])) {
                $acumulado[$clave] = [
                    'mes'        => $mes,
                    'id_material' => $idMaterial,
                    'nombre'     => $nombresMateriales[$idMaterial] ?? ('Material #' . $idMaterial),
                    'entradas'   => 0.0,
                    'salidas'    => 0.0,
                    'stock_final' => round((float) $stockDespues, 2),
                ];
            }

            if ($diferencia > 0) {
                $acumulado[$clave]['entradas'] += $diferencia;
            } elseif ($diferencia < 0) {
                $acumulado[$clave]['salidas'] += abs($diferencia);
            }

            $acumulado[$clave]['stock_final'] = round((float) $stockDespues, 2);
        }

        $resultado = array_values($acumulado);

        // Volvemos a redondear el total final acumulado por si acaso
        foreach ($resultado as &$res) {
            $res['entradas'] = round($res['entradas'], 2);
            $res['salidas'] = round($res['salidas'], 2);
        }

        usort($resultado, function ($a, $b) {
            return $a['mes'] <=> $b['mes'] ?: strcmp($a['nombre'], $b['nombre']);
        });

        return $resultado;
    }

    private function obtenerNombresMateriales(): array
    {
        $stmt = $this->conn->query("SELECT id_material, nombre_material FROM materias_primas");
        $mapa = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $mapa[$fila['id_material']] = $fila['nombre_material'];
        }
        return $mapa;
    }

    /** Estado actual de todas las materias primas */
    public function obtenerEstadoActualMaterias(): array
    {
        $stmt = $this->conn->query(
            "SELECT nombre_material, stock_actual, stock_minimo FROM materias_primas ORDER BY nombre_material"
        );

        $materias = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            if ($fila['stock_actual'] <= 0) {
                $estado = 'Agotado';
            } elseif ($fila['stock_actual'] <= $fila['stock_minimo']) {
                $estado = 'Limitado';
            } else {
                $estado = 'Disponible';
            }

            // Redondeamos también aquí por precaución
            $materias[] = [
                'nombre'    => $fila['nombre_material'],
                'categoria' => 'Materia Prima',
                'stock'     => round((float)$fila['stock_actual'], 2),
                'estado'    => $estado,
            ];
        }

        return $materias;
    }

    /**
     * Comparativo mes A vs mes B, una fila por material.
     * Acepta años independientes (ej. dic-2025 vs ene-2026).
     * Agrupa lo que obtenerMovimientosPorMes devuelve plano (mes-material)
     * y pivota a columnas A/B + diferencias. Incluye materiales sin
     * movimiento (ceros) para que no "desaparezcan".
     */
    public function obtenerComparativo(int $mesA, int $mesB, ?int $anioA = null, ?int $anioB = null): array
    {
        $anioA = $anioA ?? (int) date('Y');
        $anioB = $anioB ?? $anioA;
        $mesA = max(1, min(12, $mesA));
        $mesB = max(1, min(12, $mesB));

        $filasA = $this->obtenerMovimientosPorMes($mesA, $mesA, $anioA);
        $filasB = ($mesA === $mesB && $anioA === $anioB)
            ? [] // comparación identidad: B queda en 0
            : $this->obtenerMovimientosPorMes($mesB, $mesB, $anioB);

        $map = [];
        $sumar = function (array $filas, string $lado) use (&$map) {
            foreach ($filas as $r) {
                $id = $r['id_material'];
                if (!isset($map[$id])) {
                    $map[$id] = [
                        'id_material' => $id,
                        'nombre' => $r['nombre'],
                        'entradas_a' => 0.0, 'salidas_a' => 0.0,
                        'entradas_b' => 0.0, 'salidas_b' => 0.0,
                    ];
                }
                $map[$id]["entradas_{$lado}"] += (float) $r['entradas'];
                $map[$id]["salidas_{$lado}"] += (float) $r['salidas'];
            }
        };
        $sumar($filasA, 'a');
        $sumar($filasB, 'b');

        // Materiales sin movimiento en el rango: aparecen con ceros.
        foreach ($this->obtenerNombresMateriales() as $id => $nombre) {
            if (!isset($map[$id])) {
                $map[$id] = [
                    'id_material' => $id,
                    'nombre' => $nombre,
                    'entradas_a' => 0.0, 'salidas_a' => 0.0,
                    'entradas_b' => 0.0, 'salidas_b' => 0.0,
                ];
            }
        }

        $out = [];
        foreach ($map as $m) {
            $eA = round($m['entradas_a'], 2); $sA = round($m['salidas_a'], 2);
            $eB = round($m['entradas_b'], 2); $sB = round($m['salidas_b'], 2);
            $out[] = [
                'id_material' => $m['id_material'],
                'nombre' => $m['nombre'],
                'entradas_a' => $eA, 'salidas_a' => $sA,
                'mov_a' => round($eA - $sA, 2),
                'entradas_b' => $eB, 'salidas_b' => $sB,
                'mov_b' => round($eB - $sB, 2),
                'dif_entradas' => round($eB - $eA, 2),
                'dif_salidas' => round($sB - $sA, 2),
                'dif_mov' => round(($eB - $sB) - ($eA - $sA), 2),
            ];
        }

        usort($out, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));
        return $out;
    }

    /**
     * "2026-8" o 8 -> [anio, mes]. $defAnio se usa si viene solo el mes (legacy).
     */
    public static function parseAnioMes(mixed $valor, int $defAnio, int $defMes): array
    {
        if (is_string($valor) && preg_match('/^(\d{4})-(\d{1,2})$/', trim($valor), $m)) {
            return [(int) $m[1], max(1, min(12, (int) $m[2]))];
        }
        return [$defAnio, max(1, min(12, (int) $valor ?: $defMes))];
    }
}