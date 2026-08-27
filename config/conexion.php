<?php 
  class Conexion 
  {
    private string $Servidor = "localhost";
    private string $BaseDeDatos = "colsoftco";
    private string $Usuario = "root";
    private string $Password = "";
    
    public string $sql;

    public $pps = null;

    private static ?PDO $instance = null;
    private $Conector = null;

    public function getConnection(): PDO {
      if (self::$instance === null) {
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