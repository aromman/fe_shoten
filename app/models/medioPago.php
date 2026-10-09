<?php
require_once 'crud.php';
class MedioPago extends Crud
{
  private $id;
  const TABLE='medio_pago';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  public function __set($name,$value){
    $this->$name=$value;
  }
  public function __get($name){
    return $this->$name;
  }

/*Aquí Insertamos un animal, tenemos que crear forzosamente este método porque en el CRUD lo agregamos como **abstract** sino lo agregamos obtendremos un error.*/
  public function create(){
    /*
    try{
        $stm=$this->pdo->prepare("INSERT INTO ".self::TABLE." (name, specie,  breed, gender, color, age) VALUES (?,?,?,?,?,?)");
        $stm->execute(array($this->name,$this->specie,$this->breed,$this->gender,$this->color,$this->age));
    }catch(PDOException $e){
        echo $e->getMessage();
    }
    */
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

  public function getAllByCanal($canal,$orderBy){
    try
    {
        $allowedOrderBy = array(
          'id' => 'mp.id',
          'nombre' => 'mp.nombre',
          'nombre ASC' => 'mp.nombre ASC',
          'nombre DESC' => 'mp.nombre DESC'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $stm = $this->pdo->prepare("select mp.* from medio_pago mp, cuentas_canales cc where mp.id_cuenta = cc.id_cuenta and cc.id_canal = :canal ORDER BY ".$allowedOrderBy[$orderBy].";");
        $stm->execute(array(':canal' => $canal));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
    }
  }

  public function getByCuenta($idCuenta){
    try
    {
        $stm = $this->pdo->prepare("select * from medio_pago where id_cuenta = :idCuenta limit 1");
        $stm->execute(array(':idCuenta' => $idCuenta));
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        return null;
    }
  }


}

 ?>
