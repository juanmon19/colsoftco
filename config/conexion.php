<?php

class Conexion
{
    public $sql;
    public $pps;
    private $conexion = null;

    private string $host;
    private string $dbName;
    private string $user;
    private string $password;
    private string $charset = 'utf8mb4';

    public function __construct()
    {
        $hostActual = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $esLocal = in_array($hostActual, ['localhost', '127.0.0.1'], true)
            || strpos($hostActual, 'localhost:') === 0
            || strpos($hostActual, '127.0.0.1:') === 0;

        if ($esLocal) {
            // ===== CONFIGURACIÓN LOCAL (XAMPP) =====
            $this->host     = 'localhost';
            $this->dbName   = 'colsoftco'; // <-- Cambia si tu BD en phpMyAdmin tiene otro nombre
            $this->user     = 'root';
            $this->password = '';
        } else {
            // ===== CONFIGURACIÓN PRODUCCIÓN (INFINITYFREE) =====
            $this->host     = 'sqlXXX.infinityfree.com'; // <-- Tu MySQL Hostname de InfinityFree
            $this->dbName   = 'if0_XXXXXXXX_colsoftco';  // <-- Tu MySQL DB Name de InfinityFree
            $this->user     = 'if0_XXXXXXXX';            // <-- Tu MySQL Username de InfinityFree
            $this->password = 'TU_PASSWORD_INFINITYFREE'; // <-- Tu MySQL Password de InfinityFree
        }
    }

    public function getConnection(): ?PDO
    {
        if ($this->conexion instanceof PDO) {
            return $this->conexion;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset={$this->charset}";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $this->conexion = new PDO($dsn, $this->user, $this->password, $opciones);
            return $this->conexion;
        } catch (PDOException $e) {
            error_log('Error de conexión PDO en Conexion::getConnection(): ' . $e->getMessage());
            throw $e;
        }
    }

    public function closeDataBase(): void
    {
        $this->pps = null;
        $this->conexion = null;
    }
}