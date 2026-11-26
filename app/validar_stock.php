<?php

function stockValido($cantidad): bool
{
    // Las columnas de inventario son DECIMAL(12,2).
    return (is_string($cantidad) || is_int($cantidad) || is_float($cantidad))
        && preg_match('/^0*[0-9]{1,10}(?:\.[0-9]{1,2})?$/D', trim((string) $cantidad)) === 1;
}
