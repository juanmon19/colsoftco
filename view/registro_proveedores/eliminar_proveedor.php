<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->eliminarProveedor($id);

    header("Location: ../lista_proveedores/lista_proveedores.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Eliminar Proveedor</title>

<link rel="stylesheet" href="crud_proveedor.css">

</head>
<body>

<div class="contenedor">

    <div class="acciones-superior">
        <a href="../lista_proveedores/lista_proveedores.php" class="btn-volver">
            ← Volver a Proveedores
        </a>
    </div>

    <div class="card">

        <div class="card-header">
            Eliminar Proveedor
        </div>

        <div class="card-body">

            <h2>¿Desea eliminar este proveedor?</h2>

            <br>

            <p>
                <strong>Empresa:</strong>
                <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
            </p>

            <p>
                <strong>NIT:</strong>
                <?= htmlspecialchars($proveedor['nit']) ?>
            </p>

            <p>
                <strong>Contacto:</strong>
                <?= htmlspecialchars($proveedor['contacto_nombre']) ?>
                <?= htmlspecialchars($proveedor['contacto_apellido']) ?>
            </p>

            <p>
                <strong>Correo:</strong>
                <?= htmlspecialchars($proveedor['email']) ?>
            </p>

            <p>
                <strong>Teléfono:</strong>
                <?= htmlspecialchars($proveedor['telefono']) ?>
            </p>

            <div class="botones">

                <form method="POST">

                    <button type="submit" class="btn btn-eliminar">
                        Eliminar definitivamente
                    </button>

                </form>

                <a href="../lista_proveedores/lista_proveedores.php"
                   class="btn btn-volver">
                    Cancelar
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>