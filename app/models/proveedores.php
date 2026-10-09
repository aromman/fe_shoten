<?php
require_once 'crud.php';
require_once "logger.php";
class Proveedor extends Crud{
  
  public $id;
  
  public $origen;
  public $origenId;
  public $nombre;
  public $email;
  public $telefono;
  public $direccion;
  public $fiscalNumber;
  public $comentario;
  public $cronPedido;
  public $cronEntrega;
  public $cantidadMinima;
  public $montoMinimo;
  public $activo;

  const TABLE='proveedores';
  public $pdo;
  public $logger;
  
  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger =  new Logger();
  }

  public function __set($name,$value){
    $this->$name=$value;
  }
  public function __get($name){
    return $this->$name;
  }

  public function create(){
    try{
      $sql = "INSERT INTO ".self::TABLE." (origen,origen_id,nombre,email,telefono,direccion,fiscal_number,comentario,activo)
             VALUES (?,?,?,?,?,?,?,?,?)";
      $stm=$this->pdo->prepare($sql);
      $stm->execute(array(
        isset($this->origen) ? $this->origen : 'local',
        isset($this->origenId) ? $this->origenId : null,
        $this->nombre,
        isset($this->email) ? $this->email : null,
        isset($this->telefono) ? $this->telefono : null,
        isset($this->direccion) ? $this->direccion : null,
        isset($this->fiscalNumber) ? $this->fiscalNumber : null,
        isset($this->comentario) ? $this->comentario : null,
        $this->activo
        ));
        $id = $this->pdo->lastInsertId();
        $this->guardarCalendario($id);
        return $id;
    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function update(){
    try{

      $params = array();
      $sql = "";
      if (isset($this->origen)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen = :origen';
        $params[':origen'] = $this->origen;
      }
      if (isset($this->origenId)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'origen_id = :origenId';
        $params[':origenId'] = $this->origenId;
      }
      if (isset($this->nombre)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'nombre = :nombre';
        $params[':nombre'] = $this->nombre;
      }
      if (isset($this->email)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'email = :email';
        $params[':email'] = $this->email;
      }
      if (isset($this->telefono)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'telefono = :telefono';
        $params[':telefono'] = $this->telefono;
      }
      if (isset($this->direccion)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'direccion = :direccion';
        $params[':direccion'] = $this->direccion;
      }
      if (isset($this->fiscalNumber)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'fiscal_number = :fiscalNumber';
        $params[':fiscalNumber'] = $this->fiscalNumber;
      }
      if (isset($this->comentario)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'comentario = :comentario';
        $params[':comentario'] = $this->comentario;
      }
      if (isset($this->activo)){
        $sql = !empty($sql) ? $sql .= ', ' : $sql;
        $sql .= 'activo = :activo';
        $params[':activo'] = $this->activo;
      }

      if (!empty($sql)){

        $params[':id'] = $this->id;

        $sqlUpdate = "UPDATE ".self::TABLE." SET ".$sql." WHERE id=:id";
        $this->logger->log(__FILE__,$sqlUpdate,$this->logger::DEBUG);
        $stm=$this->pdo->prepare($sqlUpdate);
        $stm->execute($params);
      }

      $this->guardarCalendario($this->id);

    }catch(PDOException $e){
        $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  private function guardarCalendario($idProveedor){
    if (!isset($this->cronPedido) && !isset($this->cronEntrega) && !isset($this->cantidadMinima) && !isset($this->montoMinimo)) {
      return;
    }

    $sql = "INSERT INTO proveedor_calendario
              (proveedor_id, cron_pedido, cron_entrega, cantidad_minima, monto_minimo, activo)
            VALUES
              (:proveedorId, :cronPedido, :cronEntrega, :cantidadMinima, :montoMinimo, 1)
            ON DUPLICATE KEY UPDATE
              cron_pedido = VALUES(cron_pedido),
              cron_entrega = VALUES(cron_entrega),
              cantidad_minima = VALUES(cantidad_minima),
              monto_minimo = VALUES(monto_minimo),
              activo = VALUES(activo)";
    $stm = $this->pdo->prepare($sql);
    $stm->execute(array(
      ':proveedorId' => $idProveedor,
      ':cronPedido' => isset($this->cronPedido) ? $this->cronPedido : null,
      ':cronEntrega' => isset($this->cronEntrega) ? $this->cronEntrega : null,
      ':cantidadMinima' => isset($this->cantidadMinima) ? $this->cantidadMinima : 0,
      ':montoMinimo' => isset($this->montoMinimo) ? $this->montoMinimo : 0,
    ));
  }

  private function selectConCalendario(){
    return "p.*,
            pc.cron_pedido,
            pc.cron_entrega,
            COALESCE(pc.cantidad_minima, 0) cantidad_minima,
            COALESCE(pc.monto_minimo, 0) monto_minimo";
  }

  private function getAllFiltrado($orderBy, $soloLocales, $soloActivos){
    try
    {
        $sql = "select ".$this->selectConCalendario()."
                from proveedores p
                left join proveedor_calendario pc on pc.proveedor_id = p.id and pc.activo = 1
                where 1 = 1";
        if ($soloLocales) {
          $sql .= " and p.origen = 'local'";
        }
        if ($soloActivos) {
          $sql .= " and p.activo = true";
        }
        $sql .= " order by ".$orderBy;
        $stm = $this->pdo->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAll($orderBy){
    return $this->getAllFiltrado($orderBy, true, false);
  }

  public function getAllParaCalendario($orderBy){
    return $this->getAllFiltrado($orderBy, false, false);
  }

  private function getByIdFiltrado($id, $soloLocales){
    try
    {
        $sql = "select ".$this->selectConCalendario()."
                from proveedores p
                left join proveedor_calendario pc on pc.proveedor_id = p.id and pc.activo = 1
                where p.id = :id";
        if ($soloLocales) {
          $sql .= " and p.origen = 'local'";
        }
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':id' => $id));
        return $stm->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getById($id){
    return $this->getByIdFiltrado($id, true);
  }

  public function getByIdParaCalendario($id){
    return $this->getByIdFiltrado($id, false);
  }

  public function getAllProducts($idProveedor){
    try
    {
        $stm = $this->pdo->prepare("select p.*
                                    from productos p, editoriales e
                                    where p.id_editorial = e.id
                                    and e.id_proveedor = :idProveedor
                                    order by p.titulo;
        ");
        $stm->execute(array(':idProveedor' => $idProveedor));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
      $this->logger->log(__FILE__,$e->getMessage(),$this->logger::CRITICAL);
    }
  }

  public function getAllActive(){
    return $this->getAllFiltrado('p.nombre', true, true);
  }

  public function getAllActiveParaCalendario(){
    return $this->getAllFiltrado('p.nombre', false, true);
  }

}

 ?>
