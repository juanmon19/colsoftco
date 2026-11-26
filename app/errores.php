<?php

function mostrarError($codigo) {
    $codigo = (int) $codigo;
    $vistasValidas = [400, 403, 404, 500, 502];

    if (!in_array($codigo, $vistasValidas, true)) {
        $codigo = 500;
    }

    http_response_code($codigo);

    $vista = __DIR__ . '/../view/errores/error_' . $codigo . '.php';
    if (is_file($vista)) {
        require_once $vista;
    } else {
        echo 'Error ' . $codigo;
    }
    exit;
}

set_exception_handler(function ($exception) {
    error_log('Excepción no capturada: ' . $exception->getMessage() . ' en ' . $exception->getFile() . ':' . $exception->getLine());
    mostrarError(500);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    // No tumbar la aplicación por avisos menores o deprecaciones de PHP 8.2+
    if (in_array($errno, [E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED], true)) {
        error_log("Aviso PHP [{$errno}]: {$errstr} en {$errfile}:{$errline}");
        return true;
    }
    error_log("Error PHP [{$errno}]: {$errstr} en {$errfile}:{$errline}");
    mostrarError(500);
});
