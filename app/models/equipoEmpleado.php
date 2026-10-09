<?php

require_once 'crud.php';
require_once "logger.php";

class EquipoEmpleado extends Crud
{

  const TABLE='equipo_empleado';

  public $id;
  public $id_equipo;
  public $id_empleado;

  public  $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function getAllWithNames(){
    try
    {
      $sql = "SELECT ee.id, ee.id_equipo, ee.id_empleado, eq.equipo, em.apellido, em.nombre
              FROM ".self::TABLE." ee
              JOIN equipos eq ON eq.id = ee.id_equipo
              JOIN empleados em ON em.id = ee.id_empleado
              ORDER BY eq.equipo, em.apellido, em.nombre";
      $stm = $this->pdo->prepare($sql);
      $stm->execute();
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (id_equipo, id_empleado) VALUES (?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->id_equipo,
        $this->id_empleado
      ));
      return $this->pdo->lastInsertId();
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){
    try{
      $sql = "UPDATE ".self::TABLE." SET id_equipo = :id_equipo, id_empleado = :id_empleado WHERE id = :id";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        ':id_equipo' => $this->id_equipo,
        ':id_empleado' => $this->id_empleado,
        ':id' => $this->id
      ));
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }
}
