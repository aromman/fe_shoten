<?php
require_once 'crud.php';
require_once "logger.php";

class VentasHeader extends Crud
{
  public $id;

  public $idCanal;
  public $fecha;
  public $idCliente;
  public $idMedioPago;
  public $cantidad;
  public $subTotal;
  public $descuento;
  public $total;
  public $updateDate;
  public $userName;


  const TABLE='ventas_header';
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  /* create */
  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (id_canal, fecha, id_cliente, id_medio_pago, cantidad, subTotal, descuento, total, updateDate, username) VALUES (?,?,?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->idCanal,
        $this->fecha,
        $this->idCliente,
        $this->idMedioPago,
        $this->cantidad,
        $this->subTotal,
        $this->descuento,
        $this->total,
        $this->updateDate,
        $this->userName
        ));
        return $this->pdo->lastInsertId();
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  /* update */
  public function update(){
    try{

      $params = array();
      $sql = "";
      if (isset($this->subTotal)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'subTotal = :subTotal';
        $params[':subTotal'] = $this->subTotal;
      }
      if (isset($this->descuento)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'descuento = :descuento';
        $params[':descuento'] = $this->descuento;
      }
      if (isset($this->total)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'total = :total';
        $params[':total'] = $this->total;
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


  public function getAllByPaymentIdAndBetweenDate($medioPago, $dateFrom, $dateTo){
    try {
  
        $params = array(':medioPago' => $medioPago, ':dateTo' => $dateTo);
        $sql = "select
        *
        from ventas_header
        where id_medio_pago = :medioPago";
        
        if (!is_null($dateFrom)){
          $sql = $sql . " and fecha >= :dateFrom";
          $params[':dateFrom'] = $dateFrom;
        }

        $sql = $sql . " and fecha <= :dateTo";
  
        $stm = $this->pdo->prepare($sql);
        $stm->execute($params);
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
  
  public function getByIdMovement($movimiento){
    try {
  
        return null;
  
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  
  public function getAllLastDays($days, $orderBy){
    try {
        $days = max(0, (int) $days);
        $allowedOrderBy = array(
          'vh.fecha desc, vh.id desc' => 'vh.fecha desc, vh.id desc',
          'vh.fecha DESC, vh.id DESC' => 'vh.fecha DESC, vh.id DESC',
          'vh.fecha desc' => 'vh.fecha desc',
          'vh.id desc' => 'vh.id desc'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $sql = "SELECT
        vh.*,
        mp.nombre medio_pago,
        c.punto_venta punto_venta
        from ventas_header vh, medio_pago mp, canal c
        WHERE vh.fecha >= (CURRENT_DATE - INTERVAL $days DAY)
        and vh.id_medio_pago = mp.id
        and vh.id_canal = c.id  
        ORDER BY ".$allowedOrderBy[$orderBy].";";
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }


  public function getById($id){
    try {
        $sql = "select vh.fecha,
        CONCAT (cli.apellido,' ',cli.nombre)  cliente,
        mp.nombre medioPago,
        vh.cantidad unidades,
        vh.subTotal,
        vh.descuento,
        vh.total
        from ventas_header vh, canal c, clientes cli, medio_pago mp
        where vh.id_canal = c.id
        and vh.id_cliente = cli.id
        and vh.id_medio_pago = mp.id
        and vh.id = :id
        order by vh.fecha desc;";
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':id' => $id));
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

  public function getCandidatasConciliacion($desde, $hasta, $idCuenta){
    try {
        $sql = "SELECT vh.id, vh.fecha, vh.total monto,
                CONCAT('Venta #', vh.id, ' - ', COALESCE(CONCAT(cli.apellido, ' ', cli.nombre), '')) descripcion,
                'Venta' tipo,
                COALESCE(CONCAT(cli.apellido, ' ', cli.nombre), '') extra
                FROM ventas_header vh
                LEFT JOIN medio_pago mp ON mp.id = vh.id_medio_pago
                LEFT JOIN clientes cli ON cli.id = vh.id_cliente
                WHERE vh.fecha >= :desde
                  AND vh.fecha < :hasta
                  AND mp.id_cuenta = :cuenta
                  AND NOT EXISTS (
                      SELECT 1
                      FROM cuentas_movimientos cm
                      WHERE cm.origen_tipo = 'venta'
                        AND cm.origen_id = vh.id
                  )
                ORDER BY vh.fecha, vh.id";
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
        $sql = "SELECT vh.id, vh.fecha, vh.total monto,
                CONCAT('Venta #', vh.id, ' - ', COALESCE(CONCAT(cli.apellido, ' ', cli.nombre), '')) descripcion,
                'Venta' tipo,
                COALESCE(CONCAT(cli.apellido, ' ', cli.nombre), '') extra
                FROM ventas_header vh
                LEFT JOIN clientes cli ON cli.id = vh.id_cliente
                WHERE vh.id = :id
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
