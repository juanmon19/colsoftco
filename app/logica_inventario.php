<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../app/HistorialMovimientos.php';

class InventarioLogica
{
    private $conn;

    /* Debe coincidir con lo que lee app/logica_informes.php */
    private const MODULO_HISTORIAL = 'materia_prima';

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    public function listarMateriales($incluirInactivos = false)
    {
        $filtroEstado = $incluirInactivos ? '' : "WHERE mp.estado = 'activo'";
        $sql = "
            SELECT mp.*, um.nombre_unidad, p.nombre_empresa AS proveedor
            FROM materias_primas mp
            LEFT JOIN unidades_medida um ON um.id_unidad = mp.id_unidad
            LEFT JOIN proveedores p ON p.id_proveedor = mp.id_proveedor
            {$filtroEstado}
            ORDER BY mp.nombre_material ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMaterial($id)
    {
        $sql = "
        SELECT *
        FROM materias_primas
        WHERE id_material = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarMaterial(
        $nombre,
        $stockActual,
        $stockMinimo,
        $unidad,
        $proveedor
    )
    {
        $sql = "
        INSERT INTO materias_primas
        (
            nombre_material,
            stock_actual,
            stock_minimo,
            id_unidad,
            id_proveedor
        )
        VALUES
        (
            ?,?,?,?,?
        )
        ";

        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            $nombre,
            $stockActual,
            $stockMinimo,
            $unidad,
            $proveedor
        ]);

        if ($ok) {
            $idNuevo = $this->conn->lastInsertId();

            /* El stock inicial también cuenta como movimiento (entrada desde 0) */
            if ((float) $stockActual > 0) {
                $this->registrarMovimientoHistorial(
                    $idNuevo,
                    $nombre,
                    0,
                    $stockActual,
                    'entrada',
                    "Se registró la materia prima '{$nombre}' con stock inicial {$stockActual}"
                );
            }
        }

        return $ok;
    }

    public function actualizarMaterial(
        $id,
        $nombre,
        $stockActual,
        $stockMinimo,
        $unidad,
        $proveedor
    )
    {
        /* Necesitamos el stock ANTES de actualizar, para poder calcular el movimiento real */
        $materialAnterior = $this->obtenerMaterial($id);
        $stockAnterior = $materialAnterior['stock_actual'] ?? null;

        $sql = "
        UPDATE materias_primas
        SET
            nombre_material=?,
            stock_actual=?,
            stock_minimo=?,
            id_unidad=?,
            id_proveedor=?
        WHERE id_material=?
        ";

        $stmt = $this->conn->prepare($sql);

        $ok = $stmt->execute([
            $nombre,
            $stockActual,
            $stockMinimo,
            $unidad,
            $proveedor,
            $id
        ]);

        if ($ok && $stockAnterior !== null && (float) $stockAnterior !== (float) $stockActual) {
            $subio = (float) $stockActual > (float) $stockAnterior;
            $cantidad = abs((float) $stockActual - (float) $stockAnterior);

            $this->registrarMovimientoHistorial(
                $id,
                $nombre,
                $stockAnterior,
                $stockActual,
                $subio ? 'entrada' : 'salida',
                $subio
                    ? "Entró materia prima: '{$nombre}' +{$cantidad} (de {$stockAnterior} a {$stockActual})"
                    : "Salió materia prima: '{$nombre}' -{$cantidad} (de {$stockAnterior} a {$stockActual})"
            );
        }

        return $ok;
    }

    /**
     * Guarda el cambio de stock en historial_movimientos para que
     * app/logica_informes.php pueda calcular entradas/salidas reales por mes.
     */
    private function registrarMovimientoHistorial(
        $idMaterial,
        $nombreMaterial,
        $stockAnterior,
        $stockNuevo,
        $accion,
        $descripcion
    ) {
        $usuarioNombre = trim(
            ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')
        ) ?: 'Sistema';

        (new HistorialMovimientos())->registrar([
            'modulo'           => self::MODULO_HISTORIAL,
            'accion'           => $accion,
            'id_registro'      => $idMaterial,
            'descripcion'      => $descripcion,
            'datos_anteriores' => ['stock_actual' => (float) $stockAnterior],
            'datos_nuevos'     => ['stock_actual' => (float) $stockNuevo, 'nombre_material' => $nombreMaterial],
            'usuario_nombre'   => $usuarioNombre,
        ]);
    }

    /**
     * Cambia el estado (activo/inactivo) de una materia prima.
     */
    public function cambiarEstadoMaterial(int $id, string $estado): bool
    {
        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            return false;
        }
        $stmt = $this->conn->prepare(
            "UPDATE materias_primas SET estado = :estado WHERE id_material = :id"
        );
        return $stmt->execute([':estado' => $estado, ':id' => $id]);
    }

    /* Verificar si la materia prima está en una receta */
    public function materialTieneReceta($id)
    {
        $sql = "
        SELECT COUNT(*)
        FROM receta_colchon
        WHERE id_material = ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchColumn() > 0;
    }

  public function eliminarMaterial($id)
{
    $sql = "
        SELECT COUNT(*)
        FROM receta_colchon
        WHERE id_material = ?
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        return false;
    }

    $sql = "UPDATE materias_primas SET estado = 'inactivo' WHERE id_material = ?";

    $stmt = $this->conn->prepare($sql);

    return $stmt->execute([$id]);
}
    public function obtenerUnidades()
    {
        $stmt = $this->conn->query("
            SELECT *
            FROM unidades_medida
            ORDER BY nombre_unidad
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProveedores()
    {
        $stmt = $this->conn->query("
            SELECT *
            FROM proveedores
            ORDER BY nombre_empresa
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}