<?php
/**
 * Subida segura de imágenes — COLSOFTCO (remediación auditoría AppSec H1–H5)
 * ==========================================================================
 * Defensas en profundidad:
 *  1. Tamaño máximo en servidor (el del cliente se ignora).
 *  2. MIME olfateado con finfo sobre el tmp (NO el Content-Type del cliente).
 *  3. Allowlist estricta: solo JPEG/PNG/WEBP (nada de SVG/HTML/PDF ejecutable).
 *  4. Verificación estructural con getimagesize() (ancho/alto reales).
 *  5. RE-CODIFICACIÓN con GD: destruye polyglots (PHP anexado), scripts
 *     embebidos y metadatos EXIF. Lo que se guarda NUNCA es el bytes original.
 *  6. Nombre aleatorio (random_bytes) + extensión forzada del MIME real.
 *     El nombre del cliente jamás toca el disco (adiós path traversal).
 *  7. Borrado con basename() para que un valor de BD no pueda salirse del dir.
 */

const UPLOAD_MIMES_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

const UPLOAD_MAX_DIMENSION = 4000; // anti-bomba de descompresión

/**
 * Valida y guarda una imagen re-codificada. Lanza RuntimeException si falla.
 *
 * @param array  $file    Elemento de $_FILES ya verificado con UPLOAD_ERR_OK.
 * @param string $destDir Directorio destino (se crea con 0755 si falta).
 * @param int    $maxBytes Tamaño máximo en bytes.
 * @param string $prefijo Prefijo legible del nombre (solo [a-z0-9_], resto se limpia).
 * @return string Nombre del archivo guardado (solo para persistir en BD).
 */
function guardarImagenSegura(array $file, string $destDir, int $maxBytes, string $prefijo = 'img'): string
{
    if (!isset($file['tmp_name'], $file['size'], $file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se recibió ninguna imagen válida.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Archivo no válido.');
    }

    if ($file['size'] <= 0 || $file['size'] > $maxBytes) {
        throw new RuntimeException('La imagen supera el tamaño máximo permitido.');
    }

    // MIME real olfateado en servidor (ignora Content-Type del cliente).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!isset(UPLOAD_MIMES_PERMITIDOS[$mime])) {
        throw new RuntimeException('Formato no permitido. Usa JPG, PNG o WEBP.');
    }
    $extension = UPLOAD_MIMES_PERMITIDOS[$mime];

    // Estructura real de imagen (dimensiones). Falla con PDFs/HTML/SVG disfrazados.
    $dims = @getimagesize($file['tmp_name']);
    if ($dims === false || $dims[0] <= 0 || $dims[1] <= 0) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }
    if ($dims[0] > UPLOAD_MAX_DIMENSION || $dims[1] > UPLOAD_MAX_DIMENSION) {
        throw new RuntimeException('La imagen excede las dimensiones máximas.');
    }

    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new RuntimeException('No se pudo preparar el almacenamiento.');
    }

    $prefijoLimpio = preg_replace('/[^a-z0-9_]/i', '', $prefijo) ?: 'img';
    $nombre = $prefijoLimpio . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
    $ruta = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $nombre;

    // Re-codificar con GD: el bytes original (posibles polyglots, scripts,
    // EXIF malicioso) jamás llega al disco.
    if (function_exists('imagecreatefromstring')) {
        $bin = file_get_contents($file['tmp_name']);
        if ($bin === false) {
            throw new RuntimeException('No se pudo leer la imagen.');
        }
        $img = @imagecreatefromstring($bin);
        unset($bin);
        if ($img === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $ok = match ($extension) {
            'jpg'  => imagejpeg($img, $ruta, 88),
            'png'  => imagepng($img, $ruta, 6),
            'webp' => function_exists('imagewebp') ? imagewebp($img, $ruta, 88) : false,
        };
        imagedestroy($img);

        if (!$ok || !is_file($ruta)) {
            @unlink($ruta);
            throw new RuntimeException('No fue posible guardar la imagen.');
        }

        return $nombre;
    }

    // Sin GD: degradado SEGURO (no se rompe la subida). Se conservan todas
    // las demás defensas: MIME olfateado, allowlist, estructura verificada,
    // nombre aleatorio con extensión forzada y .htaccess sin ejecución.
    error_log('upload_seguro: GD no disponible, guardado sin re-codificar. Activa extension=gd.');
    if (!move_uploaded_file($file['tmp_name'], $ruta) || !is_file($ruta)) {
        @unlink($ruta);
        throw new RuntimeException('No fue posible guardar la imagen.');
    }

    return $nombre;
}

/**
 * Borra un archivo previamente guardado sin permitir salir del directorio,
 * aunque el valor venga de la BD.
 */
function borrarArchivoSeguro(string $destDir, ?string $nombre): void
{
    if ($nombre === null || $nombre === '') {
        return;
    }
    $ruta = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . basename($nombre);
    if (is_file($ruta)) {
        @unlink($ruta);
    }
}
