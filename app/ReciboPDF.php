<?php
/**
 * ReciboPDF
 * Va en app/ReciboPDF.php, junto al resto de tus clases logica_*.php
 *
 * Requiere: composer require setasign/fpdf
 */

require_once __DIR__ . '/../vendor/autoload.php';

// La clase de FPDF instalada por Composer puede llamarse "Fpdf\Fpdf"
// (versiones nuevas) o simplemente "FPDF" en el espacio global
// (versión 1.8.2, la que te instaló Composer). Detectamos cuál existe.
if (class_exists('Fpdf\Fpdf')) {
    class_alias('Fpdf\Fpdf', 'FpdfBase');
} elseif (class_exists('FPDF')) {
    class_alias('FPDF', 'FpdfBase');
} else {
    throw new Exception('No se encontró la clase FPDF. Verifica que "composer require setasign/fpdf" se haya instalado correctamente.');
}

class ReciboPDF extends FpdfBase
{
    private $numeroRecibo;
    private $fechaHora;

    // Paleta de marca
    private $azulMarino = [10, 31, 68];    // #0A1F44
    private $dorado      = [212, 175, 55]; // #D4AF37
    private $grisPanel   = [244, 246, 251];
    private $bordePanel  = [226, 230, 240];
    private $verdeFondo  = [232, 248, 238];
    private $verdeBorde  = [182, 230, 198];
    private $verdeTexto  = [22, 101, 52];

    public function __construct($numeroRecibo, $fechaHora = null)
    {
        parent::__construct('P', 'mm', 'A4');
        $this->numeroRecibo = $numeroRecibo;
        /* Si no se pasa una fecha (recibo nuevo), se usa el momento actual.
           Si se pasa (recibo regenerado desde el historial), se respeta la fecha real. */
        $this->fechaHora = $fechaHora ?? date('d/m/Y H:i');
        $this->SetAutoPageBreak(true, 25);
        $this->SetMargins(15, 15, 15);
    }

    /**
     * Cabecera: logo centrado, línea dorada, título y datos del recibo.
     */
    function Header()
    {
        // app/ReciboPDF.php -> ../public/imagenes/logo.png
        $logoPath = __DIR__ . '/../public/imagenes/logo.png';

        if (file_exists($logoPath)) {
            $anchoLogo = 26;
            $x = ($this->GetPageWidth() - $anchoLogo) / 2;
            $this->Image($logoPath, $x, 12, $anchoLogo);
            $this->SetY(12 + $anchoLogo + 4);
        } else {
            $this->SetY(15);
        }

        $this->SetDrawColor(...$this->dorado);
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(6);

        $this->SetTextColor(...$this->azulMarino);
        $this->SetFont('Arial', 'B', 18);
        $this->Cell(0, 10, utf8_decode('Comprobante de Producción'), 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(0, 6, 'COLSOFTCO  -  Sistema de Gestion Max&Flex', 0, 1, 'C');
        $this->Ln(4);

        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(...$this->azulMarino);
        $this->Cell(95, 8, utf8_decode('Recibo No.: ') . str_pad($this->numeroRecibo, 6, '0', STR_PAD_LEFT), 0, 0, 'L');
        $this->Cell(95, 8, utf8_decode('Fecha: ') . $this->fechaHora, 0, 1, 'R');

        $this->SetDrawColor(...$this->dorado);
        $this->SetLineWidth(0.4);
        $this->Line(15, $this->GetY() + 2, 195, $this->GetY() + 2);
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-22);
        $this->SetDrawColor(...$this->dorado);
        $this->SetLineWidth(0.6);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3);

        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(130, 130, 130);
        $this->Cell(0, 6, utf8_decode('Generado automaticamente por el sistema COLSOFTCO  -  Pagina ') . $this->PageNo(), 0, 0, 'C');
    }

    public function agregarDetalle($nombreProducto, $cantidad)
    {
        $yInicio = $this->GetY();
        $alto = 34;

        $this->SetFillColor(...$this->grisPanel);
        $this->SetDrawColor(...$this->bordePanel);
        $this->Rect(15, $yInicio, 180, $alto, 'DF');

        $this->SetXY(22, $yInicio + 7);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(...$this->azulMarino);
        $this->Cell(55, 8, utf8_decode('Producto fabricado:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(105, 8, utf8_decode($nombreProducto), 0, 1, 'L');

        $this->SetXY(22, $yInicio + 19);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(...$this->azulMarino);
        $this->Cell(55, 8, utf8_decode('Cantidad producida:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 12);
        $this->Cell(105, 8, $cantidad . ' unidades', 0, 1, 'L');

        $this->SetY($yInicio + $alto + 8);
    }

    public function agregarMensajeExito()
    {
        $yInicio = $this->GetY();
        $alto = 26;

        $this->SetFillColor(...$this->verdeFondo);
        $this->SetDrawColor(...$this->verdeBorde);
        $this->Rect(15, $yInicio, 180, $alto, 'DF');

        $this->SetXY(22, $yInicio + 6);
        $this->SetTextColor(...$this->verdeTexto);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(170, 7, utf8_decode('Produccion registrada exitosamente'), 0, 1, 'L');

        $this->SetX(22);
        $this->SetFont('Arial', '', 10);
        $this->MultiCell(166, 5.5, utf8_decode(
            'El inventario de materia prima fue descontado y el stock de producto terminado ' .
            'fue actualizado correctamente en el sistema.'
        ));

        $this->SetY($yInicio + $alto + 10);
    }
}