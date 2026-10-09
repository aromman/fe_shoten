<?php

require_once 'crud.php';
class Reserva extends Crud
{
  
  const TABLE='reservas';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  private function getAllowedOrderBy($orderBy){
    $allowedOrderBy = array(
      'id' => 'id',
      'fecha' => 'fecha',
      'fecha ASC' => 'fecha ASC',
      'fecha DESC' => 'fecha DESC',
      'fecha, hora, cliente ASC' => 'fecha, hora, cliente ASC',
      'fecha desc, hora desc, cliente ASC' => 'fecha desc, hora desc, cliente ASC'
    );
    if (!isset($allowedOrderBy[$orderBy])) {
      throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
    }

    return $allowedOrderBy[$orderBy];
  }

  public function getAllActive($orderBy){
    try
    {
        $sql = "SELECT * FROM reservas WHERE id_estado = 2 AND fecha >= CURDATE() ORDER BY ".$this->getAllowedOrderBy($orderBy).";";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
   }

  public function getAllVigentes($orderBy){
    try
    {
        $sql = "SELECT * FROM reservas WHERE fecha >= CURDATE() ORDER BY ".$this->getAllowedOrderBy($orderBy).";";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  public function getAllLastWeek($orderBy){
    try
    {
        $sql = "SELECT * FROM reservas WHERE fecha <= CURDATE() and fecha >= CURRENT_DATE - INTERVAL 1 WEEK ORDER BY ".$this->getAllowedOrderBy($orderBy).";";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  public function getAllInCurse($orderBy){
    try
    {
        $sql = "SELECT * FROM reservas WHERE id_estado in (3,4) and fecha >= CURDATE() ORDER BY ".$this->getAllowedOrderBy($orderBy).";";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetchAll(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  


  public function create(){}

  public function update(){}
}

