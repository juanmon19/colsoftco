<?php if (!isset($esc)) { http_response_code(404); exit; } ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $esc($titulo) ?></title>
</head>
<body style="margin:0;background:#f2f4f7;color:#172c41;font:16px/1.55 Arial,Helvetica,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f4f7">
<tr><td align="center" style="padding:24px 12px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;background:white;border:1px solid #dce2e7">
<tr><td style="padding:20px 26px;background:#0a1f44;border-bottom:4px solid #d4af37">
    <table role="presentation" cellpadding="0" cellspacing="0"><tr>
    <?php if ($logo !== false): ?>
    <td style="background:#fff;padding:4px"><img src="cid:logo-colsoftco" width="56" alt="" style="display:block;width:56px;height:auto"></td>
    <?php endif; ?>
    <td style="padding-left:14px;color:white"><strong style="font-size:21px">COLSOFTCO</strong><br><span style="color:#ead49a">Max &amp; Flex</span></td>
    </tr></table>
</td></tr>
<tr><td style="padding:24px 26px">
    <h1 style="margin:0 0 16px;font: bold 24px/1.25 Arial,sans-serif;color:#0a1f44"><?= $esc($titulo) ?></h1>
    <?php if ($saludo !== ''): ?><p><?= $esc($saludo) ?></p><?php endif; ?>
    <p><?= $esc($mensaje) ?></p>
    <?php if ($detalles): ?>
    <table width="100%" cellpadding="8" cellspacing="0" style="background:#f5f7fa;border-collapse:collapse">
    <?php foreach ($detalles as $etiqueta => $valor): ?>
    <tr><th scope="row" align="left" style="border-bottom:1px solid #dde4eb"><?= $esc($etiqueta) ?></th>
        <td style="border-bottom:1px solid #dde4eb"><?= $esc($valor) ?></td></tr>
    <?php endforeach; ?>
    </table>
    <?php endif; ?>
    <?php if ($nota !== ''): ?><p><?= $esc($nota) ?></p><?php endif; ?>
    <?php if ($url !== ''): ?>
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td bgcolor="#0a1f44" style="padding:10px 18px;border-radius:4px">
    <a href="<?= $esc($url) ?>" style="font-weight:bold;color:#fff;text-decoration:none"><?= $esc($boton) ?></a>
    </td></tr></table>
    <?php endif; ?>
</td></tr>
<tr><td style="padding:14px 26px;background:#edf1f5;font-size:13px;color:#465565">Max &amp; Flex · Sistema de gestión COLSOFTCO</td></tr>
</table>
</td></tr></table>
</body>
</html>
