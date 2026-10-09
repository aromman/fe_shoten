<?php
require_once 'crud.php';
require_once "logger.php";
class CuentasMovimientos extends Crud
{
  const TABLE='cuentas_movimientos';
  public $pdo;
  public $logger;
  
  public $id;
  public $idCuenta;
  public $fecha;
  public $tipoMovimiento;
  public $descripcion;
  public $monto;
  public $consolidado;
  public $idCausal;
  public $idCanal;
  public $origenTipo;
  public $origenId;
  public $logContext;
  public $clearOrigenTipo = false;
  public $clearOrigenId = false;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function setId($id){
    $this->id = $id;
  }
  public function setIdCuenta($idCuenta){
    $this->idCuenta = $idCuenta;
  }
  public function setFecha($fecha){
    $this->fecha = $fecha;
  }
  public function setTipoMovimiento($tipoMovimiento){
    $this->tipoMovimiento = $tipoMovimiento;
  }
  public function setDescripcion($descripcion){
    $this->descripcion = $descripcion;
  }
  public function setMonto($monto){
    $this->monto = $monto;
  }
  public function setConsolidado($consolidado){
    if (is_string($consolidado)) {
      $valor = strtoupper(trim($consolidado));
      $this->consolidado = in_array($valor, array('1', 'TRUE', 'SI', 'S', 'YES', 'Y'), true) ? 1 : 0;
      return;
    }

    $this->consolidado = !empty($consolidado) ? 1 : 0;
  }
  public function setIdCausal($idCausal){
    $this->idCausal = $idCausal;
  }
  public function setIdCanal($idCanal){
    $this->idCanal = $idCanal;
  }
  public function setOrigenTipo($origenTipo){
    $this->clearOrigenTipo = false;
    $this->origenTipo = $origenTipo;
  }
  public function setOrigenId($origenId){
    $this->clearOrigenId = false;
    $this->origenId = $origenId;
  }
  public function clearOrigenTipo(){
    $this->clearOrigenTipo = true;
    $this->origenTipo = null;
  }
  public function clearOrigenId(){
    $this->clearOrigenId = true;
    $this->origenId = null;
  }
  public function setLogContext($logContext){
    $this->logContext = $logContext;
  }
  public function getTotalByCanalDateAndType($canal, $date, $type){
    try
    {
        $sql = "select sum(cm.monto) monto
                from cuentas_movimientos cm
                where cm.id_cuenta = 1
                and cm.id_canal = :canal
                and cm.fecha >= :date
                and cm.id_causal in (3,5)
                and cm.tipo_movimiento = :type;";
        // recupera Transferencias y Cafeteria        
        $this->logger->log(__FILE__,$sql,$this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':canal' => $canal, ':date' => $date, ':type' => $type));
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

  public function getSaldoPosteriorMovimiento($idCuenta, $fechaBase, $idMovimiento, $tipoMovimiento, $montoMovimiento){
    try
    {
        $sql = "SELECT COALESCE(SUM(
                    CASE
                        WHEN UPPER(COALESCE(tipo_movimiento, '')) = 'D' THEN -COALESCE(monto, 0)
                        ELSE COALESCE(monto, 0)
                    END
                ), 0) saldo
                FROM cuentas_movimientos
                WHERE id_cuenta = :cuenta
                  AND (
                        fecha < :fechaBase
                        OR (fecha = :fechaBase AND id < :idMovimiento)
                  )";
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':cuenta' => $idCuenta,
          ':fechaBase' => $fechaBase,
          ':idMovimiento' => $idMovimiento,
        ));

        $rowSaldo = $stm->fetch(PDO::FETCH_ASSOC);
        $saldoAntes = (is_array($rowSaldo) && isset($rowSaldo['saldo'])) ? (float) $rowSaldo['saldo'] : 0.0;
        $montoMovimiento = (float) $montoMovimiento;
        $saldoPosterior = $saldoAntes + (strtoupper(trim((string) $tipoMovimiento)) === 'D' ? -$montoMovimiento : $montoMovimiento);

        return array(
          'saldo_antes' => $saldoAntes,
          'saldo_posterior' => $saldoPosterior,
        );
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        die($e->getMessage());
    }
  }


