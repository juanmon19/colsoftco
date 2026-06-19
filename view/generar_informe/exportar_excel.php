<?php

require_once __DIR__ . '/../../app/logicaInforme.php';

$informe = new InformeLogica();
$datos = $informe->getMateriasPrimas();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Reporte_Materias_Primas.xls");

echo "<table border='1'>";

echo "
<tr>
    <th>ID</th>
    <th>Nombre Material</th>
    <th>Stock Actual</th>
    <th>Stock Mínimo</th>
    <th>ID Unidad</th>
    <th>ID Proveedor</th>
</tr>
";

foreach ($datos as $fila) {

    echo "<tr>";

    echo "<td>".$fila['id_material']."</td>";
    echo "<td>".$fila['nombre_material']."</td>";
    echo "<td>".$fila['stock_actual']."</td>";
    echo "<td>".$fila['stock_minimo']."</td>";
    echo "<td>".$fila['id_unidad']."</td>";
    echo "<td>".$fila['id_proveedor']."</td>";

    echo "</tr>";
}

echo "</table>";