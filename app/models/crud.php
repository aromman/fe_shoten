<?php
require_once 'connection.php';
abstract class Crud extends Connection{
    private $table;
    public $pdo;

    public function __construct($table) {
        $this->table=(string) $table;
        $this->pdo=parent::conexion();
    }

    public function __destruct(){
    }

    public function getAll($orderBy){
        try
        {
            $sql = "SELECT * FROM $this->table ORDER BY $orderBy";
            //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
            $stm = $this->pdo->prepare($sql);
            $stm->execute();
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
            die($e->getMessage());
        }
    }
    
    public function getById($id){
        try
        {
            $stm = $this->pdo->prepare("SELECT * FROM $this->table WHERE id=?");
            $stm->execute(array($id));
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e){
            error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
            die($e->getMessage());
        }
    }
    
    public function delete($id){
        try
        {
            $stm = $this->pdo->prepare("DELETE FROM $this->table WHERE id=?");
            $stm->execute(array($id));
        }
        catch (PDOException $e)
        {
            error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
            die($e->getMessage());
        }
    }    

    abstract function create();
    abstract function update();
}