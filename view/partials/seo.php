<?php
/**
 * SEO compartido — COLSOFTCO
 * ==========================
 * Emite canonical absoluta + descripción + robots + Open Graph/Twitter
 * en cualquier vista. Incluir DESPUÉS del viewport en el <head>:
 *   <?php include __DIR__ . '/../partials/seo.php'; ?>
 * (en subcarpetas view/<mod>/<sub>/: '/../../partials/seo.php')
 *
 * Personalizable por página definiendo ANTES del include:
 *   $seo_titulo, $seo_descripcion
 */
if (!isset($__seo_done)) {
    $__seo_done = true;

    $__esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $__host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $__ruta    = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
    $__canonica = $__esquema . '://' . $__host . $__ruta;

    // Raíz de la app para el logo OG (funciona en /colsoftco o en raíz).
    $__base = (strpos($__ruta, '/colsoftco') === 0) ? '/colsoftco' : '';
    $__logo  = $__esquema . '://' . $__host . $__base . '/public/imagenes/logo.png';

    $__tit  = $seo_titulo ?? 'COLSOFTCO — Sistema de Gestión';
    $__desc = $seo_descripcion
        ?? 'COLSOFTCO: sistema de gestión y administración de materias primas para Max&Flex. Inventarios, stock, proveedores, recetas y producción.';

    echo '<link rel="canonical" href="' . htmlspecialchars($__canonica) . '">' . "\n";
    echo '<meta name="description" content="' . htmlspecialchars($__desc) . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-image-preview:large">' . "\n";
    echo '<meta name="theme-color" content="#0A1F44">' . "\n";
    echo '<meta property="og:type" content="website">' . "\n";
    echo '<meta property="og:site_name" content="COLSOFTCO">' . "\n";
    echo '<meta property="og:locale" content="es_CO">' . "\n";
    echo '<meta property="og:title" content="' . htmlspecialchars($__tit) . '">' . "\n";
    echo '<meta property="og:description" content="' . htmlspecialchars($__desc) . '">' . "\n";
    echo '<meta property="og:url" content="' . htmlspecialchars($__canonica) . '">' . "\n";
    echo '<meta property="og:image" content="' . htmlspecialchars($__logo) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary">' . "\n";
    echo '<meta name="twitter:title" content="' . htmlspecialchars($__tit) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . htmlspecialchars($__desc) . '">' . "\n";
}
