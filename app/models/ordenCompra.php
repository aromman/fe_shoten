<?php

require_once 'crud.php';
require_once "logger.php";

class OrdenCompra extends Crud
{
  const TABLE='orden_compra';
  public  $pdo;
  public $logger;
  
  public $id;
  public $fecha;
  public $idProveedor;
  public $cantidad;
  public $monto;
  public $fechaEntrega;
  public $idEstadoPedido;
  public $idMedioPago;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  
/*Aquí Insertamos un animal, tenemos que crear forzosamente este método porque en el CRUD lo agregamos como **abstract** sino lo agregamos obtendremos un error.*/
  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (
              fecha,
              id_proveedor,
              cantidad,
              monto,
              fecha_entrega,
              id_estado_pedido,
              id_medio_pago) VALUES (?,?,?,?,?,?,?)";
      $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
          $this->fecha,
          $this->idProveedor,
          $this->cantidad,
          $this->monto,
          $this->fechaEntrega,
          $this->idEstadoPedido,
          $this->idMedioPago
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
      if (isset($this->idProveedor)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_proveedor = :idProveedor';
        $params[':idProveedor'] = $this->idProveedor;
      }
      if (isset($this->cantidad)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'cantidad = :cantidad';
        $params[':cantidad'] = $this->cantidad;
      }
      if (isset($this->monto)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'monto = :monto';
        $params[':monto'] = $this->monto;
      }
      if (isset($this->fechaEntrega)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fecha_entrega = :fechaEntrega';
        $params[':fechaEntrega'] = $this->fechaEntrega;
      }
      if (isset($this->idEstadoPedido)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_estado_pedido = :idEstadoPedido';
        $params[':idEstadoPedido'] = $this->idEstadoPedido;
      }
      if (isset($this->idMedioPago)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_medio_pago = :idMedioPago';
        $params[':idMedioPago'] = $this->idMedioPago;
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

  public function updateStatus($id, $idStatus){
    try{
      $this->pdo->beginTransaction();
      $sqlUpdate = "UPDATE ".self::TABLE." SET id_estado_pedido = :idStatus WHERE id = :id";
      $stm=$this->pdo->prepare($sqlUpdate);
      $stm->execute(array(':idStatus' => $idStatus, ':id' => $id));
        
      $sqlUpdate2 = "update compras set id_estado = :idStatus WHERE id_orden_compra = :id";
      $stm=$this->pdo->prepare($sqlUpdate2);
      $stm->execute(array(':idStatus' => $idStatus, ':id' => $id));

      $this->pdo->commit();

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        $this->pdo->rollback();
    }

  }

  public function getCandidatasConciliacion($desde, $hasta, $idCuenta){
    try {
      $sql = "SELECT oc.id, oc.fecha, oc.monto monto,
              CONCAT('OC #', oc.id, ' - ', p.nombre) descripcion,
              'Compra' tipo,
              p.nombre extra
              FROM orden_compra oc
              LEFT JOIN medio_pago mp ON mp.id = oc.id_medio_pago
              LEFT JOIN proveedores p ON p.id = oc.id_proveedor
              WHERE oc.fecha >= :desde
                AND oc.fecha < :hasta
                AND mp.id_cuenta = :cuenta
                AND NOT EXISTS (
                    SELECT 1
                    FROM cuentas_movimientos cm
                    WHERE cm.origen_tipo = 'compra'
                      AND cm.origen_id = oc.id
                )
              ORDER BY oc.fecha, oc.id";
      $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(
        ':desde' => $desde,
        ':hasta' => $hasta,
        ':cuenta' => $idCuenta,
      ));
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      return array();
    }
  }

  public function getConciliacionDetalleById($id){
    try {
      $sql = "SELECT oc.id, oc.fecha, oc.monto,
              CONCAT('OC #', oc.id, ' - ', COALESCE(p.nombre, '')) descripcion,
              'Compra' tipo,
              COALESCE(p.nombre, '') extra
              FROM orden_compra oc
              LEFT JOIN proveedores p ON p.id = oc.id_proveedor
              WHERE oc.id = :id
              LIMIT 1";
      $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(':id' => (int) $id));
      $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
      return !empty($rows) ? $rows[0] : null;
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      return null;
    }
  }

}
