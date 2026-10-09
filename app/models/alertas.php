<?php

require_once 'crud.php';
require_once "logger.php";
class Alerta extends Crud {
  
  const TABLE='alertas';
  
  public $pdo;
  public $logger;
  public $criticidad;
  public $mensaje;
  public $titulo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public $id;
  
  public function create(){}
  
  public function update(){}

  public function getCurrentActive(){
    try {
        $return = $this->getAlertaCierre();

        if (is_null($return)){
          $return = $this->getAlertaContactarPedido();
        }

        if (is_null($return)){
          $return = $this->getAlertaPedidosAComprar();
        }

        if (is_null($return)){
          $return = $this->getAlertaConsignacionesALiquidar();
        }

        if (is_null($return)){
          $return = $this->getAlertaConsignacionesAPagar();
        }

        if (is_null($return)){
          $return = $this->getAlertaControlStock();
        }

        return $return;

    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaCierre(){
    try {
        $return = new Alerta();

        // alerta cierre parcial
        $sql = "select
                'Alerta Cierre Parcial !' titulo,
                concat('Hola el usuario ', UCASE(username) ,' hace ', DATEDIFF(CURDATE(), updateDate) ,' dias que no hace un cierre de caja parcial') mensaje,
                CASE
                    WHEN DATEDIFF(CURDATE(), updateDate) > 7 THEN 'danger'
                    WHEN DATEDIFF(CURDATE(), updateDate) > 3 THEN 'warning'
                    ELSE 'primary'
                END AS criticidad
                from 
                turnos_parcial
                order by updatedate desc  limit 1" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          $rs = $stm->fetch(PDO::FETCH_ASSOC);
          if ($rs['criticidad']!='primary'){
            return $rs;
          } else {
            return null;  
          }
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaContactarPedido(){
    try {
        $return = new Alerta();

        // alerta contactar pedido
        $sql = "select 
                'Alerta Contactar Pedido !' titulo,
                concat('Contactar a ',ucase(cliente), ' por su pedido de ', ucase(producto), ' pasaron ',DATEDIFF(CURDATE(), p.fecha_actualizacion), ' dias desde el ultimo contacto') mensaje,
                CASE
                    WHEN DATEDIFF(CURDATE(), p.fecha_actualizacion) > 14 THEN 'danger'
                    WHEN DATEDIFF(CURDATE(), p.fecha_actualizacion) > 7 THEN 'warning'
                    ELSE 'primary'
                END AS criticidad
                from pedidos p, estado_pedido ep
                where p.id_estado = ep.id
                and p.id_estado in (4) 
                and DATEDIFF(CURDATE(), p.fecha_actualizacion) > 7
                order by p.fecha_actualizacion limit 1;" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaPedidosAComprar(){
    try {
        $return = new Alerta();

        // alerta contactar pedido
        $sql = "select 
                'Alerta Realizar Pedido !' titulo,
                concat('Tenes que pedir ',ucase(producto), ' para ', ucase(cliente), ' que hace ',DATEDIFF(CURDATE(), p.fecha_actualizacion), ' dias que solicito el pedido') mensaje,
                CASE
                    WHEN DATEDIFF(CURDATE(), p.fecha_actualizacion) > 14 THEN 'danger'
                    WHEN DATEDIFF(CURDATE(), p.fecha_actualizacion) > 7 THEN 'warning'
                    ELSE 'primary'
                END AS criticidad
                from pedidos p, estado_pedido ep
                where p.id_estado = ep.id
                and p.id_estado in (1) 
                and DATEDIFF(CURDATE(), p.fecha_actualizacion) > 7
                order by p.fecha_actualizacion limit 1;" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaConsignacionesALiquidar(){
    try {
        $return = new Alerta();

        // alerta contactar pedido
        $sql = "select
                'Alerta Consignaciones a Liquidar !' titulo,
                concat('Tenes que liquidar a ',ucase(e.nombre), ' ventas en consignacion que hace ',DATEDIFF(CURDATE(), v.fecha), ' dias se realizaron') mensaje,
                CASE
                WHEN DATEDIFF(CURDATE(), v.fecha) > 90 THEN 'danger'
                WHEN DATEDIFF(CURDATE(), v.fecha) > 30 THEN 'warning'
                ELSE 'primary'
                END AS criticidad
                from ventas v, productos p, editoriales e
                where v.id_producto = p.id
                and p.id_editorial = e.id
                and e.consignacion = TRUE
                and v.id not in  (select id_venta from consignaciones)
                group by e.nombre, v.fecha
                order by v.fecha limit 1;" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaConsignacionesAPagar(){
    try {
        $return = new Alerta();

        // alerta contactar pedido
        $sql = "select 
                'Alerta Consignaciones a Pagar !' titulo,
                concat('Tenes que abonar a ',ucase(e.nombre), ' compras por consignacion que hace ',DATEDIFF(CURDATE(), c.fecha), ' dias se liquido') mensaje,
                CASE
                WHEN DATEDIFF(CURDATE(), c.fecha) > 90 THEN 'danger'
                WHEN DATEDIFF(CURDATE(), c.fecha) > 30 THEN 'warning'
                ELSE 'primary'
                END AS criticidad
                from compras c, productos p, editoriales e
                where c.id_producto = p.id
                and p.id_editorial = e.id
                and e.consignacion = TRUE
                and c.id_estado != 6
                and c.id in (select id_compra from consignaciones)
                order by c.fecha limit 1;" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  private function getAlertaControlStock(){
    try {
        $return = new Alerta();

        // alerta control de stock
        $sql = "select 
          'Alerta Control de Stock !' titulo,
          concat('El producto ',ucase(p.titulo), ' hace ',DATEDIFF(CURDATE(), p.updateDate), ' dias que no se revisa stock') mensaje,
          CASE
          WHEN DATEDIFF(CURDATE(), p.updateDate) > 90 THEN 'danger'
          WHEN DATEDIFF(CURDATE(), p.updateDate) > 30 THEN 'warning'
          ELSE 'primary'
          END AS criticidad
          from productos p, editoriales e, productos_serie ps, productos_formato pf 
          where 
          p.id_editorial = e.id 
          and p.id_serie = ps.id 
          and p.id_formato = pf.id
          and p.stock > 0
          and ( 
          p.validado = 0 
          or ( p.validado = 1 and p.updateDate < CURRENT_DATE - INTERVAL 30 DAY)
          ) order by p.updateDate limit 1;" ;
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }


}
