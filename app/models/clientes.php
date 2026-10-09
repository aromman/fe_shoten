<?php
require_once 'crud.php';
class Cliente extends Crud
{
  private $id;
  const TABLE='clientes';
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

  public function getAllActiveForSales($orderBy){
    try
    {
        $allowedOrderBy = array(
          'id' => 'id',
          'apellido' => 'apellido',
          'nombre' => 'nombre',
          'apellido, nombre ASC' => 'apellido, nombre ASC',
          'apellido ASC, nombre ASC' => 'apellido ASC, nombre ASC'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $stm = $this->pdo->prepare("SELECT * FROM clientes where solo_contacto = False ORDER BY ".$allowedOrderBy[$orderBy]);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
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
}

 ?>
