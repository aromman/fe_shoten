<?php
require_once 'crud.php';
require_once "logger.php";

class Ventas extends Crud {

  const TABLE='ventas';
  public $pdo;
  public $logger;
  public $id;
  public $fecha;
  public $total;
  public $idCanal;
  public $idProducto;
  public $unidades;
  public $idEquipo;
  public $idMedioPago;
  public $descuento;
  public $motivoDescuento;
  public $idCliente;
  public $precioUnitario;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (fecha, total, id_canal, id_producto, unidades, id_equipo, id_medio_pago, descuento, motivo_descuento, id_cliente, precio_unitario) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->total,
        $this->idCanal,
        $this->idProducto,
        $this->unidades,
        $this->idEquipo,
        $this->idMedioPago,
        $this->descuento,
        $this->motivoDescuento,
        $this->idCliente,
        $this->precioUnitario
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
      if (isset($this->total)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'total = :total';
        $params[':total'] = $this->total;
      }
      if (isset($this->idCanal)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_canal = :idCanal';
        $params[':idCanal'] = $this->idCanal;
      }
      if (isset($this->idProducto)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_producto = :idProducto';
        $params[':idProducto'] = $this->idProducto;
      }
      if (isset($this->unidades)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'unidades = :unidades';
        $params[':unidades'] = $this->unidades;
      }
      if (isset($this->idEquipo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_equipo = :idEquipo';
        $params[':idEquipo'] = $this->idEquipo;
      }
      if (isset($this->idMedioPago)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_medio_pago = :idMedioPago';
        $params[':idMedioPago'] = $this->idMedioPago;
      }
      if (isset($this->descuento)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'descuento = :descuento';
        $params[':descuento'] = $this->descuento;
      }
      if (isset($this->motivoDescuento)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'motivo_descuento = :motivoDescuento';
        $params[':motivoDescuento'] = $this->motivoDescuento;
      }
      if (isset($this->idCliente)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_cliente = :idCliente';
        $params[':idCliente'] = $this->idCliente;
      }
      if (isset($this->precioUnitario)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'precio_unitario = :precioUnitario';
        $params[':precioUnitario'] = $this->precioUnitario;
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

  public function getTotalByCanalAndDate($canal, $date){
    try
    {
        $sql = "select sum(total) monto from ventas where fecha >= :date and id_medio_pago = 1 and id_canal = :canal ;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':date' => $date, ':canal' => $canal));
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
            sum(total) total_ventas,
            count(distinct(id_ventas_header)) cantidad_ventas,
            sum(unidades) total_unidades,
            (sum(total) / sum(unidades)) promedio,
            (sum(costo) / sum(unidades)) costo_promedio
            from ventas 
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


public function getTotalMonthByCanal($canal){
  try
  {
      $sql = "select 
            sum(total) total_ventas,
            count(distinct(id_ventas_header)) cantidad_ventas,
            sum(unidades) total_unidades,
            (sum(total) / sum(unidades)) promedio,
            (sum(costo) / sum(unidades)) costo_promedio
            from ventas 
            where 
            YEAR(fecha) = YEAR(CURRENT_DATE()) 
            AND MONTH(fecha) = MONTH(CURRENT_DATE())
            AND id_canal = :canal
            ;";
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(':canal' => $canal));
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


public function getTotalMonthByFormato($idFormato){
  try
  {
      $sql = "select 
            sum(v.total) total_ventas,
            count(distinct(v.id_ventas_header)) cantidad_ventas,
            sum(v.unidades) total_unidades,
            (sum(v.total) / sum(unidades)) promedio,
            (sum(v.costo) / sum(unidades)) costo_promedio,
            sum(v.costo) total_costo
            from ventas v, productos p
            where 
            v.id_producto = p.id 
            and p.id_formato = :idFormato
            and YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
            AND MONTH(v.fecha) = MONTH(CURRENT_DATE());";
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(':idFormato' => $idFormato));
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

public function getAllMonthByFormato($idFormato){
  try
  {
      $sql = "select v.*, c.nombre canal, p.titulo producto
            from ventas v, productos p, canal c
            where 
            v.id_producto = p.id 
            and v.id_canal = c.id
            and p.id_formato = :idFormato
            and YEAR(v.fecha) = YEAR(CURRENT_DATE()) 
            AND MONTH(v.fecha) = MONTH(CURRENT_DATE())
            order by v.fecha, c.nombre
            ;";
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(':idFormato' => $idFormato));
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

public function getAllMonthGrouByDay(){
  try
  {
      $sql = "select 
            day(fecha) dia,
            sum(total) total_ventas,
            count(distinct(id_ventas_header)) cantidad_ventas,
            sum(unidades) total_unidades,
            (sum(total) / sum(unidades)) promedio,
            (sum(costo) / sum(unidades)) costo_promedio
            from ventas 
            where 
            YEAR(fecha) = YEAR(CURRENT_DATE()) 
            AND MONTH(fecha) = MONTH(CURRENT_DATE())
            group by DAY(fecha)
            order by 1
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

  public function getAllByHeader($header){
    try {

        $sql = "select
        v.id_ventas_header,
        v.fecha fecha,
        c.nombre canal,
        p.sku sku,
        p.titulo producto,
        v.unidades unidades,
        v.precio_unitario precioUnitario,
        v.descuento descuento,
        v.motivo_descuento motivoDescuento,
        v.id_producto,
        v.total total,
        mp.nombre medioPago
        from ventas v, canal c, productos p, medio_pago mp
        where v.id_canal = c.id
        and p.id = v.id_producto
        and mp.id = v.id_medio_pago
        and v.id_ventas_header = :header
        order by v.id_ventas_header desc, p.titulo";

        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':header' => $header));
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }

    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,'getAllByHeader : '.$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function getAllByEditorialAndDate($id_editorial, $fecha){
    try
    {
        $sql = "select 
            v.id,
            v.fecha,
            v.id_producto,
            v.unidades,
            v.precio_unitario,
            v.precio_unitario - ((IFNULL(e.monto_fijo, 0) + ( (v.precio_unitario - IFNULL(e.monto_fijo, 0)) * IFNULL(e.porcentaje,0)/100)))  precio_compra
            from ventas v, productos p, editoriales e 
            where e.id = :idEditorial
            and v.fecha = :fecha
            and v.id_producto = p.id
            and p.id_editorial = e.id";

        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':idEditorial' => $id_editorial, ':fecha' => $fecha));
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

  public function createMasivo($header,$fecha, $canal, $cliente, $medioPago,$porcentaje,$lista){

    try {
      
      $hayRegistros = false;
      foreach ($lista as $item){
        $hayRegistros = true;
        $id_producto =  $item["id"];
        $cantidadItem = $item["cantidad"];
        $precioItem = $item["precio"];
        $costoItem = $item["costo"];
        $subTotalItem = $cantidadItem * $precioItem;

        $item_descuento = 0;
        if ($porcentaje > 0){
            $item_descuento = ($precioItem * ($porcentaje / 100)) * $cantidadItem;
        }
        $totalItem = $subTotalItem - $item_descuento;

        $sql[] = '('.$header.',"'.$fecha.'",'.$totalItem.','.$canal.','.$id_producto.','.$cantidadItem.',1,'.$medioPago.','.$cliente.','.$precioItem.','.$item_descuento.','.$costoItem.')';
      }
      
      if ($hayRegistros){
        $this->pdo->beginTransaction();  
        $sqlInsert = 'INSERT INTO ventas (id_ventas_header, fecha, total, id_canal, id_producto, unidades, id_equipo, id_medio_pago, id_cliente, precio_unitario, descuento, costo) values '.implode(',', $sql);
        $stm = $this->pdo->prepare($sqlInsert);
        $stm->execute();
        $this->pdo->commit();
      }
      //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] Fin ======== ", 3, "my-errors.log");
    }catch(PDOException $e){
      $this->logger->log(__FILE__,'createMasivo: '.$e->getMessage(),$this->logger::CRITICAL);
      $this->pdo->rollback();
    }
  }



}
?>
