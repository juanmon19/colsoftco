<?php

session_start();

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