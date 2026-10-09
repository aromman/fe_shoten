<?php

require_once 'crud.php';
require_once "logger.php";

class TurnoParcial extends Crud {
  
  const TABLE='turnos_parcial';
  
  public  $pdo;
  public $logger;

  public $montoApertura;
  public $montoCierre;
  public $userName;
  public $updateDate;
  public $fecha;
  public $idCanal;

  public $monto10;
  public $monto20;
  public $monto50;
  public $monto100;
  public $monto200;
  public $monto500;
  public $monto1000;
  public $monto2000;
  public $monto10000;
  public $monto20000;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  /** Create */
  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (fecha, monto_apertura, monto_cierre, id_canal,username,updateDate,monto10,monto20,monto50,monto100,monto200,monto500,monto1000,monto2000,monto10000,monto20000) 
                                          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->fecha,
        $this->montoApertura,
        $this->montoCierre,
        $this->idCanal,
        $this->userName,
        $this->updateDate,
        $this->monto10,
        $this->monto20,
        $this->monto50,
        $this->monto100,
        $this->monto200,
        $this->monto500,
        $this->monto1000,
        $this->monto2000,
        $this->monto10000,
        $this->monto20000
        ));
        return $this->pdo->lastInsertId();
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  /** update */
  public function update(){
  }

  public function getDetailsActive($fechaCaja, $id_canal){
    try {
        $sql = "select *
        from ".self::TABLE."
        where
        fecha >= '".$fechaCaja."'
        and id_canal = ".$id_canal."
        order by updateDate desc;";
      
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

  public function getDetailLastActive($userName = null){
    try {
        $sql = "select * from turnos_parcial" ;
        if (!is_null($userName)){
          $sql = $sql . " where username = '".$userName."'";
        }
        $sql = $sql . " order by updatedate desc limit 1";
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

  public function getArqueoConciliacion($fechaMovimiento, $idCanal = 0){
    try {
      $params = array(':fechaMovimiento' => $fechaMovimiento);
      $filtroCanal = '';
      $filtroCanalSubconsulta = '';
      if ((int) $idCanal > 0) {
        $filtroCanal = 'AND tp.id_canal = :canal';
        $filtroCanalSubconsulta = 'AND tp2.id_canal = :canal';
        $params[':canal'] = (int) $idCanal;
      }

      $sql = "SELECT
                tp.id,
                tp.fecha,
                tp.monto_cierre monto,
                CONCAT('Arqueo / Cierre #', tp.id) descripcion,
                'Arqueo' tipo,
                'Caja' extra
              FROM ".self::TABLE." tp
              WHERE tp.fecha = (
                  SELECT MAX(tp2.fecha)
                  FROM ".self::TABLE." tp2
                  WHERE tp2.fecha <= :fechaMovimiento
                  {$filtroCanalSubconsulta}
              )
                {$filtroCanal}
              ORDER BY tp.updateDate DESC, tp.id DESC
              LIMIT 1";
      $this->logger->log(__FILE__, $sql, $this->logger::DEBUG);
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
      return array();
    }
  }

  public function getDetalleConciliacionById($id){
    try {
      $sql = "SELECT
                tp.id,
                tp.fecha,
                tp.monto_cierre monto,
                CONCAT('Arqueo / Cierre #', tp.id) descripcion,
                'Arqueo' tipo,
                'Caja' extra
              FROM ".self::TABLE." tp
              WHERE tp.id = :id
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
