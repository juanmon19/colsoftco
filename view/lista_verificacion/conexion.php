<?php
/**
 * Conexión a la base de datos usando PDO.
 * Ajusta estos datos según tu entorno (XAMPP, servidor local, etc.)
 */

$host   = 'localhost';
$dbname = 'colsoftco';      // Nombre de tu base de datos
$user   = 'root';           // Usuario de tu MySQL
$pass   = '';                // Contraseña de tu MySQL
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}