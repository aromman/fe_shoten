<?php

require_once "constantes.php";
class Connection{
    private $driver;
    private $host;
    private $user;
    private $pass;
    private $dbName;
    private $charset;
  
    # Create a singleton to store the connection for reuse
    private static $singleton, $con;
    # save connection to singleton and return itself (the full object)
    public function __construct(){

        # If your singleton is not set
        if(!isset(self::$singleton))
            # assign it this class
            self::$singleton = $this;
        # return this class
        return self::$singleton;
    }
    
    public function __destruct() {
        //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). " Cerrando conexion] ", 3, "my-errors.log");      
    }

    protected function conexion()
    {
        if(self::$con instanceof \PDO){
            try {
                //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). " Testeando conexion] ", 3, "my-errors.log");      
                self::$con->query("SELECT 1");
            } catch (PDOException $e) {
                //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
                self::init();
            }
            
            //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). " Devolviendo conexion] ", 3, "my-errors.log");      
            return self::$con;
        } else {
            self::init();
        }

    }    

    protected function init()
    {
        try{
            //error_log(PHP_EOL."[".date('d.m.Y h:i:s'). " Creando Nueva conexion] ", 3, "my-errors.log");      
            $this->driver = DB_DRIVER;
            $this->host = DB_HOST;
            $this->user = DB_USER;
            $this->pass = DB_PASS;
            $this->dbName = DB_NAME;
            $this->charset = DB_CHARSET;

            self::$con=new PDO("{$this->driver}:host={$this->host};dbname={$this->dbName};charset={$this->charset}", $this->user, $this->pass);
            self::$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$con->setAttribute(PDO::ATTR_PERSISTENT, true);
            return self::$con;
        } catch (PDOException $e){
            error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
            die($e->getMessage());
        }
    }    

}
?>