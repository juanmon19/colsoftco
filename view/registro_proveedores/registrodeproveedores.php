<?php
require_once __DIR__ . '/../../app/logica_proveedores.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre_empresa = trim($_POST['nombre_empresa']);
    $contacto_nombre = trim($_POST['contacto_nombre']);
    $contacto_apellido = trim($_POST['contacto_apellido']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $nit = trim($_POST['nit']);
    $direccion = trim($_POST['direccion']);
    $descripcion_empresa = trim($_POST['descripcion_empresa']);

    try {

        $logica = new ProveedorLogica();

        $resultado = $logica->registrarProveedor(
            $nombre_empresa,
            $contacto_nombre,
            $contacto_apellido,
            $telefono,
            $email,
            $nit,
            $direccion,
            $descripcion_empresa
        );

        if ($resultado) {
            $mensaje = "Proveedor registrado correctamente";
        } else {
            $mensaje = "Error al registrar proveedor";
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Proveedores</title>
    <link rel="stylesheet" href="registrop.css">
</head>

<body>

    <header>
        <a class="logo-area" href="../panel_admin/panel_admin.html">
            <img src="../../public/imagenes/logo.png" alt="Logo">
        </a>

        <h1>Registro de Proveedores</h1>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="contenedor-principal">

        <div class="acciones-superior">
            <a href="../lista_proveedores/lista_proveedores.php" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <div class="card-formulario">

            <div class="card-header">
                
                <h2>Registrar Nuevo Proveedor</h2>
            </div>

            <form method="POST">

                <div class="grid-form">

                    <div class="field-group">
                        <label>Nombre Empresa</label>
                        <input type="text" name="nombre_empresa" required>
                    </div>

                    <div class="field-group">
                        <label>NIT</label>
                        <input type="text" name="nit" required>
                    </div>

                    <div class="field-group">
                        <label>Nombre Contacto</label>
                        <input type="text" name="contacto_nombre" required>
                    </div>

                    <div class="field-group">
                        <label>Apellido Contacto</label>
                        <input type="text" name="contacto_apellido" required>
                    </div>

                    <div class="field-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" required>
                    </div>

                    <div class="field-group">
                        <label>Correo Electrónico</label>
                        <input type="email" name="email" required>
                    </div>

                </div>

                <div class="field-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" required>
                </div>

                <div class="field-group">
                    <label>Descripción de la Empresa</label>
                    <textarea name="descripcion_empresa" rows="4"></textarea>
                </div>

                <div class="form-actions">

                    <button type="reset" class="btn-limpiar">
                        Limpiar
                    </button>

                    <button type="submit" class="btn-registrar">
                        Registrar
                    </button>

                </div>

            </form>

        </div>

    </div>

</body>

</html>