<?php 
  class Conexion 
  {
    private string $Servidor = "sql301.infinityfree.com";
    private string $BaseDeDatos = "if0_43417357_colsoftco";
    private string $Usuario = "if0_43417357";
    private string $Password = "Sanijajo";
    
    public string $sql;

    public $pps = null;

    private static ?PDO $instance = null;
    private $Conector = null;

    public function getConnection(): PDO {
      if (self::$instance === null) {
        try {
          self::$instance = new PDO(
            "mysql:host={$this->Servidor};dbname={$this->BaseDeDatos};charset=utf8mb4",
            $this->Usuario, 
            $this->Password,
            [
              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
              PDO::ATTR_EMULATE_PREPARES => false,
            ]
          );
        } catch (PDOException $e) {
          error_log("Error de conexión BD: " . $e->getMessage());

          if (ob_get_level() > 0) {
            ob_end_clean();
          }

          http_response_code(500);

          $posiblesRutas = [
            dirname(__DIR__, 1) . '/view/errores/error_500.php',
            dirname(__DIR__, 2) . '/view/errores/error_500.php',
            dirname(__DIR__, 1) . '/view/errors/error_500.php',
            dirname(__DIR__, 2) . '/view/errors/error_500.php',
            dirname(__DIR__, 1) . '/view/error_500.php',
            dirname(__DIR__, 2) . '/view/error_500.php',
            $_SERVER['DOCUMENT_ROOT'] . '/view/errores/error_500.php',
            $_SERVER['DOCUMENT_ROOT'] . '/view/errors/error_500.php',
            $_SERVER['DOCUMENT_ROOT'] . '/view/error_500.php'
          ];

          $cargado = false;
          foreach ($posiblesRutas as $ruta) {
            if (file_exists($ruta)) {
              require_once $ruta;
              $cargado = true;
              break;
            }
          }

          if (!$cargado) {
            echo "<h1>Error 500 - Error interno del servidor</h1><p>No se pudo establecer conexión con la base de datos.</p>";
          }
          exit;
        }
      }
      $this->Conector = self::$instance;
      return $this->Conector;
    }

    public function closeDataBase()
    {
      if($this->pps != null)
      {
        $this->pps = null;
      }

      if($this->Conector != null)
      {
        $this->Conector = null;
      }      
    }
    
  }