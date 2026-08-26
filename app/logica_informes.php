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
     * Busca en la base de datos cuáles meses realmente tienen movimientos
     */
    public function obtenerMesesDisponibles(?int $anio = null): array
    {
        $anio = $anio ?? (int) date('Y');

        $placeholders = implode(',', array_fill(0, count($this->modulosMateriaPrima), '?'));

        $sql = "
            SELECT DISTINCT MONTH(fecha_hora) as mes
            FROM historial_movimientos
            WHERE modulo IN ($placeholders)
              AND YEAR(fecha_hora) = ?
            ORDER BY mes ASC
        ";

        $params = array_merge($this->modulosMateriaPrima, [$anio]);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        $meses = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $meses[] = (int) $fila['mes'];
        }
        
        return $meses;
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
}