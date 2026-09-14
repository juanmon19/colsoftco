<?php


date_default_timezone_set('America/Bogota');

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_informes.php';
require_once __DIR__ . '/../../vendor/autoload.php';



use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

$MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$logica = new InformeLogica();

$anioActual = (int) date('Y');
[$anioA, $mesA] = InformeLogica::parseAnioMes($_GET['mes_a'] ?? 1, $anioActual, 1);
[$anioB, $mesB] = InformeLogica::parseAnioMes($_GET['mes_b'] ?? 12, $anioActual, 12);
$datos = $logica->obtenerComparativo($mesA, $mesB, $anioA, $anioB);

/* =======================================================
   ARMAR EXCEL PERSONALIZADO
   ======================================================= */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Informe de Inventario');

$colorNavy = '0A1F44';
$colorGold = 'D4AF37';

/* ── 1. Insertar Logo ── */
$rutaLogo = __DIR__ . '/../../public/imagenes/logo.png';
if (file_exists($rutaLogo)) {
    $drawing = new Drawing();
    $drawing->setName('Logo COLSOFTCO');
    $drawing->setDescription('Logo');
    $drawing->setPath($rutaLogo);
    $drawing->setCoordinates('A1');
    $drawing->setHeight(55); 
    $drawing->setOffsetX(10); 
    $drawing->setOffsetY(10); 
    $drawing->setWorksheet($sheet);
}

/* ── 2. Título Principal ── */
$sheet->getRowDimension(1)->setRowHeight(60); 
$sheet->mergeCells('B1:F1');
$sheet->setCellValue('B1', 'INFORME DE MATERIA PRIMA — COLSOFTCO');
$sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNavy);
$sheet->getStyle('B1:F1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($colorGold);
$sheet->getStyle('B1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

/* ── 3. Subtítulo (Fechas) ── */
$sheet->mergeCells('A2:F2');
$subtitulo = 'Comparando: ' . $MESES[$mesA] . ' ' . $anioA . ' vs ' . $MESES[$mesB] . ' ' . $anioB . '   |   Generado: ' . date('d/m/Y H:i');
$sheet->setCellValue('A2', $subtitulo);
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('555555');
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F4F6FB');
$sheet->getRowDimension(2)->setRowHeight(20);
$sheet->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

/* ── 4. Encabezados de la tabla ── */
$encabezados = ['Material', "Ent. {$MESES[$mesA]} {$anioA}", "Sal. {$MESES[$mesA]} {$anioA}", "Ent. {$MESES[$mesB]} {$anioB}", "Sal. {$MESES[$mesB]} {$anioB}", 'Dif. Mov.'];
$sheet->fromArray($encabezados, null, 'A4');

$sheet->getStyle('A4:F4')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A4:F4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNavy);
$sheet->getStyle('A4:F4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:F4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($colorGold);
$sheet->getRowDimension(4)->setRowHeight(25);
$sheet->getStyle('A4:F4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

/* ── 5. Filas de Datos ── */
$fila = 5;
if (empty($datos)) {
    $sheet->mergeCells("A{$fila}:F{$fila}");
    $sheet->setCellValue("A{$fila}", 'No se registraron movimientos de materia prima en el rango seleccionado.');
    $sheet->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("A{$fila}")->getFont()->setItalic(true)->getColor()->setRGB('888888');
    $fila++;
} else {
    foreach ($datos as $d) {
        $sheet->setCellValue("A{$fila}", $d['nombre']);
        $sheet->setCellValue("B{$fila}", $d['entradas_a']);
        $sheet->setCellValue("C{$fila}", $d['salidas_a'] > 0 ? -$d['salidas_a'] : 0);
        $sheet->setCellValue("D{$fila}", $d['entradas_b']);
        $sheet->setCellValue("E{$fila}", $d['salidas_b'] > 0 ? -$d['salidas_b'] : 0);
        $sheet->setCellValue("F{$fila}", $d['dif_mov']);
        $sheet->getStyle("A{$fila}")->getFont()->setBold(true);
        $sheet->getStyle("F{$fila}")->getFont()->setBold(true);
        $sheet->getStyle("B{$fila}:F{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($fila % 2 === 0) {
            $sheet->getStyle("A{$fila}:F{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F3F8');
        }
        $fila++;
    }
}

foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
$sheet->getStyle("A4:F" . ($fila - 1))->getBorders()->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D9D9D9');

/* =======================================================
   DESCARGA
   ======================================================= */
$nombreArchivo = 'Informe_Comparativo_' . $MESES[$mesA] . $anioA . '_vs_' . $MESES[$mesB] . $anioB . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;