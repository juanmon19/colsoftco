<?php

require_once __DIR__ . '/app/logicaInforme.php';

$informe = new InformeLogica();

$datos = $informe->getMateriasPrimas();

echo "<pre>";
print_r($datos);
echo "</pre>";