<?php
require_once 'crud.php';
require_once "logger.php";
require_once dirname(__DIR__) . '/helpers/CronExpressionHelper.php';

class Compra extends Crud
{
  const TABLE='compras';
  public $pdo;
  public $logger;
  public $idOrdenCompra;
  public $fecha;
  public $idProducto;
  public $cantidad;
  public $precioLista;
  public $precioCosto;
  public $idEstado;
  public $idMedioPago;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  private function completarFechaEntregaCalculada($rows)
  {
    if (!is_array($rows)) {
      return $rows;
    }

    foreach ($rows as $key => $row) {
      $fechaOrden = isset($row['fecha']) ? new DateTime($row['fecha']) : new DateTime();
      $cronEntrega = isset($row['cron_entrega']) ? $row['cron_entrega'] : '';
      $proximaEntrega = CronExpressionHelper::proximaFecha($cronEntrega, $fechaOrden, 'Y-m-d');
      $rows[$key]['fecha_entrega_calculada'] = is_array($proximaEntrega) ? $proximaEntrega['fecha'] : null;
    }

    return $rows;
  }

  public function getLastByProducto($id){
    try
    {
        $sql = "select * from compras where id_producto = :id order by fecha desc limit 1;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':id' => $id));
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
    
  public function getAllActive(){
    try
    {
        $sql = "select
                oc.id,
                oc.fecha,
                p.nombre proveedor,
                oc.cantidad,
                oc.monto,
                oc.fecha_entrega,
                ep.estado,
                ep.id id_estado,
                pc.cron_entrega
                from orden_compra oc
                join proveedores p on oc.id_proveedor = p.id
                join estado_pedido ep on oc.id_estado_pedido = ep.id
                left join proveedor_calendario pc on pc.proveedor_id = p.id and pc.activo = 1
                where 1 = 1
                and (
                       ( oc.id_estado_pedido = 5 and oc.fecha >= (CURRENT_DATE - INTERVAL 1 MONTH))
                       or (oc.id_estado_pedido !=5)
                )
                order by oc.fecha desc;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $this->completarFechaEntregaCalculada($stm->fetchAll(PDO::FETCH_ASSOC));
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function getById($id){
    try
    {
        $sql = "select
        oc.id,
        oc.fecha,
        oc.id_proveedor,
        p.nombre proveedor,
        oc.cantidad,
        oc.monto,
        oc.fecha_entrega,
        ep.estado,
        mp.nombre medioPago
        from orden_compra oc, proveedores p, estado_pedido ep, medio_pago mp
        where oc.id_proveedor = p.id
        and oc.id_estado_pedido = ep.id
        and oc.id_medio_pago = mp.id
        and oc.id = :id";

        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':id' => $id));
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

  public function getDetails($id){
    try
    {
        $sql = "select
                p.id, 
                p.sku,
                p.titulo,
                c.precio_lista,
                c.precio_costo,
                c.cantidad,
                (c.precio_costo * c.cantidad) costo_total
                from compras c, productos p
                where c.id_orden_compra = :id
                and c.id_producto = p.id
                order by p.titulo
                ;";

        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':id' => $id));
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


  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (id_orden_compra, fecha, id_producto, cantidad, precio_lista, precio_costo, id_estado, id_medio_pago) VALUES (?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->idOrdenCompra,
        $this->fecha,
        $this->idProducto,
        $this->cantidad,
        $this->precioLista,
        $this->precioCosto,
        $this->idEstado,
        $this->idMedioPago
        ));
        return $this->pdo->lastInsertId();
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

/*Aquí actualizaremos un registro.*/
  public function update(){}


  public function createMasive($id_header, $fecha, $estado, $medioPago, $lista){

    try {
      if (empty($lista)) {
        return;
      }

      $values = array();
      $params = array();
      $this->pdo->beginTransaction();

      foreach ($lista as $item){
        $values[] = '(?,?,?,?,?,?,?,?)';
        $params[] = $id_header;
        $params[] = $fecha;
        $params[] = $item["id"];
        $params[] = $item["cantidad"];
        $params[] = $item["precioLista"];
        $params[] = $item["precioCosto"];
        $params[] = $estado;
        $params[] = $medioPago;
      }
      $sqlInsert = 'INSERT INTO compras (id_orden_compra, fecha, id_producto, cantidad, precio_lista, precio_costo, id_estado, id_medio_pago) values '.implode(',', $values);
      $stm = $this->pdo->prepare($sqlInsert);
      $stm->execute($params);

      $this->pdo->commit();

    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      $this->pdo->rollback();
    }
  }

  public function getCantidadTotalByProducto($idProducto){
    try{
        $sql = "select sum(cantidad) stock from compras where id_producto = :idProducto;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':idProducto' => $idProducto));
        if ($stm->rowCount() > 0) {
          $rs =  $stm->fetch(PDO::FETCH_ASSOC);
          return $rs['stock'];
        } else {
          return 0;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }


  public function getTotalByDate($date){
    try
    {
        $sql = "select sum(cantidad * precio_costo) monto from compras where fecha >= :date and id_medio_pago = 1;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':date' => $date));
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

public function getTotalMonth(){
  try
  {
      $sql = "select 
              sum(precio_costo * cantidad) total_compras,
              count(distinct(id_orden_compra)) cantidad_compras,
              sum(cantidad) total_unidades,
              (sum(precio_costo * cantidad) / sum(cantidad)) promedio,
              (sum(precio_costo) / sum(cantidad)) costo_promedio,
              sum(precio_costo) costo_compras
              from compras
              where
              YEAR(fecha) = YEAR(CURRENT_DATE()) 
              AND MONTH(fecha) = MONTH(CURRENT_DATE())
              ;";
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

    public function getAllPendiente(){
    try
    {
        $sql = "select
                oc.id,
                oc.fecha,
                p.nombre proveedor,
                oc.cantidad,
                oc.monto,
                oc.fecha_entrega,
                ep.estado,
                ep.id id_estado,
                pc.cron_entrega
                from orden_compra oc
                join proveedores p on oc.id_proveedor = p.id
                join estado_pedido ep on oc.id_estado_pedido = ep.id
                left join proveedor_calendario pc on pc.proveedor_id = p.id and pc.activo = 1
                where 1 = 1
                and oc.id_estado_pedido !=5
                order by oc.fecha desc, p.nombre;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $this->completarFechaEntregaCalculada($stm->fetchAll(PDO::FETCH_ASSOC));
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function updateEstadoByConsignacion($idConsignatario, $idEstado){
    try{
      $sql = "update compras set id_estado = :idEstado WHERE id in
    (
        select 
        c.id
        from compras c, productos p
        where c.id_producto = p.id
        and p.id_editorial = :idConsignatario
        and c.id_estado != :idEstadoFiltro
        and c.id in (select con.id_compra from consignaciones con)
    )
    ";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        ':idEstado' => $idEstado,
        ':idConsignatario' => $idConsignatario,
        ':idEstadoFiltro' => $idEstado
      ));
    }catch(PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

}
