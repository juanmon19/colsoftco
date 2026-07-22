<?php

require_once '../../../config/conexion.php';

$db = new Conexion();
$conn = $db->getConnection();

if (!isset($_GET['id'])) {
    header("Location: lista_inventario.php");
    exit();
}

$id = $_GET['id'];

/* Obtener datos del material */
$sql = "SELECT * FROM materias_primas WHERE id_material = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    header("Location: lista_inventario.php");
    exit();
}

/* Confirmar eliminación */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "DELETE FROM materias_primas WHERE id_material = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    header("Location: lista_inventario.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Materia Prima</title>

    <style>

        body{
            font-family: Arial, sans-serif;
            background:#eef2ff;
            margin:0;
        }

        .contenedor{
            width:900px;
            margin:40px auto;
        }

        .volver{
            display:inline-block;
            background:#6c757d;
            color:white;
            text-decoration:none;
            padding:12px 18px;
            border-radius:6px;
            margin-bottom:20px;
            font-weight:bold;
        }

        .card{
            background:white;
            border-radius:10px;
            overflow:hidden;
            box-shadow:0 4px 10px rgba(0,0,0,.15);
        }

        .card-header{
            background:#0A1F44;
            color:white;
            padding:20px;
            font-size:32px;
            font-weight:bold;
            border-bottom:4px solid #D4AF37;
        }

        .card-body{
            padding:30px;
        }

        h2{
            color:#0A1F44;
        }

        .info{
            margin-top:25px;
            line-height:2;
            font-size:18px;
        }

        .acciones{
            margin-top:30px;
        }

        .btn-eliminar{
            background:#dc2626;
            color:white;
            border:none;
            padding:12px 20px;
            border-radius:6px;
            cursor:pointer;
            font-weight:bold;
            font-size:15px;
        }

        .btn-eliminar:hover{
            background:#b91c1c;
        }

        .btn-cancelar{
            background:#6c757d;
            color:white;
            text-decoration:none;
            padding:12px 20px;
            border-radius:6px;
            margin-left:10px;
            font-weight:bold;
        }

    </style>

</head>
<body>

<div class="contenedor">

    <a href="../../inventario_materiaprima/inventario_materia_prima.php" class="volver">
        ← Volver a Inventario
    </a>

    <div class="card">

        <div class="card-header">
            Eliminar Materia Prima
        </div>

        <div class="card-body">

            <h2>¿Desea eliminar esta materia prima?</h2>

            <div class="info">

                <strong>Material:</strong>
                <?= htmlspecialchars($material['nombre_material']) ?>
                <br>

                <strong>Stock Actual:</strong>
                <?= $material['stock_actual'] ?>
                <br>

                <strong>Stock Mínimo:</strong>
                <?= $material['stock_minimo'] ?>
                <br>

                <strong>ID Unidad:</strong>
                <?= $material['id_unidad'] ?>
                <br>

                <strong>ID Proveedor:</strong>
                <?= $material['id_proveedor'] ?>

            </div>

            <div class="acciones">

                <form method="POST" style="display:inline;">
                    <button type="submit" class="btn-eliminar">
                        Eliminar definitivamente
                    </button>
                </form>

                <a href="lista_inventario.php" class="btn-cancelar">
                    Cancelar
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>