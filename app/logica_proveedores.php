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
                descripcion_empresa
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
        string $descripcion
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
                descripcion_empresa
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
                :descripcion_empresa
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
            ':descripcion_empresa' => $descripcion
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
        string $descripcion
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
                descripcion_empresa = :descripcion_empresa
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
            ':descripcion_empresa' => $descripcion
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
}