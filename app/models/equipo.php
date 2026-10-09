<?php

require_once 'crud.php';
require_once "logger.php";

class Equipo extends Crud
{

  const TABLE='equipos';

  public $id;
  public $equipo;

  public  $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (equipo) VALUES (?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->equipo
      ));
      return $this->pdo->lastInsertId();
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){
    try{
      $sql = "UPDATE ".self::TABLE." SET equipo = :equipo WHERE id = :id";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        ':equipo' => $this->equipo,
        ':id' => $this->id
      ));
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }
}
