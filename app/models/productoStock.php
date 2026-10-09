<?php
require_once 'crud.php';
class ProductoStock extends Crud
{
  private $id;
  const TABLE='productos_stock';
  public  $pdo;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
  }

  public function getByProducto($producto){
    try
    {
        $sql = "select ps.*, 
                e.nombre editorial,
                concat(c.nombre, ' - ', put1.nombre, ' ', pu.nivel_codigo_01, ' - ', put2.nombre, ' ', pu.nivel_codigo_02 )  ubicacion
                from productos_stock ps, editoriales e, productos_ubicacion pu, 
                canal c,
                productos_ubicacion_tipo put1,
                productos_ubicacion_tipo put2
                where ps.id_editorial = e.id
                and ps.id_ubicacion = pu.id
                and pu.id_canal = c.id
                and pu.id_nivel_tipo_01 = put1.id
                and pu.id_nivel_tipo_02 = put2.id
                and ps.id_producto = :producto";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':producto' => $producto));
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
    
  public function getByProductoUbicacion($producto, $ubicacion){
    try
    {
        $sql = "select ps.*, 
                e.nombre editorial,
                concat(c.nombre, ' - ', put1.nombre, ' ', pu.nivel_codigo_01, ' - ', put2.nombre, ' ', pu.nivel_codigo_02 )  ubicacion,
                p.titulo nombre_producto
                from productos_stock ps, editoriales e, productos_ubicacion pu, 
                canal c,
                productos_ubicacion_tipo put1,
                productos_ubicacion_tipo put2,
                productos p
                where ps.id_editorial = e.id
                and ps.id_ubicacion = pu.id
                and pu.id_canal = c.id
                and pu.id_nivel_tipo_01 = put1.id
                and pu.id_nivel_tipo_02 = put2.id
                and ps.id_producto = p.id
                and ps.id_producto = :producto
                and ps.id_ubicacion = :ubicacion";
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");        
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':producto' => $producto, ':ubicacion' => $ubicacion));
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
