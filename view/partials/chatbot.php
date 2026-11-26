<?php
require_once __DIR__ . '/../../app/permisos.php';
$rolAyuda = isset($_SESSION['documento']) ? rol_actual() : '';
$modulosAyuda = array_filter(array_keys(PERMISOS_MODULOS),
    static fn($vista) => $rolAyuda !== '' && puede_acceder($vista, $rolAyuda));
?>
<colsoft-ayuda data-rol="<?= htmlspecialchars($rolAyuda, ENT_QUOTES, 'UTF-8') ?>"
    data-modulos="<?= htmlspecialchars(implode(' ', $modulosAyuda), ENT_QUOTES, 'UTF-8') ?>"></colsoft-ayuda>
