<?php
require_once __DIR__ . '/../config/conexion.php';

class InformeLogica
{
    private $conn;

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    // ── Lista todas las materias primas ──────────────────────
    public function getMateriasPrimas(): array
    {
        $sql = "
            SELECT
                mp.id_material AS id,
                mp.nombre_material AS nombre,
                mp.stock_actual AS stock,
                mp.stock_minimo,
                IFNULL(um.nombre_unidad, 'N/A') AS unidad,
                IFNULL(p.nombre_empresa, 'Sin Proveedor') AS categoria
            FROM materias_primas mp
            LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
            LEFT JOIN proveedores p ON mp.id_proveedor = p.id_proveedor
            ORDER BY mp.nombre_material
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($resultados as &$fila) {
            if ($fila['stock'] <= 0) {
                $fila['estado'] = 'Agotado';
            } elseif ($fila['stock'] <= $fila['stock_minimo']) {
                $fila['estado'] = 'Limitado';
            } else {
                $fila['estado'] = 'Disponible';
            }
        }

        return $resultados;
    }

    // ── Movimientos filtrados por rango de meses ─────────────
    public function getMovimientos(int $mesInicio, int $mesFin, int $anio): array
    {
        $sql = "SELECT
                    m.id_movimiento AS id,
                    mp.nombre_material AS materia_prima,
                    IFNULL(p.nombre_empresa, 'Sin Proveedor') AS categoria,
                    IFNULL(um.nombre_unidad, 'N/A') AS unidad,
                    LOWER(m.tipo_movimiento) AS tipo,
                    m.cantidad,
                    'Movimiento' AS descripcion,
                    m.fecha_movimiento AS fecha,
                    MONTH(m.fecha_movimiento) AS mes_num,
                    MONTHNAME(m.fecha_movimiento) AS mes_nombre
                FROM movimientos_inventario m
                INNER JOIN materias_primas mp ON mp.id_material = m.id_material
                LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
                LEFT JOIN proveedores p ON mp.id_proveedor = p.id_proveedor
                WHERE YEAR(m.fecha_movimiento) = :anio
                  AND MONTH(m.fecha_movimiento) >= :mes_inicio
                  AND MONTH(m.fecha_movimiento) <= :mes_fin
                ORDER BY m.fecha_movimiento ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':anio',       $anio,      PDO::PARAM_INT);
        $stmt->bindParam(':mes_inicio', $mesInicio, PDO::PARAM_INT);
        $stmt->bindParam(':mes_fin',    $mesFin,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Comparativo: totales de UN mes específico por materia prima ──
    public function getTotalesPorMes(int $mes, int $anio): array
    {
        $sql = "SELECT
                    mp.id_material AS id,
                    mp.nombre_material AS materia_prima,
                    IFNULL(p.nombre_empresa, 'Sin Proveedor') AS categoria,
                    IFNULL(um.nombre_unidad, 'N/A') AS unidad,
                    SUM(CASE WHEN m.tipo_movimiento = 'ENTRADA' THEN m.cantidad ELSE 0 END) AS entradas,
                    SUM(CASE WHEN m.tipo_movimiento = 'SALIDA'  THEN m.cantidad ELSE 0 END) AS salidas
                FROM materias_primas mp
                LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
                LEFT JOIN proveedores p ON mp.id_proveedor = p.id_proveedor
                LEFT JOIN movimientos_inventario m
                       ON mp.id_material = m.id_material
                      AND YEAR(m.fecha_movimiento) = :anio
                      AND MONTH(m.fecha_movimiento) = :mes
                GROUP BY mp.id_material, mp.nombre_material, p.nombre_empresa, um.nombre_unidad
                ORDER BY p.nombre_empresa, mp.nombre_material";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
        $stmt->bindParam(':mes',  $mes,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Comparativo completo: mes A vs mes B, combinado por materia prima ──
    public function getComparativo(int $mesA, int $anioA, int $mesB, int $anioB): array
    {
        $datosA = $this->getTotalesPorMes($mesA, $anioA);
        $datosB = $this->getTotalesPorMes($mesB, $anioB);

        $indexB = [];
        foreach ($datosB as $fila) {
            $indexB[$fila['id']] = $fila;
        }

        $comparativo = [];
        foreach ($datosA as $filaA) {
            $filaB = $indexB[$filaA['id']] ?? ['entradas' => 0, 'salidas' => 0];

            $entradasA = (float) $filaA['entradas'];
            $salidasA  = (float) $filaA['salidas'];
            $entradasB = (float) $filaB['entradas'];
            $salidasB  = (float) $filaB['salidas'];

            $comparativo[] = [
                'materia_prima'    => $filaA['materia_prima'],
                'categoria'        => $filaA['categoria'],
                'unidad'           => $filaA['unidad'],
                'entradas_mes_a'   => $entradasA,
                'salidas_mes_a'    => $salidasA,
                'entradas_mes_b'   => $entradasB,
                'salidas_mes_b'    => $salidasB,
                'diferencia_entradas' => $entradasB - $entradasA,
                'diferencia_salidas'  => $salidasB - $salidasA,
            ];
        }

        return $comparativo;
    }

    public function getResumen(int $mesInicio, int $mesFin, int $anio): array
    {
        return [];
    }

    public function getComparacionMeses(int $mesA, int $mesB, int $anio): array
    {
        return [];
    }
}
