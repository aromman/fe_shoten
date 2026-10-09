<?php
require_once 'crud.php';
require_once "logger.php";

class Consignaciones extends Crud
{
  private $id;
  const TABLE='consignaciones';
  public $pdo;
  public $logger;
  public $fecha;
  public $idVenta;
  public $idCompra;
  public $idEstado;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }


  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (fecha, id_venta, id_compra, id_estado) VALUES (?,?,?,?);";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->idVenta,
        $this->idCompra,
        $this->idEstado
      ));
      return $this->pdo->lastInsertId();
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){}

  public function getTotalPendientePago(){
    try
    {
       $sql = "select sum(c.cantidad * c.precio_lista) monto
        from compras c, productos p, editoriales e
        where c.id_producto = p.id
        and p.id_editorial = e.id
        and e.consignacion = TRUE
        and c.id_estado != 6
        and c.id in (select co.id_compra from consignaciones co);";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }

    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
}

  public function getAllALiquidar(){
    try
    {
       $sql = "select
                e.nombre nombre,
                v.fecha,
                sum(v.total) monto,
                sum((IFNULL(e.monto_fijo, 0) + ( (v.precio_unitario - IFNULL(e.monto_fijo, 0)) * IFNULL(e.porcentaje,0)/100)) * v.unidades) comision,
                e.id
                from ventas v, productos p, editoriales e
                where v.id_producto = p.id
                and p.id_editorial = e.id
                and e.consignacion = TRUE
                and v.id not in  (select id_venta from consignaciones)
                group by e.nombre, v.fecha
                order by 1,2 desc;";
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

  public function getAllAPagar(){
    try
    {
       $sql = "select 
              c.id,
              e.id editorial,
              e.nombre nombre,
              c.fecha,
              (c.cantidad * c.precio_lista) monto,
              (c.cantidad * c.precio_costo) costo
              from compras c, productos p, editoriales e
              where c.id_producto = p.id
              and p.id_editorial = e.id
              and e.consignacion = TRUE
              and c.id_estado != 6
              and c.id in (select id_compra from consignaciones)
              order by e.nombre, c.fecha desc;";
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

  public function deleteByCompra($idCompra){
    try
    {
       $sql = "delete from ".self::TABLE." where id_compra = ?;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array($idCompra));
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }

  }

  public function getAllAPagarByEditorial($id_editorial){
    try
    {
       $sql = "select 
               p.sku,
              p.titulo,
              c.id,
              e.nombre nombre,
              c.fecha,
              c.cantidad,
              c.precio_lista,
              c.precio_costo
              from compras c, productos p, editoriales e
              where c.id_producto = p.id
              and p.id_editorial = e.id
              and e.id = ".$id_editorial."
              and c.id_estado != 6
              and c.id in (select id_compra from consignaciones)
              order by c.fecha, p.titulo;";
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

}
 ?>