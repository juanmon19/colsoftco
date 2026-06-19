<?php

require_once __DIR__ . '/../../app/logicaInforme.php';

$informe = new InformeLogica();
$datos = $informe->getMateriasPrimas();

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Reporte_Materias_Primas.xls");

echo "\xEF\xBB\xBF";


echo "<h2>COLSOFTCO - Reporte de Materias Primas</h2>";

echo "<p>Fecha de generación: "
    . date('d/m/Y H:i:s')
    . "</p>";


echo "
<table style='border-collapse: collapse;'>
";

echo "
<tr>
    <th style='border:1px solid black;'>ID</th>
    <th style='border:1px solid black;'>Nombre Material</th>
    <th style='border:1px solid black;'>Stock Actual</th>
    <th style='border:1px solid black;'>Stock Mínimo</th>
    <th style='border:1px solid black;'>Unidad</th>
    <th style='border:1px solid black;'>Proveedor</th>
</tr>
";

foreach ($datos as $fila) {

    echo "<tr>";

    echo "<td style='border:1px solid black;'>" . $fila['id_material'] . "</td>";
    echo "<td style='border:1px solid black;'>" . $fila['nombre_material'] . "</td>";
    echo "<td style='border:1px solid black;'>" . $fila['stock_actual'] . "</td>";
    echo "<td style='border:1px solid black;'>" . $fila['stock_minimo'] . "</td>";
    echo "<td style='border:1px solid black;'>" . $fila['nombre_unidad'] . "</td>";
    echo "<td style='border:1px solid black;'>" . $fila['nombre_empresa'] . "</td>";

    echo "</tr>";
}

echo "</table>";

echo "<br><br>";
echo "<strong>Total de materiales registrados: "
    . count($datos)
    . "</strong>";
