<?php

require_once 'crud.php';
require_once "logger.php";

class Canal extends Crud {
  
  const TABLE='canal';
  
  public $pdo;
  public $logger;
  public $id;
  public $nombre;
  public $tipo;
  public $fechaInicio;
  public $fechaFin;
  public $activo;
  public $vende;
  public $puntoVenta;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger = new Logger();
  }

  public function getAllActive($orderBy){
    try
    {
        $allowedOrderBy = array(
          'id' => 'id',
          'nombre' => 'nombre',
          'nombre ASC' => 'nombre ASC',
          'nombre DESC' => 'nombre DESC',
          'tipo' => 'tipo',
          'fechaInicio' => 'fechaInicio'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $sql = "SELECT * FROM canal WHERE activo = TRUE AND vende = TRUE AND fechaInicio <= CURDATE() ORDER BY ".$allowedOrderBy[$orderBy].";";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function getByType($type, $orderBy){
    try
    {
        $allowedOrderBy = array(
          'id' => 'id',
          'nombre' => 'nombre',
          'nombre ASC' => 'nombre ASC',
          'nombre DESC' => 'nombre DESC',
          'tipo' => 'tipo',
          'fechaInicio' => 'fechaInicio'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $sql = "SELECT * FROM canal WHERE activo = TRUE AND tipo = :type ORDER BY ".$allowedOrderBy[$orderBy].";";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':type' => $type));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (nombre,tipo,fechaInicio,fechaFin,activo,vende,punto_venta) VALUES (?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->nombre,
        $this->tipo,
        $this->fechaInicio,
        $this->fechaFin,
        $this->activo,
        $this->vende,
        $this->puntoVenta
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
      if (isset($this->nombre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'nombre = :nombre';
        $params[':nombre'] = $this->nombre;
      }
      if (isset($this->tipo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'tipo = :tipo';
        $params[':tipo'] = $this->tipo;
      }
      if (isset($this->fechaInicio)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fechaInicio = :fechaInicio';
        $params[':fechaInicio'] = $this->fechaInicio;
      }
      if (isset($this->fechaFin)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fechaFin = :fechaFin';
        $params[':fechaFin'] = $this->fechaFin;
      }
      if (isset($this->activo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'activo = :activo';
        $params[':activo'] = $this->activo;
      }
      if (isset($this->vende)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'vende = :vende';
        $params[':vende'] = $this->vende;
      }
      if (isset($this->puntoVenta)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'punto_venta = :puntoVenta';
        $params[':puntoVenta'] = $this->puntoVenta;
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

