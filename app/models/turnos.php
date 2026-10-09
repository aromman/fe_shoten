<?php
require_once 'crud.php';
require_once "logger.php";
class Turnos extends Crud {
  
  const TABLE='turnos';
  public $pdo;
  public $logger;
  
  public $id;
  public $montoApertura;
  public $montoCierre;
  public $userName;
  public $updateDate;
  public $fecha;
  public $idCanal;
  public $estado;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function getActiveByCanal($canal){
    try {
        $sql = "SELECT * FROM turnos where estado = 'A' and id_canal = $canal order by fecha desc limit 1;";
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

  public function getLastCloseByCanal($canal){
    try  {
        $sql = "SELECT * FROM turnos where estado = 'C' and id_canal = $canal order by fecha desc limit 1;";
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

  public function getNombreEstado($idEstado){
    if ($idEstado == "A")
        return "Abierto";

    if ($idEstado == "C")
        return "Cerrado";

    return $idEstado;
  }

  public function getAllLastDays($days, $orderBy){
    try {
        $sql = "SELECT
        t.*,
        c.nombre canal
        FROM turnos t, canal c
        WHERE t.id_canal = c.id
        and t.fecha >= (CURRENT_DATE - INTERVAL $days DAY)
        ORDER BY $orderBy;";
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

  public function getCierresConciliacion($fechaMovimiento, $idCanal = 0){
    try {
        $sql = "SELECT
                  t.id,
                  t.fecha,
                  COALESCE(t.monto_cierre, 0) monto,
                  CONCAT('Cierre de turno #', t.id) descripcion,
                  'C' tipo
                FROM turnos t
                WHERE t.estado = 'C'
                  AND DATE(t.fecha) = :fechaMovimiento";

        $params = array(':fechaMovimiento' => $fechaMovimiento);
        if ($idCanal > 0) {
            $sql .= " AND t.id_canal = :idCanal";
            $params[':idCanal'] = $idCanal;
        }

        $sql .= " ORDER BY t.fecha DESC, t.id DESC";

        $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
        $stm = $this->pdo->prepare($sql);
        $stm->execute($params);
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        return null;
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      die($e->getMessage());
    }
  }

  public function getDetailsActive($fechaCaja, $id_canal){
    try {
        $sql = "select COALESCE(vh.updateDate, v.fecha) fecha, concat('Venta-', v.id) detalle, v.total monto, 'I' tipo
        from ventas v
        left join ventas_header vh on vh.id = v.id_ventas_header
        where
        v.fecha >= '".$fechaCaja."'
        and v.id_medio_pago = 1
        and v.id_canal = ".$id_canal."
        union all
        SELECT
        g.fecha,
        gc.nombre detalle,
        g.monto monto,
        'E' tipo
        FROM gastos g, gastos_clase gc
        where g.fecha >=  '".$fechaCaja."'
        and gc.id = g.id_clase
        and g.tipo = 'F'
        and g.id_medio_pago = 1
        and g.id_canal = ".$id_canal."
        union all
        select c.fecha, concat('Compra-', c.id) detalle, (c.cantidad * c.precio_costo) monto, 'E' tipo
        from compras c
        where
        c.fecha >= '".$fechaCaja."'
        and c.id_medio_pago = 1
        union all
        select
        cm.fecha,
        cm.descripcion detalle,
        cm.monto,
        'E' tipo
        from cuentas_movimientos cm where cm.id_cuenta = 1 and cm.fecha >= '".$fechaCaja."'  and cm.id_causal in (3,5) and cm.tipo_movimiento = 'D' and cm.id_canal = ".$id_canal."
        UNION ALL
        select
        cm.fecha,
        cm.descripcion detalle,
        cm.monto,
        'I' tipo
        from cuentas_movimientos cm where cm.id_cuenta = 1 and cm.fecha >= '".$fechaCaja."'  and cm.id_causal in (3,5) and cm.tipo_movimiento = 'C' and cm.id_canal = ".$id_canal."
        order by 1,4 DESC, 2;";
      
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

  public function cerrar(){
    try{
      $params = array();
      $sql = "";
      if (isset($this->montoCierre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'monto_cierre = :montoCierre';
        $params[':montoCierre'] = $this->montoCierre;
      }
      if (isset($this->userName)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'username = :userName';
        $params[':userName'] = $this->userName;
      }
      if (isset($this->updateDate)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'updateDate = :updateDate';
        $params[':updateDate'] = $this->updateDate;
      }

      if (!empty($sql)){

        $params[':fecha'] = $this->fecha;
        $params[':idCanal'] = $this->idCanal;
        
        $sqlUpdate = "UPDATE ".self::TABLE." SET estado='C',".$sql." WHERE fecha=:fecha and id_canal=:idCanal";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function create(){
    try{

      $sql = "INSERT INTO ".self::TABLE." (fecha, monto_apertura, monto_cierre, estado,id_canal,username,updateDate) VALUES (?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->montoApertura,
        $this->montoCierre,
        $this->estado,
        $this->idCanal,
        $this->userName,
        $this->updateDate
        ));
        return $this->pdo->lastInsertId();
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  /** update */
  public function update(){
    try{
      $params = array();
      $sql = "";
      if (isset($this->montoCierre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'monto_cierre = :montoCierre';
        $params[':montoCierre'] = $this->montoCierre;
      }
      if (isset($this->userName)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'username = :userName';
        $params[':userName'] = $this->userName;
      }
      if (isset($this->updateDate)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'updateDate = :updateDate';
        $params[':updateDate'] = $this->updateDate;
      }
      if (isset($this->estado)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'estado = :estado';
        $params[':estado'] = $this->estado;
      }

      if (!empty($sql)){

        $params[':id'] = $this->id;
        
        $sqlUpdate = "UPDATE ".self::TABLE." SET estado='C',".$sql." WHERE id=:id";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }

  }

    public function delete($id){
        try
        {
            $stm = $this->pdo->prepare("DELETE FROM ".self::TABLE." WHERE id=?");
            $stm->execute(array($id));

            // busco ultimo cerrado 
            $sqlOpen = "UPDATE ".self::TABLE." SET estado='A' WHERE estado='C' and fecha = (select max(t1.fecha) from turnos t1 where t1.estado = 'C')";
            $stm=$this->pdo->prepare($sqlOpen);
            $stm->execute();

        } catch (PDOException $e) {
            $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
            die($e->getMessage());
        }
    }    


}
