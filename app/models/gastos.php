<?php
require_once 'crud.php';
require_once "logger.php";

class Gastos extends Crud
{
  private $id;
  const TABLE='gastos';
  public $pdo;
  public $logger;
  public $fecha;
  public $detalle;
  public $monto;
  public $tipo;
  public $idClase;
  public $idMedioPago;
  public $idCanal;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (fecha, detalle, monto, tipo, id_clase, id_medio_pago, id_canal) VALUES (?,?,?,?,?,?,?)";
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->detalle,
        $this->monto,
        $this->tipo,
        $this->idClase,
        $this->idMedioPago,
        $this->idCanal,
      ));
      return $this->pdo->lastInsertId();
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }
  public function update(){}


  public function getTotalByCanalAndDate($canal, $date){
    try
    {
        $sql = "SELECT sum(g.monto) monto FROM gastos g, gastos_clase gc where g.fecha >= :date and gc.id = g.id_clase and g.tipo = 'F' and g.id_medio_pago = 1 and g.id_canal = :canal;";
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

  public function getTotalByClaseAndDate($clase, $date){
    try
    {
        $sql = "SELECT sum(g.monto) monto FROM gastos g where g.fecha > :date and id_clase = :clase;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':date' => $date, ':clase' => $clase));
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

  public function getTotalPresupuesto(){
    try
    {
        $sql = "SELECT sum(gc.presupuesto) monto FROM gastos_clase gc";
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

  public function getTotalMonth(){
  try
  {
      $sql = "SELECT sum(monto) monto 
              FROM gastos
              where 
              YEAR(fecha) = YEAR(CURRENT_DATE()) 
              AND MONTH(fecha) = MONTH(CURRENT_DATE())
      ";
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

  public function getTotalByCanal($canal){
    try
    {
        $sql = "SELECT sum(g.monto) monto 
        FROM gastos g, gastos_clase gc 
        where YEAR(g.fecha) = YEAR(CURRENT_DATE())
        AND MONTH(g.fecha) = MONTH(CURRENT_DATE())
        and gc.id = g.id_clase and g.tipo = 'F' 
        and g.id_canal = :canal;";
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':canal' => $canal));
        if ($stm->rowCount() > 0) {
            $rs =  $stm->fetch(PDO::FETCH_ASSOC);
            return $rs['monto'];
        } else {
          return 0;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }

  public function getTotalMesActual(){
    try
    {
        $sql = "SELECT COALESCE(SUM(monto), 0) monto
                FROM gastos
                WHERE fecha >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
                  AND fecha < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? (float) $row['monto'] : 0;
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return 0;
    }
  }

  public function getResumenPorClaseCanalPeriodo($canal, $inicio, $fin){
    try
    {
        $sql = "SELECT gc.nombre clase, COALESCE(SUM(g.monto), 0) monto
                FROM gastos g
                JOIN gastos_clase gc ON gc.id = g.id_clase
                WHERE g.id_canal = :canal
                  AND g.fecha >= :inicio
                  AND g.fecha < :fin
                GROUP BY gc.nombre
                ORDER BY monto DESC";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':canal' => $canal, ':inicio' => $inicio, ':fin' => $fin));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return array();
    }
  }

  public function getCandidatasConciliacion($desde, $hasta, $idCuenta){
    try {
      $sql = "SELECT g.id, g.fecha, g.monto monto,
              CONCAT(gc.nombre, ' - ', COALESCE(g.detalle, '')) descripcion,
              'Gasto' tipo,
              gc.nombre extra
              FROM gastos g
              LEFT JOIN gastos_clase gc ON gc.id = g.id_clase
              LEFT JOIN medio_pago mp ON mp.id = g.id_medio_pago
              WHERE g.fecha >= :desde
                AND g.fecha < :hasta
                AND g.tipo = 'F'
                AND (mp.id_cuenta = :cuenta OR g.id_medio_pago = 1)
                AND NOT EXISTS (
                    SELECT 1
                    FROM cuentas_movimientos cm
                    WHERE cm.origen_tipo = 'gasto'
                      AND cm.origen_id = g.id
                )
              ORDER BY g.fecha, g.id";
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
      $sql = "SELECT g.id, g.fecha, g.monto,
              CONCAT(COALESCE(gc.nombre, ''), ' - ', COALESCE(g.detalle, '')) descripcion,
              'Gasto' tipo,
              COALESCE(gc.nombre, '') extra
              FROM gastos g
              LEFT JOIN gastos_clase gc ON gc.id = g.id_clase
              WHERE g.id = :id
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

 ?>
