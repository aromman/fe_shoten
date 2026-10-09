<?php
require_once 'crud.php';
require_once "logger.php";

class Pedidos extends Crud
{
  public $id;
  public $fecha;
  public $cliente;
  public $contacto;
  public $producto;
  public $idEstado;
  public $observacion;
  public $fechaActualizacion;

  const TABLE='pedidos';
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

    public function create(){
    try{

      $sql = "INSERT INTO ".self::TABLE." (fecha, cliente, contacto, producto,id_estado,observacion,fecha_actualizacion) VALUES (?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->cliente,
        $this->contacto,
        $this->producto,
        $this->idEstado,
        $this->observacion,
        $this->fechaActualizacion
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
      if (isset($this->fecha)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fecha = :fecha';
        $params[':fecha'] = $this->fecha;
      }
      if (isset($this->cliente)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'cliente = :cliente';
        $params[':cliente'] = $this->cliente;
      }
      if (isset($this->contacto)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'contacto = :contacto';
        $params[':contacto'] = $this->contacto;
      }
      if (isset($this->producto)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'producto = :producto';
        $params[':producto'] = $this->producto;
      }
      if (isset($this->idEstado)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_estado = :idEstado';
        $params[':idEstado'] = $this->idEstado;
      }
      if (isset($this->observacion)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'observacion = :observacion';
        $params[':observacion'] = $this->observacion;
      }
      if (isset($this->fechaActualizacion)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fecha_actualizacion = :fechaActualizacion';
        $params[':fechaActualizacion'] = $this->fechaActualizacion;
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



  public function getAllVigentes(){
    try
    {
        $sql = "select 
                p.id id,
                p.fecha fecha,
                p.cliente cliente,
                p.contacto,
                p.producto,
                p.id_estado id_estado,
                ep.estado estado,
                p.fecha_actualizacion,
                p.observacion,
                DATEDIFF(CURDATE(), p.fecha_actualizacion) dias_actualizacion
                from pedidos p, estado_pedido ep
                where p.id_estado = ep.id
                and p.id_estado not in (5,7) 
                order by p.id_estado, p.fecha, p.cliente, p.producto
                ;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }

    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function getFinalizadosByMonths($intervalMonths){
    try
    {
        $sql = "select
                p.id id,
                p.fecha fecha,
                p.cliente cliente,
                p.contacto,
                p.producto,
                p.id_estado id_estado,
                ep.estado estado,
                p.fecha_actualizacion
                from pedidos p, estado_pedido ep
                where p.id_estado = ep.id
                and p.id_estado in ( 5 , 7)
                and p.fecha_actualizacion >= now()-interval ".$intervalMonths." month
                order by p.fecha_actualizacion desc, p.id_estado, p.cliente, p.producto;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }

    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }


}?>