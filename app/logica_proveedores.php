<?php
require_once __DIR__ . '/validar_stock.php';

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
        $params = [];
        $filtroWhere = '';
        if ($estado !== 'todos') {
            $filtroWhere = ' WHERE p.estado = :estado';
            $params[':estado'] = $estado;
        }
        $order = ' ORDER BY p.nombre_empresa, p.id_proveedor, m.nombre_material, m.id_material';

        $sql = "SELECT p.id_proveedor, p.nombre_empresa, p.contacto_nombre, p.contacto_apellido,
                       p.telefono, p.email, p.nit, p.direccion, p.descripcion_empresa, p.imagen, p.estado,
                       m.nombre_material
                FROM proveedores p
                LEFT JOIN (
                    SELECT id_proveedor, id_material FROM materias_primas WHERE id_proveedor IS NOT NULL
                    UNION
                    SELECT id_proveedor, id_material FROM proveedores_materiales
                ) vinculo ON vinculo.id_proveedor = p.id_proveedor
                LEFT JOIN materias_primas m ON m.id_material = vinculo.id_material AND m.estado = 'activo'"
            . $filtroWhere . $order;

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
        } catch (\PDOException $e) {
            // Compatibilidad si la tabla proveedores_materiales aún no existe en la BD
            $sqlFallback = "SELECT p.id_proveedor, p.nombre_empresa, p.contacto_nombre, p.contacto_apellido,
                                   p.telefono, p.email, p.nit, p.direccion, p.descripcion_empresa, p.imagen, p.estado,
                                   m.nombre_material
                            FROM proveedores p
                            LEFT JOIN materias_primas m ON m.id_proveedor = p.id_proveedor AND m.estado = 'activo'"
                . $filtroWhere . $order;
            $stmt = $this->conn->prepare($sqlFallback);
            $stmt->execute($params);
        }

        $proveedores = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $fila['id_proveedor'];
            $material = $fila['nombre_material'];
            if (!isset($proveedores[$id])) {
                unset($fila['nombre_material']);
                $fila['materias_primas'] = [];
                $proveedores[$id] = $fila;
            }
            if ($material !== null) {
                $proveedores[$id]['materias_primas'][] = $material;
            }
        }
        return array_values($proveedores);
    }

    // =====================================
    // CONTAR PROVEEDORES POR ESTADO (para los contadores de las pestañas)
    // =====================================

    // Se llama dentro de la transacción de alta del proveedor.
    public function vincularMateriaPrima(int $material, int $proveedor): bool
    {
        $stmt = $this->conn->prepare("SELECT id_proveedor FROM materias_primas
            WHERE id_material = ? AND estado = 'activo' FOR UPDATE");
        $stmt->execute([$material]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return false;
        }
        if ($fila['id_proveedor'] === null) {
            $stmt = $this->conn->prepare('UPDATE materias_primas SET id_proveedor = ? WHERE id_material = ?');
            return $stmt->execute([$proveedor, $material]);
        }
        try {
            $stmt = $this->conn->prepare('INSERT INTO proveedores_materiales (id_proveedor, id_material) VALUES (?, ?)');
            return $stmt->execute([$proveedor, $material]);
        } catch (\PDOException $e) {
            $stmt = $this->conn->prepare('UPDATE materias_primas SET id_proveedor = ? WHERE id_material = ?');
            return $stmt->execute([$proveedor, $material]);
        }
    }

    public function materialesParaEdicion(int $proveedor): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT m.id_material, m.nombre_material, m.estado,
                CASE WHEN m.id_proveedor = ? OR pm.id_material IS NOT NULL THEN 1 ELSE 0 END AS vinculado
                FROM materias_primas m LEFT JOIN proveedores_materiales pm
                ON pm.id_material = m.id_material AND pm.id_proveedor = ?
                WHERE m.estado = 'activo' OR m.id_proveedor = ? OR pm.id_material IS NOT NULL
                ORDER BY m.nombre_material, m.id_material");
            $stmt->execute([$proveedor, $proveedor, $proveedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $stmt = $this->conn->prepare("SELECT m.id_material, m.nombre_material, m.estado,
                CASE WHEN m.id_proveedor = ? THEN 1 ELSE 0 END AS vinculado
                FROM materias_primas m
                WHERE m.estado = 'activo' OR m.id_proveedor = ?
                ORDER BY m.nombre_material, m.id_material");
            $stmt->execute([$proveedor, $proveedor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // La actualización del proveedor mantiene bloqueada su fila en la misma transacción.
    public function cambiarMateriaSuministrada(int $proveedor, int $anterior, int $nueva): void
    {
        if ($anterior === $nueva) {
            return;
        }
        $vinculados = array_column(array_filter($this->materialesParaEdicion($proveedor),
            static fn($fila) => $fila['vinculado']), 'id_material');
        if (($anterior && !in_array($anterior, $vinculados)) || (!$anterior && $vinculados)) {
            throw new DomainException('El catálogo ha cambiado. Revisa la materia prima que quieres reemplazar.');
        }
        if (in_array($nueva, $vinculados)) {
            throw new DomainException('El proveedor ya suministra esa materia prima.');
        }
        if (!$this->vincularMateriaPrima($nueva, $proveedor)) {
            throw new DomainException('Selecciona una materia prima activa.');
        }
        if ($anterior) {
            $stmt = $this->conn->prepare('UPDATE materias_primas SET id_proveedor = NULL
                WHERE id_material = ? AND id_proveedor = ?');
            $stmt->execute([$anterior, $proveedor]);
            try {
                $stmt = $this->conn->prepare('DELETE FROM proveedores_materiales WHERE id_material = ? AND id_proveedor = ?');
                $stmt->execute([$anterior, $proveedor]);
            } catch (\PDOException $e) {
                // Tabla opcional no migrada
            }
        }
    }

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
        string $imagen,
        ?int &$idNuevo = null
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

        $guardado = $stmt->execute([
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
        $idNuevo = $guardado ? (int) $this->conn->lastInsertId() : null;
        return $guardado;
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

    // ── DEPRECADO: lógica de stock/inventario ──
    // Usar InventarioLogica::obtenerMaterial / actualizarStockMinimo / eliminarAlerta.
    // Se conservan por compatibilidad con código antiguo.
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
        if (!stockValido($stockMinimo)) {
            return false;
        }
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