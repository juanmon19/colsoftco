<?php

date_default_timezone_set('America/Bogota'); 

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_informes.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$mesInicio = max(1, min(12, (int) ($_GET['mes_inicio'] ?? 1)));
$mesFin    = max(1, min(12, (int) ($_GET['mes_fin'] ?? 12)));

if ($mesInicio > $mesFin) {
    [$mesInicio, $mesFin] = [$mesFin, $mesInicio];
}

$MESES = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$logica = new InformeLogica();
$datos = $logica->obtenerMovimientosPorMes($mesInicio, $mesFin);

$rutaLogo = __DIR__ . '/../../public/imagenes/logo.png';
$base64Logo = '';
if (file_exists($rutaLogo)) {
    $tipoContenido = pathinfo($rutaLogo, PATHINFO_EXTENSION);
    $datosImagen = file_get_contents($rutaLogo);
    $base64Logo = 'data:image/' . $tipoContenido . ';base64,' . base64_encode($datosImagen);
}

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 25px; }
        .header img { width: 85px; margin-bottom: 10px; }
        .header h1 { color: #0A1F44; font-size: 20px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .header h3 { color: #D4AF37; font-size: 12px; margin: 5px 0 0 0; font-weight: normal; }
        .divider { border: none; border-top: 2px solid #D4AF37; margin: 20px 0; }
        .info-box { background: #f4f6fb; border-left: 4px solid #0A1F44; padding: 12px; margin-bottom: 25px; border-radius: 4px; }
        .info-box p { margin: 4px 0; font-size: 12px; color: #555; }
        .info-box strong { color: #0A1F44; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead th { background: #0A1F44; color: #D4AF37; font-size: 11px; text-transform: uppercase; padding: 10px 8px; text-align: center; border-bottom: 2px solid #D4AF37; }
        thead th:nth-child(2) { text-align: left; }
        tbody td { font-size: 11px; padding: 9px 8px; border-bottom: 1px solid #e2e6f0; text-align: center; }
        tbody td:nth-child(2) { text-align: left; }
        tbody tr:nth-child(even) { background: #F0F3F8; }
        .sin-datos { text-align: center; color: #888; font-style: italic; padding: 30px; font-size: 13px; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <?php if($base64Logo): ?>
            <img src="<?= $base64Logo ?>" alt="Logo ColSoft">
        <?php endif; ?>
        <h1>Informe de Materia Prima</h1>
        <h3>COLSOFTCO - Sistema de Gestión Max&Flex</h3>
    </div>

    <hr class="divider">

    <div class="info-box">
        <p><strong>Rango evaluado:</strong> <?= $MESES[$mesInicio] ?> – <?= $MESES[$mesFin] ?></p>
        <p><strong>Fecha de generación:</strong> <?= date('d/m/Y H:i') ?></p>
    </div>

    <?php if (empty($datos)): ?>
        <p class="sin-datos">No se registraron movimientos de materia prima en el rango seleccionado.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th>Material</th>
                    <th>Entradas</th>
                    <th>Salidas</th>
                    <th>Mov. Total</th>
                    <th>Stock Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datos as $d): ?>
                    <?php 
                        // Cálculos matemáticos correctos
                        $entradas = (float) $d['entradas'];
                        $salidas = (float) $d['salidas'];
                        $salida_negativa = $salidas > 0 ? -$salidas : 0;
                        $mov_total = $entradas - $salidas;
                    ?>
                    <tr>
                        <td><strong><?= $MESES[$d['mes']] ?></strong></td>
                        <td><?= htmlspecialchars($d['nombre']) ?></td>
                        <td style="color: <?= $entradas > 0 ? 'green' : '#333' ?>;"><?= $entradas ?></td>
                        <td style="color: <?= $salidas > 0 ? 'red' : '#333' ?>;"><?= $salida_negativa ?></td>
                        <td><strong><?= $mov_total ?></strong></td>
                        <td><strong><?= $d['stock_final'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="footer">
        Generado automáticamente por el sistema COLSOFTCO • Página 1
    </div>

</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true); 
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait'); 
$dompdf->render();

$nombreArchivo = 'Informe_MateriaPrima_' . $MESES[$mesInicio] . '_' . $MESES[$mesFin] . '.pdf';
$dompdf->stream($nombreArchivo, ['Attachment' => true]);
exit;