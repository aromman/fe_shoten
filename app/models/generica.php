<?php

require_once 'crud.php';
require_once "logger.php";

class Generica extends Crud
{
  const TABLE='';
  public  $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function getSql($sql){
      try{
          //$this->logger->log(__FILE__,$sql,$this->logger::CRITICAL);
          $stm = $this->pdo->prepare($sql);
          $stm->execute();
          return $stm->fetchAll(PDO::FETCH_ASSOC);
      }
      catch (PDOException $e){
          $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
          die($e->getMessage());
      }
  }

  public function updateSql($sql){
      try{
          //$this->logger->log(__FILE__,$sql,$this->logger::CRITICAL);
          $stm = $this->pdo->prepare($sql);
          $stm->execute();
      }
      catch (PDOException $e){
          $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
          die($e->getMessage());
      }
  }

  public function insertSql($sql){
      try{
          //$this->logger->log(__FILE__,$sql,$this->logger::CRITICAL);
          $stm = $this->pdo->prepare($sql);
          $stm->execute();
          return $this->pdo->lastInsertId();
      }
      catch (PDOException $e){
          $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
          die($e->getMessage());
      }
  }


  public function create(){}

  public function update(){}
}