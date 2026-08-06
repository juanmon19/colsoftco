<?php
/**
 * Clase HistorialMovimientos
 * Registra y consulta el historial de movimientos de todos los
 * módulos de COLSOFTCO. Usa la clase Conexion ya existente.
 */

require_once __DIR__ . '/../config/conexion.php';

class HistorialMovimientos
{
    private PDO $conn;
    private Conexion $conexionObj;

    public function __construct()
    {
        $this->conexionObj = new Conexion();
        $this->conn = $this->conexionObj->getConnection();
    }

    public function registrar(array $datos): bool
    {
        try {
            $sql = "INSERT INTO historial_movimientos
                        (modulo, accion, id_registro, descripcion,
                         datos_anteriores, datos_nuevos,
                         usuario_id, usuario_nombre, ip, fecha_hora)
                    VALUES
                        (:modulo, :accion, :id_registro, :descripcion,
                         :datos_anteriores, :datos_nuevos,
                         :usuario_id, :usuario_nombre, :ip, NOW())";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([
                ':modulo'           => $datos['modulo'],
                ':accion'           => $datos['accion'],
                ':id_registro'      => $datos['id_registro'] ?? null,
                ':descripcion'      => $datos['descripcion'],
                ':datos_anteriores' => isset($datos['datos_anteriores'])
                    ? json_encode($datos['datos_anteriores'], JSON_UNESCAPED_UNICODE)
                    : null,
                ':datos_nuevos'     => isset($datos['datos_nuevos'])
                    ? json_encode($datos['datos_nuevos'], JSON_UNESCAPED_UNICODE)
                    : null,
                ':usuario_id'       => $datos['usuario_id'] ?? null,
                ':usuario_nombre'   => $datos['usuario_nombre'] ?? ($_SESSION['nombre'] ?? 'Sistema'),
                ':ip'               => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Error registrando historial: ' . $e->getMessage());
            return false;
        }
    }

    public function obtener(array $filtros = [], int $pagina = 1, int $porPagina = 20): array
    {
        [$where, $params] = $this->construirFiltros($filtros);
        $offset = max(0, ($pagina - 1) * $porPagina);

        $sql = "SELECT * FROM historial_movimientos
                {$where}
                ORDER BY fecha_hora DESC
                LIMIT :limite OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarRegistros(array $filtros = []): int
    {
        [$where, $params] = $this->construirFiltros($filtros);

        $sql = "SELECT COUNT(*) AS total FROM historial_movimientos {$where}";
        $stmt = $this->conn->prepare($sql);
        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();

        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function obtenerModulos(): array
    {
        $stmt = $this->conn->query("SELECT DISTINCT modulo FROM historial_movimientos ORDER BY modulo ASC");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'modulo');
    }

    public function obtenerAcciones(): array
    {
        $stmt = $this->conn->query("SELECT DISTINCT accion FROM historial_movimientos ORDER BY accion ASC");
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'accion');
    }

    private function construirFiltros(array $filtros): array
    {
        $condiciones = [];
        $params = [];

        if (!empty($filtros['modulo'])) {
            $condiciones[] = "modulo = :modulo";
            $params[':modulo'] = $filtros['modulo'];
        }
        if (!empty($filtros['accion'])) {
            $condiciones[] = "accion = :accion";
            $params[':accion'] = $filtros['accion'];
        }
        if (!empty($filtros['usuario'])) {
            $condiciones[] = "usuario_nombre LIKE :usuario";
            $params[':usuario'] = '%' . $filtros['usuario'] . '%';
        }
        if (!empty($filtros['fecha_inicio'])) {
            $condiciones[] = "fecha_hora >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'] . ' 00:00:00';
        }
        if (!empty($filtros['fecha_fin'])) {
            $condiciones[] = "fecha_hora <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'] . ' 23:59:59';
        }
        if (!empty($filtros['buscar'])) {
            $condiciones[] = "descripcion LIKE :buscar";
            $params[':buscar'] = '%' . $filtros['buscar'] . '%';
        }

        $where = count($condiciones) > 0 ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        return [$where, $params];
    }
}