<?php

require_once __DIR__ . '/../fpdf/fpdf.php';
require_once __DIR__ . '/../app/logicaInforme.php';

$informe = new InformeLogica();
$datos = $informe->getMateriasPrimas();

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'COLSOFTCO - Reporte de Materias Primas', 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 6, 'Fecha de generacion: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        $this->Ln(4);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function TableHeader()
    {
        $this->SetFillColor(70, 70, 70);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);

        $this->Cell(15, 8, 'ID', 1, 0, 'C', true);
        $this->Cell(55, 8, 'Nombre Material', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Stock Actual', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Stock Minimo', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Unidad', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Proveedor', 1, 1, 'C', true);

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->TableHeader();

$fill = false;
foreach ($datos as $fila) {

    $bajoMinimo = $fila['stock_actual'] < $fila['stock_minimo'];
    $pdf->SetTextColor($bajoMinimo ? 200 : 0, 0, 0);
    $pdf->SetFillColor(240, 240, 240);

    $pdf->Cell(15, 8, $fila['id_material'], 1, 0, 'C', $fill);
    $pdf->Cell(55, 8, mb_convert_encoding($fila['nombre_material'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
    $pdf->Cell(25, 8, number_format($fila['stock_actual'], 2), 1, 0, 'R', $fill);
    $pdf->Cell(25, 8, number_format($fila['stock_minimo'], 2), 1, 0, 'R', $fill);
    $pdf->Cell(25, 8, mb_convert_encoding($fila['nombre_unidad'] ?? '-', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', $fill);
    $pdf->Cell(45, 8, mb_convert_encoding($fila['nombre_empresa'] ?? '-', 'ISO-8859-1', 'UTF-8'), 1, 1, 'L', $fill);

    $fill = !$fill;
}

$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Ln(4);
$pdf->Cell(0, 8, 'Total de materiales registrados: ' . count($datos), 0, 1, 'L');

// 'D' fuerza la descarga del archivo, 'I' lo muestra en el navegador
$pdf->Output('D', 'Reporte_Materias_Primas_' . date('Ymd_His') . '.pdf');