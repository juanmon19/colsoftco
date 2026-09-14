<?php

// TODO: Mover estas credenciales a un archivo .env para mayor seguridad
define("HOST", "smtp.gmail.com");
define("USERNAME", "colsoftco4@gmail.com");
define("PASSWORD", "hzwabnwohwndfvhx");

define("TIEMPO_VIDA", time() + 270); // 4.5 minutos (se evalúa por petición)

// URL base del sistema (cambiar en producción)
define("BASE_URL", "http://localhost/colsoftco");