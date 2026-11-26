<?php
// Ruta relativa a public/imagenes para la primera carga del perfil.
require_once __DIR__ . '/../../config/conexion.php';

$consultaFoto = (new Conexion())->getConnection()->prepare(
    'SELECT foto FROM usuarios WHERE documento = :documento LIMIT 1'
);
$consultaFoto->execute([':documento' => $_SESSION['documento']]);
$archivoFoto = $consultaFoto->fetchColumn();

if (is_string($archivoFoto) && $archivoFoto !== '' && !strpbrk($archivoFoto, "/\\")
    && is_file(__DIR__ . '/../../public/imagenes/perfiles/' . $archivoFoto)) {
    return 'perfiles/' . rawurlencode($archivoFoto);
}

return 'perfil_predeterminado.png';
