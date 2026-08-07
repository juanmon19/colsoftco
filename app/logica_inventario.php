<?php

require_once __DIR__ . '/../config/conexion.php';

class InventarioLogica
{
    private $conn;

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    public function listarMateriales()
    {
        $sql = "
        SELECT
            mp.*,
            um.nombre_unidad,
            p.nombre_empresa
        FROM materias_primas mp
        LEFT JOIN unidades_medida um
            ON mp.id_unidad = um.id_unidad
        LEFT JOIN proveedores p
            ON mp.id_proveedor = p.id_proveedor
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

        return $stmt->execute([
            $nombre,
            $stockActual,
            $stockMinimo,
            $unidad,
            $proveedor
        ]);
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

        return $stmt->execute([
            $nombre,
            $stockActual,
            $stockMinimo,
            $unidad,
            $proveedor,
            $id
        ]);
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

    $sql = "
        DELETE FROM materias_primas
        WHERE id_material = ?
    ";

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