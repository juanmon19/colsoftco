<?php

header('Content-Type: application/json');
require_once "../../config/conexion.php";

try {

    $conexion = new Conexion();
    $db = $conexion->getConnection();

    $sql = "SELECT
                id_material,
                nombre_material,
                stock_actual,
                stock_minimo
            FROM materias_primas";

    $stmt = $db->prepare($sql);
    $stmt->execute();

    $materias = [];

    while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {

        if ($fila['stock_actual'] <= 0) {
            $estado = "Agotado";
        } elseif ($fila['stock_actual'] <= $fila['stock_minimo']) {
            $estado = "Limitado";
        } else {
            $estado = "Disponible";
        }

        $materias[] = [
            "nombre" => $fila['nombre_material'],
            "categoria" => "Materia Prima",
            "stock" => $fila['stock_actual'],
            "estado" => $estado
        ];
    }

    echo json_encode($materias);

} catch (Exception $e) {

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}