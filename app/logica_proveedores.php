<?php

require_once __DIR__ . '/../config/conexion.php';

class ProveedorLogica
{
    private $conn;

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    // =====================================
    // LISTAR TODOS LOS PROVEEDORES
    // =====================================

    public function getProveedores(): array
    {
        $sql = "
            SELECT
                id_proveedor,
                nombre_empresa,
                contacto_nombre,
                contacto_apellido,
                telefono,
                email,
                nit,
                direccion,
                descripcion_empresa,
                imagen
            FROM proveedores
            ORDER BY nombre_empresa ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // BUSCAR PROVEEDOR POR ID
    // =====================================

    public function getProveedorById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM proveedores
            WHERE id_proveedor = :id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

        return $proveedor ?: null;
    }

    // =====================================
    // REGISTRAR PROVEEDOR
    // =====================================

    public function registrarProveedor(
        string $nombreEmpresa,
        string $contactoNombre,
        string $contactoApellido,
        string $telefono,
        string $email,
        string $nit,
        string $direccion,
        string $descripcion,
        string $imagen
    ): bool {

        $sql = "
    INSERT INTO proveedores
    (
        nombre_empresa,
        contacto_nombre,
        contacto_apellido,
        telefono,
        email,
        nit,
        direccion,
        descripcion_empresa,
        imagen
    )
    VALUES
    (
        :nombre_empresa,
        :contacto_nombre,
        :contacto_apellido,
        :telefono,
        :email,
        :nit,
        :direccion,
        :descripcion_empresa,
        :imagen
    )
";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':nombre_empresa' => $nombreEmpresa,
            ':contacto_nombre' => $contactoNombre,
            ':contacto_apellido' => $contactoApellido,
            ':telefono' => $telefono,
            ':email' => $email,
            ':nit' => $nit,
            ':direccion' => $direccion,
            ':descripcion_empresa' => $descripcion,
            ':imagen' => $imagen
        ]);
    }

    // =====================================
    // ACTUALIZAR PROVEEDOR
    // =====================================

    public function actualizarProveedor(
        int $id,
        string $nombreEmpresa,
        string $contactoNombre,
        string $contactoApellido,
        string $telefono,
        string $email,
        string $nit,
        string $direccion,
        string $descripcion,
        ?string $imagen
    ): bool {

        $sql = "
    UPDATE proveedores
    SET
        nombre_empresa = :nombre_empresa,
        contacto_nombre = :contacto_nombre,
        contacto_apellido = :contacto_apellido,
        telefono = :telefono,
        email = :email,
        nit = :nit,
        direccion = :direccion,
        descripcion_empresa = :descripcion_empresa,
        imagen = :imagen
    WHERE id_proveedor = :id
";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':nombre_empresa' => $nombreEmpresa,
            ':contacto_nombre' => $contactoNombre,
            ':contacto_apellido' => $contactoApellido,
            ':telefono' => $telefono,
            ':email' => $email,
            ':nit' => $nit,
            ':direccion' => $direccion,
            ':descripcion_empresa' => $descripcion,
            ':imagen' => $imagen
        ]);
    }

    // =====================================
    // ELIMINAR PROVEEDOR
    // =====================================

    public function eliminarProveedor(int $id): bool
    {
        $sql = "
            DELETE FROM proveedores
            WHERE id_proveedor = :id
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id
        ]);
    }

    public function obtenerAlertasStock()
    {
        $sql = "SELECT *
            FROM materias_primas
            WHERE stock_actual <= stock_minimo
            ORDER BY stock_actual ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMateriaPorId($id)
    {
        $sql = "SELECT *
            FROM materias_primas
            WHERE id_material = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarStockMinimo($id, $stockMinimo)
    {
        $sql = "UPDATE materias_primas
            SET stock_minimo = ?
            WHERE id_material = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $stockMinimo,
            $id
        ]);
    }

    public function eliminarAlerta($id)
    {
        $sql = "UPDATE materias_primas
            SET stock_minimo = 0
            WHERE id_material = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }
}
