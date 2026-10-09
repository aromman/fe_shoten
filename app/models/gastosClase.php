<?php
require_once 'crud.php';
class GastosClase extends Crud
{
  private $id;
  const TABLE='gastos_clase';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
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