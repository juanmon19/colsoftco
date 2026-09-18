<?php

// TODO: Mover estas credenciales a un archivo .env para mayor seguridad
define("HOST", "smtp.gmail.com");
define("USERNAME", "colsoftco4@gmail.com");
define("PASSWORD", "hzwabnwohwndfvhx");

define("TIEMPO_VIDA_SEGUNDOS", 270); // 4.5 minutos de vigencia para tokens
// Compat: TIEMPO_VIDA es el timestamp de expiración calculado al momento de usarlo.
if (!defined("TIEMPO_VIDA")) {
    define("TIEMPO_VIDA", time() + TIEMPO_VIDA_SEGUNDOS);
}
function tiempo_expiracion_token(): int {
    return time() + TIEMPO_VIDA_SEGUNDOS;
}

// URL base del sistema (cambiar en producción)
define("BASE_URL", "http://localhost/colsoftco");