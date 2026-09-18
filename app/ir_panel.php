<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si llega con ?denegado=1 es porque la guardia de roles bloqueó una ruta.
// Se guarda el aviso y se le devuelve a SU panel (el rol nunca cambia).
if (isset($_GET['denegado'])) {
    $_SESSION['aviso_rol'] = 'No tienes permiso para ese módulo. Te devolvimos a tu panel.';
}

if (!isset($_SESSION['rol'])) {
    header("Location: ../view/login/login.php");
    exit();
}

switch ($_SESSION['rol']) {

    case 'administrador':
        header("Location: ../view/panel_admin/panel_admin.php");
        break;

    case 'bodeguero':
        header("Location: ../view/panel_bodeguero/panel_bodeguero.php");
        break;

    case 'operario':
        header("Location: ../view/panel_operario/panel_operario.php");
        break;

    default:
        header("Location: ../view/login/login.php");
        break;
}

exit();