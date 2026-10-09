<?php
require_once 'crud.php';
class ProductoUbicacion extends Crud
{
  private $id;
  const TABLE='productos_ubicacion';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  public function getLabelById($id){
    try
    {
        $sql = "select 
                concat(c.nombre, ' - ', put1.nombre, ' ', pu.nivel_codigo_01, ' - ', put2.nombre, ' ', pu.nivel_codigo_02 ) ubicacion
                from productos_ubicacion pu, 
                canal c,
                productos_ubicacion_tipo put1,
                productos_ubicacion_tipo put2
                where pu.id = $id
                and pu.id_canal = c.id
                and pu.id_nivel_tipo_01 = put1.id
                and pu.id_nivel_tipo_02 = put2.id";
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