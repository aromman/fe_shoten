<?php

require_once 'crud.php';
require_once "logger.php";
class Empleado extends Crud
{

  const TABLE='empleados';

  public $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public $id;
  public $apellido;
  public $nombre;

  public function getByName($apellido, $nombre){
    try
    {
        $stm = $this->pdo->prepare("SELECT * from ".self::TABLE." where apellido = '$apellido' and nombre='$nombre'");
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (apellido, nombre) VALUES (?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->apellido,
        $this->nombre
        ));
        return $this->pdo->lastInsertId();
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){
    try{
      $sql = "UPDATE ".self::TABLE." SET apellido = :apellido, nombre = :nombre WHERE id = :id";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        ':apellido' => $this->apellido,
        ':nombre' => $this->nombre,
        ':id' => $this->id
      ));
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

}
