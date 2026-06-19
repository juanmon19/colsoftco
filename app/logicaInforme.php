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
            mp.id_material,
            mp.nombre_material,
            mp.stock_actual,
            mp.stock_minimo,
            um.nombre_unidad,
            p.nombre_empresa
        FROM materias_primas mp
        LEFT JOIN unidades_medida um
            ON mp.id_unidad = um.id_unidad
        LEFT JOIN proveedores p
            ON mp.id_proveedor = p.id_proveedor
        ORDER BY mp.id_material
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

       
    // ── Movimientos filtrados por rango de meses ─────────────
    public function getMovimientos(int $mesInicio, int $mesFin, int $anio): array
    {
        $sql = "SELECT
                    m.id,
                    mp.nombre       AS materia_prima,
                    mp.categoria,
                    mp.unidad,
                    m.tipo,
                    m.cantidad,
                    m.descripcion,
                    m.fecha,
                    MONTH(m.fecha)  AS mes_num,
                    MONTHNAME(m.fecha) AS mes_nombre
                FROM movimientos_inventario m
                INNER JOIN materias_primas mp ON mp.id = m.materia_prima_id
                WHERE YEAR(m.fecha)  = :anio
                  AND MONTH(m.fecha) >= :mes_inicio
                  AND MONTH(m.fecha) <= :mes_fin
                ORDER BY m.fecha ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':anio',       $anio,      PDO::PARAM_INT);
        $stmt->bindParam(':mes_inicio', $mesInicio, PDO::PARAM_INT);
        $stmt->bindParam(':mes_fin',    $mesFin,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Comparativo: totales de UN mes específico por materia prima ──
    // Se usa dos veces (mes A y mes B) para construir la comparación
    public function getTotalesPorMes(int $mes, int $anio): array
    {
        $sql = "SELECT
                    mp.id,
                    mp.nombre       AS materia_prima,
                    mp.categoria,
                    mp.unidad,
                    SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE 0 END) AS entradas,
                    SUM(CASE WHEN m.tipo = 'salida'  THEN m.cantidad ELSE 0 END) AS salidas
                FROM materias_primas mp
                LEFT JOIN movimientos_inventario m
                       ON mp.id = m.materia_prima_id
                      AND YEAR(m.fecha)  = :anio
                      AND MONTH(m.fecha) = :mes
                GROUP BY mp.id, mp.nombre, mp.categoria, mp.unidad
                ORDER BY mp.categoria, mp.nombre";

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

        // Indexar mes B por id de materia prima para cruce rápido
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

    // ── Resumen: total entradas/salidas por materia prima ────
    public function getResumen(int $mesInicio, int $mesFin, int $anio): array
    {
        $sql = "SELECT
                    mp.nombre       AS materia_prima,
                    mp.categoria,
                    mp.unidad,
                    mp.stock        AS stock_actual,
                    mp.estado,
                    SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE 0 END) AS total_entradas,
                    SUM(CASE WHEN m.tipo = 'salida'  THEN m.cantidad ELSE 0 END) AS total_salidas
                FROM materias_primas mp
                LEFT JOIN movimientos_inventario m
                       ON mp.id = m.materia_prima_id
                      AND YEAR(m.fecha)  = :anio
                      AND MONTH(m.fecha) >= :mes_inicio
                      AND MONTH(m.fecha) <= :mes_fin
                GROUP BY mp.id, mp.nombre, mp.categoria, mp.unidad, mp.stock, mp.estado
                ORDER BY mp.categoria, mp.nombre";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':anio',       $anio,      PDO::PARAM_INT);
        $stmt->bindParam(':mes_inicio', $mesInicio, PDO::PARAM_INT);
        $stmt->bindParam(':mes_fin',    $mesFin,    PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Comparación directa: mes A vs mes B por materia prima ─
    // Devuelve, para cada materia prima, el total movido (entradas - salidas)
    // en el mes inicial y en el mes final, más la diferencia y el % de variación.
    public function getComparacionMeses(int $mesA, int $mesB, int $anio): array
    {
        $sql = "SELECT
                    mp.id,
                    mp.nombre     AS materia_prima,
                    mp.categoria,
                    mp.unidad,
                    MONTH(m.fecha) AS mes_num,
                    SUM(CASE WHEN m.tipo = 'entrada' THEN m.cantidad ELSE -m.cantidad END) AS neto
                FROM materias_primas mp
                INNER JOIN movimientos_inventario m ON mp.id = m.materia_prima_id
                WHERE YEAR(m.fecha) = :anio
                  AND MONTH(m.fecha) IN (:mes_a, :mes_b)
                GROUP BY mp.id, mp.nombre, mp.categoria, mp.unidad, MONTH(m.fecha)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':anio',   $anio, PDO::PARAM_INT);
        $stmt->bindParam(':mes_a',  $mesA, PDO::PARAM_INT);
        $stmt->bindParam(':mes_b',  $mesB, PDO::PARAM_INT);
        $stmt->execute();
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Reorganizar: una fila por materia prima, con columna mes_a y mes_b
        $porMateria = [];
        foreach ($filas as $f) {
            $id = $f['id'];
            if (!isset($porMateria[$id])) {
                $porMateria[$id] = [
                    'materia_prima' => $f['materia_prima'],
                    'categoria'     => $f['categoria'],
                    'unidad'        => $f['unidad'],
                    'mes_a_total'   => 0,
                    'mes_b_total'   => 0,
                ];
            }
            if ((int)$f['mes_num'] === $mesA) {
                $porMateria[$id]['mes_a_total'] = (float)$f['neto'];
            }
            if ((int)$f['mes_num'] === $mesB) {
                $porMateria[$id]['mes_b_total'] = (float)$f['neto'];
            }
        }

        // Calcular diferencia y % de variación
        $resultado = [];
        foreach ($porMateria as $r) {
            $diferencia = $r['mes_b_total'] - $r['mes_a_total'];
            $variacion  = $r['mes_a_total'] != 0
                ? round(($diferencia / abs($r['mes_a_total'])) * 100, 1)
                : ($r['mes_b_total'] != 0 ? 100.0 : 0.0);

            $resultado[] = [
                'materia_prima' => $r['materia_prima'],
                'categoria'     => $r['categoria'],
                'unidad'        => $r['unidad'],
                'mes_a_total'   => $r['mes_a_total'],
                'mes_b_total'   => $r['mes_b_total'],
                'diferencia'    => $diferencia,
                'variacion_pct' => $variacion,
            ];
        }

        // Ordenar por categoría y nombre
        usort(
            $resultado,
            fn($a, $b) =>
            $a['categoria'] === $b['categoria']
                ? strcmp($a['materia_prima'], $b['materia_prima'])
                : strcmp($a['categoria'], $b['categoria'])
        );

        return $resultado;
    }
}
