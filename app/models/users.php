<?php

require_once 'crud.php';
require_once "logger.php";
class User extends Crud
{
  
  const TABLE='users';
  
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public $id;
  public $usuario;
  public $email;
  public $apellido;
  public $nombre;
  public $idRol;
  public $idEmpleado;
  public $password;
  
  public function getByUserName($userName){
    try
    {
        $stm = $this->pdo->prepare("SELECT u.id, u.username, u.password, u.id_rol, u.id_empleado, e.apellido, e.nombre FROM users u left join empleados e on u.id_empleado = e.id WHERE u.username = :userName;");
        $stm->execute(array(':userName' => $userName));
        if ($stm->rowCount() > 0) {
          return $stm->fetch(PDO::FETCH_ASSOC);
        } else {
          return null;
        }
    }
    catch (PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (username, email, id_rol, id_empleado, password) VALUES (?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        $this->usuario,
        $this->email,
        $this->idRol,
        $this->idEmpleado,
        $this->password
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
      if (isset($this->usuario)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'username = :usuario';
        $params[':usuario'] = $this->usuario;
      }
      if (isset($this->email)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'email = :email';
        $params[':email'] = $this->email;
      }
      if (isset($this->idRol)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_rol = :idRol';
        $params[':idRol'] = $this->idRol;
      }
      if (isset($this->idEmpleado)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'id_empleado = :idEmpleado';
        $params[':idEmpleado'] = $this->idEmpleado;
      }
      if (isset($this->password)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'password = :password';
        $params[':password'] = $this->password;
      }

      if (!empty($sql)){

        $params[':id'] = $this->id;

        $sqlUpdate = "UPDATE ".self::TABLE." SET ".$sql." WHERE id=:id";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }

  }

  public function getAll($orderBy){
        try
        {
            $sql = "SELECT 
                    u.*,
                    e.apellido,
                    e.nombre,
                    r.rol
                    FROM users u 
                    left join empleados e on u.id_empleado = e.id
                    left join roles r on u.id_rol = r.id;";
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
            die($e->getMessage());
        }
    }

    public function getById($id){
        try
        {
            $stm = $this->pdo->prepare("SELECT 
                    u.*,
                    e.apellido,
                    e.nombre,
                    r.id id_rol,
                    r.rol
                    FROM users u 
                    left join empleados e on u.id_empleado = e.id
                    left join roles r on u.id_rol = r.id
                    where u.id = :id;");
            $stm->execute(array(':id' => $id));
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
            die($e->getMessage());
        }
    }

}
