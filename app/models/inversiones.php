<?php
require_once 'crud.php';
class Inversiones extends Crud
{
  private $id;
  const TABLE='inversiones';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  public function setFecha($fecha){
    $this->fecha=$fecha;
  }
  public function setDetalle($detalle){
    $this->detalle=$detalle;
  }
  public function setMonto($monto){
    $this->monto=$monto;
  }
  public function setLibros($libros){
    $this->libros=$libros;
  }
  public function setCotizacion($cotizacion){
    $this->cotizacion=$cotizacion;
  }
  public function setInvertidoPor($invertidoPor){
    $this->invertidoPor=$invertidoPor;
  }

  public function getTotal(){
    try
    {
        $sql = "select sum(libros) as total from inversiones;";
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }

    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
}

public function getByInversor($inversor){
  try
  {
      $sql = "select sum(libros) as total from inversiones where id_invertido_por = :inversor";
      $stm = $this->pdo->prepare($sql);
      $stm->execute(array(':inversor' => $inversor));
      if ($stm->rowCount() > 0) {
        return $stm->fetch(PDO::FETCH_ASSOC);
      } else {
        return null;
      }

  }
  catch (PDOException $e){
      error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
      die($e->getMessage());
  }
}

public function getCotizacion(){
  try
  {
      $sql = "select cotizacion from inversiones order by fecha desc limit 1";
      $stm = $this->pdo->prepare($sql);
      $stm->execute();
      if ($stm->rowCount() > 0) {
        return $stm->fetch(PDO::FETCH_ASSOC);
      } else {
        return null;
      }

  }
  catch (PDOException $e){
      error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
      die($e->getMessage());
  }
}


/*Aquí Insertamos un animal, tenemos que crear forzosamente este método porque en el CRUD lo agregamos como **abstract** sino lo agregamos obtendremos un error.*/
  public function create(){

    try{
        $stm=$this->pdo->prepare("INSERT INTO ".self::TABLE." ( fecha, detalle, monto,libros,cotizacion,id_invertido_por) VALUES (?,?,?,?,?,?)");
        $stm->execute(array($this->fecha,$this->detalle,$this->monto,$this->libros,$this->cotizacion,$this->invertidoPor));
    }catch(PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
    }

  }

  public function update(){
    /*
    try{
        $stm=$this->pdo->prepare("UPDATE ".self::TABLE." SET name=?, specie=?, breed=?,gender=?,color=?,age=? WHERE id=?");
        $stm->execute(array($this->name,$this->specie,$this->breed,$this->gender,$this->color,$this->age,$this->id));
    }catch(PDOException $e){
        echo $e->getMessage();
    }
    */
  }
}

 ?>
