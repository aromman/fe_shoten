<?php

require_once 'crud.php';
require_once "logger.php";

class Rol extends Crud
{
  
  const TABLE='roles';
  
  public  $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){
    // a implementar
  }
  
  public function update(){
    // a implementar
  }
}