/*Aquí Insertamos un animal, tenemos que crear forzosamente este método porque en el CRUD lo agregamos como **abstract** sino lo agregamos obtendremos un error.*/
  public function getSaldoHastaFecha($idCuenta, $fechaHasta){
    try {
        $sql = "SELECT
                    COALESCE(SUM(
                        CASE
                            WHEN UPPER(COALESCE(tipo_movimiento, '')) = 'D' THEN -COALESCE(monto, 0)
                            ELSE COALESCE(monto, 0)
                        END
                    ), 0) saldo
                FROM cuentas_movimientos
                WHERE id_cuenta = :cuenta
                  AND fecha < :fecha
                  AND COALESCE(es_referencia, 0) = 0";
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':cuenta' => (int) $idCuenta,
          ':fecha' => (string) $fechaHasta . ' 23:59:59',
        ));
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return isset($row['saldo']) ? (float) $row['saldo'] : 0.0;
    } catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return 0.0;
    }
  }

  public function getTransferenciasCandidatasConciliacion($desde, $hasta, $idCuenta, $tipoMovimiento){
    try {
        $sql = "SELECT cm.id, cm.fecha, cm.monto, cm.descripcion, cm.tipo_movimiento tipo, CONCAT(c.nombre, '') extra
                FROM cuentas_movimientos cm
                LEFT JOIN cuentas c ON c.id = cm.id_cuenta
                WHERE cm.fecha >= :desde
                  AND cm.fecha < :hasta
                  AND cm.id_cuenta <> :cuenta
                  AND cm.id_causal = 3
                  AND cm.tipo_movimiento <> :tipoMovimiento
                  AND NOT EXISTS (
                      SELECT 1
                      FROM cuentas_movimientos cmx
                      WHERE cmx.origen_tipo = 'transferencia'
                        AND cmx.origen_id = cm.id
                  )
                ORDER BY cm.fecha, cm.id";
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':desde' => $desde,
          ':hasta' => $hasta,
          ':cuenta' => $idCuenta,
          ':tipoMovimiento' => $tipoMovimiento,
        ));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return array();
    }
  }

  public function getConciliacionDetalleById($id){
    try {
        $sql = "SELECT cm.id, cm.fecha, cm.monto, cm.descripcion,
                       cm.tipo_movimiento tipo,
                       c.nombre extra,
                       cm.origen_tipo,
                       cm.origen_id
                FROM cuentas_movimientos cm
                LEFT JOIN cuentas c ON c.id = cm.id_cuenta
                WHERE cm.id = :id
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

  public function getTransferenciaConciliacionFallback($idCuenta, $fecha, $monto){
    try {
        $sql = "SELECT cm.id, cm.fecha, cm.monto, cm.descripcion,
                       cm.tipo_movimiento tipo,
                       c.nombre extra,
                       cm.origen_tipo,
                       cm.origen_id
                FROM cuentas_movimientos cm
                LEFT JOIN cuentas c ON c.id = cm.id_cuenta
                WHERE cm.id_causal = 3
                  AND cm.id_cuenta <> :cuenta
                  AND cm.fecha = :fecha
                  AND cm.monto = :monto
                ORDER BY cm.id DESC
                LIMIT 1";
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':cuenta' => (int) $idCuenta,
          ':fecha' => (string) $fecha,
          ':monto' => (float) $monto,
        ));
        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        return !empty($rows) ? $rows[0] : null;
    } catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return null;
    }
  }

  public function ejecutarConsulta($sql, $params = array()){
    try {
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return array();
    }
  }

  public function create(){
    try{
      $this->logger->log(__FILE__, sprintf(
        'INSERT cuentas_movimientos context=%s id_canal=%s id_cuenta=%s fecha=%s tipo=%s descripcion=%s monto=%s consolidado=%s id_causal=%s origen_tipo=%s origen_id=%s',
        var_export($this->logContext, true),
        var_export($this->idCanal, true),
        var_export($this->idCuenta, true),
        var_export($this->fecha, true),
        var_export($this->tipoMovimiento, true),
        var_export($this->descripcion, true),
        var_export($this->monto, true),
        var_export($this->consolidado, true),
        var_export($this->idCausal, true),
        var_export($this->origenTipo, true),
        var_export($this->origenId, true)
      ), $this->logger::DEBUG);
      $stm=$this->pdo->prepare("INSERT INTO ".self::TABLE." (id_canal, id_cuenta, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal, origen_tipo, origen_id) VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stm->execute(array(
        $this->idCanal,
        $this->idCuenta,
        $this->fecha,
        $this->tipoMovimiento,
        $this->descripcion,
        $this->monto,
        $this->consolidado,
        $this->idCausal,
        $this->origenTipo,
        $this->origenId
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

      if (isset($this->idCuenta)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_cuenta = :idCuenta';
        $params[':idCuenta'] = $this->idCuenta;
      }
      if (isset($this->fecha)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fecha = :fecha';
        $params[':fecha'] = $this->fecha;
      }
      if (isset($this->tipoMovimiento)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'tipo_movimiento = :tipoMovimiento';
        $params[':tipoMovimiento'] = $this->tipoMovimiento;
      }
      if (isset($this->descripcion)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'descripcion = :descripcion';
        $params[':descripcion'] = $this->descripcion;
      }
      if (isset($this->monto)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'monto = :monto';
        $params[':monto'] = $this->monto;
      }
      if (isset($this->consolidado)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'consolidado = :consolidado';
        $params[':consolidado'] = $this->consolidado;
      }
      if (isset($this->idCausal)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_causal = :idCausal';
        $params[':idCausal'] = $this->idCausal;
      }
      if (isset($this->idCanal)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_canal = :idCanal';
        $params[':idCanal'] = $this->idCanal;
      }
      if (isset($this->origenTipo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen_tipo = :origenTipo';
        $params[':origenTipo'] = $this->origenTipo;
      }
      if (isset($this->origenId)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen_id = :origenId';
        $params[':origenId'] = $this->origenId;
      }
      if ($this->clearOrigenTipo === true){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen_tipo = NULL';
      }
      if ($this->clearOrigenId === true){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen_id = NULL';
      }
      if (!empty($sql)){
        $params[':id'] = $this->id;
        $sqlUpdate = "UPDATE ".self::TABLE." SET ".$sql." WHERE id=:id";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $this->logger->log(__FILE__, sprintf(
          'UPDATE cuentas_movimientos id=%s data=%s',
          var_export($this->id, true),
          json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ), $this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  
  }

  public function consolidarHastaFecha($idCuenta){
    try {
        $sql = "UPDATE ".self::TABLE."
                SET consolidado = 1
                WHERE id_cuenta = :idCuenta
                  AND consolidado <> 1";
        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':idCuenta' => (int) $idCuenta,
        ));
        return $stm->rowCount();
    } catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
        return 0;
    }
  }

  public function getAllByCausal($idCausal){
    try
    {
        $sql = "SELECT * FROM ".self::TABLE." WHERE id_causal = :idCausal";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':idCausal' => $idCausal));
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

  public function getAllByCausalCanalAndDate($idCausal, $canal, $fechaDesde){
    try
    {
        $sql = "SELECT * FROM ".self::TABLE."
                WHERE id_cuenta = 1
                AND id_causal = :idCausal
                AND id_canal = :canal
                AND fecha >= :fechaDesde
                ORDER BY fecha";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(
          ':idCausal' => $idCausal,
          ':canal' => $canal,
          ':fechaDesde' => $fechaDesde,
        ));
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
