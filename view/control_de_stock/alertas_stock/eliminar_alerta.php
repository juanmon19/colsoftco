<?php

require_once '../../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

if (!isset($_GET['id'])) {
    header("Location: lista_alertas.php");
    exit;
}

$id = $_GET['id'];

/* Obtener material para mostrarlo */
$material = $logica->obtenerMateriaPorId($id);

if (!$material) {
    header("Location: lista_alertas.php");
    exit;
}

/* Eliminar cuando se confirme */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $logica->eliminarAlerta($id);

    header("Location: lista_alertas.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Alerta</title>
    <link rel="stylesheet" href="alertas.css">
</head>

<body>

<header>
    <div class="logo">
        <a href="lista_alertas.php">
            <img src="../../../public/imagenes/logo.png" alt="logo">
        </a>
    </div>
    
    <div class="header-title">
        <h1>Eliminar Alerta de Stock</h1>
    </div>

    <div style="width: 50px; flex-shrink: 0;"></div>
</header>

<div class="container">

    <a href="lista_alertas.php" class="btn-volver">
        ← Volver
    </a>

    <div class="card">

        <div class="card-header">
            Confirmar Eliminación
        </div>

        <div class="card-body">

            <p class="mensaje-eliminar">
                ¿Está seguro de eliminar la alerta del material:
                <strong>
                    <?= htmlspecialchars($material['nombre_material']) ?>
                </strong>?
            </p>

            <form method="POST">

                <div class="botones">

                    <a href="lista_alertas.php"
                       class="btn btn-cancelar">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-eliminar">
                        Eliminar
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>