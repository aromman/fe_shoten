<?php

require_once 'crud.php';
require_once "logger.php";

class EstadoPedido extends Crud
{
  const TABLE='estado_pedido';
  public  $pdo;
  public $logger;

  public $id;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){}
  public function update(){}

}