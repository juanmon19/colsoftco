<?php

session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: ../view/login/login.html");
    exit();
}

switch ($_SESSION['rol']) {

    case 'administrador':
        header("Location: ../view/panel_admin/panel_admin.html");
        break;

    case 'bodeguero':
        header("Location: ../view/panel_bodeguero/panel_bodeguero.html");
        break;

    case 'operario':
        header("Location: ../view/panel_operario/panel_operario.html");
        break;

    default:
        header("Location: ../view/login/login.html");
        break;
}

exit();