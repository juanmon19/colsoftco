<?php

require_once '../../../app/logica_proveedores.php';

$logica = new ProveedorLogica();

$id = $_GET['id'];

$material = $logica->obtenerMateriaPorId($id);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $logica->actualizarStockMinimo(
        $id,
        $_POST['stock_minimo']
    );

    header("Location: lista_alertas.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Alerta</title>

    <link rel="stylesheet" href="alertas.css">
</head>

<body>

    <header>
        <h1>Editar Stock Mínimo</h1>
    </header>

    <div class="container">

        <div class="card">

            <div class="card-header">
                Actualizar Alerta
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="form-group">
                        <label>Material</label>
                        <input type="text"
                            class="form-control"
                            value="<?= $material['nombre_material'] ?>"
                            readonly>
                    </div>

                    <div class="form-group">
                        <label>Stock Mínimo</label>
                        <input type="number"
                            name="stock_minimo"
                            class="form-control"
                            value="<?= $material['stock_minimo'] ?>"
                            required>
                    </div>

                    <div class="botones">

                        <a href="lista_alertas.php"
                            class="btn btn-volver">
                            ← Volver
                        </a>

                        <button type="submit"
                            class="btn btn-guardar">
                            Guardar Cambios
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</body>

</html>