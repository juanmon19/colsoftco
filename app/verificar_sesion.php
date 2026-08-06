<?php
session_start();

// Verificar si el usuario inició sesión
if (!isset($_SESSION['documento'])) {
    header("Location: ../login/login.php");
    exit();
}