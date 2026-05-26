<?php 
  class Conexion 
  {
    private string $Servidor = "localhost";
    private string $BaseDeDatos = "colsoftco";
    private string $Usuario = "root";
    private string $Password = "";
    
    public string $sql;

    public $pps = null;

    private $Conector = null;



    public function getConnection(){

      $this->Conector = new PDO(
        "mysql:host=".$this->Servidor.";dbname=".$this->BaseDeDatos,
        $this->Usuario, 
        $this->Password
      );

      $this->Conector->exec("set names utf8");
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

//   $connection = new Conexion;

//   if($connection->getConnection())
//   {
//     echo "conectado";
//   }
//   else
// {
//     echo "Error conectado";
//   }
  