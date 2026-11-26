<?php

require_once __DIR__ . '/validar_stock.php';
require_once __DIR__ . '/HistorialMovimientos.php';

function registrarMateriaPrima(PDO $conn, array $datos): int
{
    $texto = static fn($campo) => is_string($datos[$campo] ?? null) ? trim($datos[$campo]) : '';
    $nombre = $texto('nombre_material');
    $stock = $datos['stock_actual'] ?? null;
    $minimo = $datos['stock_minimo'] ?? '0';
    if ($nombre === '' || mb_strlen($nombre) > 100) {
        throw new DomainException('Escribe un nombre de material de hasta 100 caracteres.');
    }
    if (!stockValido($stock) || !stockValido($minimo)) {
        throw new DomainException('Las existencias deben ser cantidades no negativas con hasta dos decimales.');
    }

    $unidad = $texto('id_unidad');
    $proveedor = $texto('id_proveedor');
    $transaccionPropia = !$conn->inTransaction();
    if ($transaccionPropia) {
        $conn->beginTransaction();
    }
    try {
        if ($unidad === 'nueva') {
            $nombreUnidad = $texto('nombre_unidad');
            $sigla = $texto('sigla');
            if ($nombreUnidad === '' || mb_strlen($nombreUnidad) > 20 || $sigla === '' || mb_strlen($sigla) > 5) {
                throw new DomainException('Indica el nombre de la unidad (hasta 20 caracteres) y su sigla (hasta 5).');
            }
            // La clave única de sigla resuelve también dos altas simultáneas.
            $stmt = $conn->prepare('INSERT INTO unidades_medida (nombre_unidad, sigla) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE id_unidad = LAST_INSERT_ID(id_unidad)');
            $stmt->execute([$nombreUnidad, $sigla]);
            $unidad = $conn->lastInsertId();
            $stmt = $conn->prepare('SELECT 1 FROM unidades_medida WHERE id_unidad = ? AND nombre_unidad = ?');
            $stmt->execute([$unidad, $nombreUnidad]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('Esa sigla pertenece a otra unidad. Selecciona la unidad existente o cambia la sigla.');
            }
        } else {
            $stmt = $conn->prepare('SELECT id_unidad FROM unidades_medida WHERE id_unidad = ?');
            $stmt->execute([ctype_digit($unidad) ? $unidad : 0]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('Selecciona una unidad válida o registra una nueva.');
            }
        }
        if ($proveedor !== '') {
            $stmt = $conn->prepare("SELECT id_proveedor FROM proveedores WHERE id_proveedor = ? AND estado = 'activo' FOR UPDATE");
            $stmt->execute([ctype_digit($proveedor) ? $proveedor : 0]);
            if (!$stmt->fetchColumn()) {
                throw new DomainException('El proveedor seleccionado ya no está disponible.');
            }
        }
        $proveedor = $proveedor === '' ? null : $proveedor;
        $stmt = $conn->prepare('INSERT INTO materias_primas
            (nombre_material, stock_actual, stock_minimo, id_unidad, id_proveedor) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$nombre, $stock, $minimo, $unidad, $proveedor]);
        $id = (int) $conn->lastInsertId();
        $registrado = (new HistorialMovimientos())->registrar([
            'modulo' => 'materia_prima', 'accion' => 'crear', 'id_registro' => $id,
            'descripcion' => "Se registró la materia prima '{$nombre}' con stock inicial de {$stock}",
            'datos_anteriores' => ['stock_actual' => 0],
            'datos_nuevos' => ['nombre_material' => $nombre, 'stock_actual' => $stock,
                'stock_minimo' => $minimo, 'id_unidad' => $unidad, 'id_proveedor' => $proveedor],
            'usuario_id' => $_SESSION['user_id'] ?? null,
            'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
        ]);
        if (!$registrado) {
            throw new RuntimeException('No se pudo registrar el historial.');
        }
        if ($transaccionPropia) {
            $conn->commit();
        }
        return $id;
    } catch (Throwable $e) {
        if ($transaccionPropia) {
            $conn->rollBack();
        }
        throw $e;
    }
}
