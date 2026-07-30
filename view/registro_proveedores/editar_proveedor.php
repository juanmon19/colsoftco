
<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;

$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->actualizarProveedor(
        $id,
        $_POST['nombre_empresa'],
        $_POST['contacto_nombre'],
        $_POST['contacto_apellido'],
        $_POST['telefono'],
        $_POST['email'],
        $_POST['nit'],
        $_POST['direccion'],
        $_POST['descripcion_empresa']
    );

    header("Location: ../lista_proveedores/lista_proveedores.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Proveedor</title>

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
            Editar Proveedor
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="form-grid">

                    <div class="campo">
                        <label>Nombre Empresa</label>
                        <input
                            type="text"
                            name="nombre_empresa"
                            value="<?= htmlspecialchars($proveedor['nombre_empresa']) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label>NIT</label>
                        <input
                            type="text"
                            name="nit"
                            value="<?= htmlspecialchars($proveedor['nit']) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label>Nombre Contacto</label>
                        <input
                            type="text"
                            name="contacto_nombre"
                            value="<?= htmlspecialchars($proveedor['contacto_nombre']) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label>Apellido Contacto</label>
                        <input
                            type="text"
                            name="contacto_apellido"
                            value="<?= htmlspecialchars($proveedor['contacto_apellido']) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label>Teléfono</label>
                        <input
                            type="text"
                            name="telefono"
                            value="<?= htmlspecialchars($proveedor['telefono']) ?>"
                            required>
                    </div>

                    <div class="campo">
                        <label>Correo Electrónico</label>
                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($proveedor['email']) ?>"
                            required>
                    </div>

                    <div class="campo full">
                        <label>Dirección</label>
                        <input
                            type="text"
                            name="direccion"
                            value="<?= htmlspecialchars($proveedor['direccion']) ?>"
                            required>
                    </div>

                    <div class="campo full">
                        <label>Descripción Empresa</label>
                        <textarea
                            name="descripcion_empresa"
                            required><?= htmlspecialchars($proveedor['descripcion_empresa']) ?></textarea>
                    </div>

                </div>

                <div class="botones">

                    <button type="submit" class="btn btn-guardar">
                        Guardar Cambios
                    </button>

                    <a href="../lista_proveedores/lista_proveedores.php"
                       class="btn btn-volver">
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>