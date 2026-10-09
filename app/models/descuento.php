<?php
require_once 'crud.php';
require_once "logger.php";

class Descuento extends Crud
{
  
  const TABLE='descuentos';
  
  public $id;
  public $nombre;
  public $tipo;
  public $valor;  
  public $porcentaje;
  
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger = new Logger();
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (tipo,nombre,valor,porcentaje) VALUES (?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->tipo,
        $this->nombre,
        $this->valor,
        $this->porcentaje
      ));
      return $this->pdo->lastInsertId();
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){
    try{
      $params = array();
      $sql = "";
      if (isset($this->tipo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'tipo = :tipo';
        $params[':tipo'] = $this->tipo;
      }
      if (isset($this->nombre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'nombre = :nombre';
        $params[':nombre'] = $this->nombre;
      }
      if (isset($this->valor)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'valor = :valor';
        $params[':valor'] = $this->valor;
      }
      if (isset($this->porcentaje)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'porcentaje = :porcentaje';
        $params[':porcentaje'] = $this->porcentaje;
      }

      if (!empty($sql)){

        $params[':id'] = $this->id;

        $sqlUpdate = "UPDATE ".self::TABLE." SET ".$sql." WHERE id=:id";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }

   }    
  
}

 ?>