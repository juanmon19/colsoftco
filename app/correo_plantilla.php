<?php

use PHPMailer\PHPMailer\PHPMailer;

function prepararCorreo(PHPMailer $mail, string $titulo, string $saludo, string $mensaje,
    array $detalles = [], string $nota = '', string $boton = '', string $url = ''): void
{
    $esc = static fn($texto) => htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $mail->isHTML(true);
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->Subject = $titulo;
    $rutaLogo = dirname(__DIR__) . '/public/imagenes/logo.png';
    $logo = is_file($rutaLogo) ? file_get_contents($rutaLogo) : false;
    if ($logo !== false) {
        $mail->addStringEmbeddedImage($logo, 'logo-colsoftco', 'COLSOFTCO.png', 'base64', 'image/png');
    }
    $rutaPlantillaHtml = __DIR__ . '/../view/correos/plantilla.php';
    if (is_file($rutaPlantillaHtml)) {
        ob_start();
        require $rutaPlantillaHtml;
        $mail->Body = ob_get_clean();
    } else {
        $htmlDetalles = '';
        foreach ($detalles as $etiqueta => $valor) {
            $htmlDetalles .= '<li><strong>' . $esc($etiqueta) . ':</strong> ' . $esc($valor) . '</li>';
        }
        $htmlBoton = $url !== ''
            ? '<p><a href="' . $esc($url) . '">' . $esc($boton ?: 'Abrir enlace') . '</a></p>'
            : '';
        $mail->Body = '<h2>' . $esc($titulo) . '</h2>'
            . ($saludo !== '' ? '<p>' . $esc($saludo) . '</p>' : '')
            . '<p>' . $esc($mensaje) . '</p>'
            . ($htmlDetalles !== '' ? '<ul>' . $htmlDetalles . '</ul>' : '')
            . ($nota !== '' ? '<p>' . $esc($nota) . '</p>' : '')
            . $htmlBoton;
    }

    $lineas = [$titulo, $saludo, $mensaje];
    foreach ($detalles as $etiqueta => $valor) {
        $lineas[] = $etiqueta . ': ' . $valor;
    }
    $lineas[] = $nota;
    if ($url !== '') {
        $lineas[] = $boton . ': ' . $url;
    }
    $lineas[] = 'Max & Flex · COLSOFTCO';
    $mail->AltBody = implode("\n\n", array_filter($lineas, static fn($linea) => $linea !== ''));
}
