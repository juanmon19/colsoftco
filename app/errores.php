<?php

function mostrarError($codigo) {
    http_response_code($codigo);

    $vistasValidas = [400, 403, 404, 500, 502];

    if (!in_array($codigo, $vistasValidas)) {
        $codigo = 500;
    }

    require_once __DIR__ . '/../view/errores/error_' . $codigo . '.php';
    exit;
}

set_exception_handler(function ($exception) {
    mostrarError(500);
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    mostrarError(500);
});