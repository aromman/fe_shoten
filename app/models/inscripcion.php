<?php

require_once 'crud.php';
require_once "logger.php";

class Inscripcion extends Crud
{
  
  const TABLE='inscripciones';
  
  public $pdo;
  public $logger;
  public $id;
  public $idCanal;
  public $nombre;
  public $idEstado;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (id_canal,nombre,id_estado) VALUES (?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->idCanal,
        $this->nombre,
        $this->idEstado
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

      if (isset($this->idCanal)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_canal = :id_canal';
        $params[':id_canal'] = $this->idCanal;
      }
      if (isset($this->nombre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'nombre = :nombre';
        $params[':nombre'] = $this->nombre;
      }
      if (isset($this->idEstado)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_estado = :id_estado';
        $params[':id_estado'] = $this->idEstado;
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

  public function getAllActive($orderBy){
    try{
      $sql = "select i.id, 
                     c.nombre torneo,
                     i.nombre,
                     i.id_estado
                from inscripciones i, canal c
               where i.id_canal = c.id
                and c.tipo = 'TORNEO'
                 and c.activo = TRUE
                  AND c.vende = TRUE;";
      $stm=$this->pdo->prepare($sql);
      $stm->execute();
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }
}