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
    // LISTAR PROVEEDORES (por estado: activo / inactivo / todos)
    // =====================================

    public function getProveedores(string $estado = 'activo'): array
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
                imagen,
                estado
            FROM proveedores
        ";

        $params = [];

        if ($estado !== 'todos') {
            $sql .= " WHERE estado = :estado ";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY nombre_empresa ASC ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =====================================
    // CONTAR PROVEEDORES POR ESTADO (para los contadores de las pestañas)
    // =====================================

    public function contarProveedoresPorEstado(): array
    {
        $sql = "
            SELECT estado, COUNT(*) AS total
            FROM proveedores
            GROUP BY estado
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $conteos = ['activo' => 0, 'inactivo' => 0];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $conteos[$fila['estado']] = (int) $fila['total'];
        }

        return $conteos;
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
    // ELIMINAR PROVEEDOR (borrado permanente — ya no se usa desde la lista,
    // se deja disponible por si se necesita en otro flujo)
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

    // =====================================
    // CAMBIAR ESTADO (HABILITAR / DESHABILITAR)
    // =====================================

    public function cambiarEstadoProveedor(int $id, string $estado): bool
    {
        if (!in_array($estado, ['activo', 'inactivo'], true)) {
            return false;
        }

        $sql = "
            UPDATE proveedores
            SET estado = :estado
            WHERE id_proveedor = :id
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':estado' => $estado,
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