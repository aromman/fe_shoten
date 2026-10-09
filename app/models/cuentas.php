<?php
require_once 'crud.php';
class Cuentas extends Crud
{
  private $id;
  const TABLE='cuentas';
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

  public function getByType($type, $orderBy){
    try
    {
        $allowedOrderBy = array(
          'id' => 'id',
          'nombre' => 'nombre',
          'nombre ASC' => 'nombre ASC',
          'nombre DESC' => 'nombre DESC',
          'tipo' => 'tipo',
          'saldo_inicial' => 'saldo_inicial'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $sql = "SELECT * FROM cuentas WHERE tipo = :type ORDER BY ".$allowedOrderBy[$orderBy].";";
        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':type' => $type));
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  public function getAllWithBalance($orderBy = 'nombre ASC', $ambitoUsoFiltro = null, $incluirInactivas = false){
    try
    {
        $allowedOrderBy = array(
          'id' => 'c.id',
          'nombre' => 'c.nombre',
          'nombre ASC' => 'c.nombre ASC',
          'nombre DESC' => 'c.nombre DESC',
          'tipo' => 'c.tipo',
          'saldo_inicial' => 'c.saldo_inicial',
          'tipo_saldo' => 'c.tipo_saldo'
        );
        if (!isset($allowedOrderBy[$orderBy])) {
          throw new InvalidArgumentException("Ordenamiento invalido: ".$orderBy);
        }

        $condiciones = array();
        $params = array();
        if (is_array($ambitoUsoFiltro) && !empty($ambitoUsoFiltro)) {
          $placeholders = array();
          foreach (array_values($ambitoUsoFiltro) as $indice => $valor) {
            $clave = ':ambitoUso' . $indice;
            $placeholders[] = $clave;
            $params[$clave] = $valor;
          }
          $condiciones[] = 'c.ambito_uso IN (' . implode(',', $placeholders) . ')';
        }
        if (!$incluirInactivas) {
          $condiciones[] = 'c.activa = 1';
        }
        $where = !empty($condiciones) ? ' WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT
                c.id,
                c.nombre,
                c.tipo,
                c.saldo_inicial,
                c.tipo_saldo,
                c.ambito_uso,
                c.id_empleado,
                c.activa,
                CONCAT(e.apellido, ', ', e.nombre) empleado_nombre,
                COALESCE((select sum(cmd.monto) from cuentas_movimientos cmd where cmd.id_cuenta = c.id and cmd.tipo_movimiento='D'), 0) debitos,
                COALESCE((select sum(cmc.monto) from cuentas_movimientos cmc where cmc.id_cuenta = c.id and cmc.tipo_movimiento='C'), 0) creditos
                FROM cuentas c
                LEFT JOIN empleados e ON e.id = c.id_empleado"
                . $where . "
                ORDER BY ".$allowedOrderBy[$orderBy].";";

        $stm = $this->pdo->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }


  public function getTotalByType($type){
    try
    {
        $sql = "SELECT
                sum(
                (c.saldo_inicial  + (select IFNULL(sum(cmc.monto), 0) from cuentas_movimientos cmc where cmc.id_cuenta = c.id and cmc.tipo_movimiento='C') - (select IFNULL(sum(cmd.monto), 0) from cuentas_movimientos cmd where cmd.id_cuenta = c.id and cmd.tipo_movimiento='D') )
                )
                monto
                FROM cuentas c
                where c.tipo = :type
                and c.ambito_uso = 'COMERCIAL'
                and c.activa = 1";

        $stm = $this->pdo->prepare($sql);
        $stm->execute(array(':type' => $type));
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


  public function tieneDependencias($id){
    try
    {
        $dependencias = array();

        $stmMovimientos = $this->pdo->prepare("SELECT COUNT(*) total FROM cuentas_movimientos WHERE id_cuenta = :id");
        $stmMovimientos->execute(array(':id' => $id));
        $rowMovimientos = $stmMovimientos->fetch(PDO::FETCH_ASSOC);
        if (isset($rowMovimientos['total']) && (int) $rowMovimientos['total'] > 0) {
          $dependencias[] = 'movimientos';
        }

        $stmMedioPago = $this->pdo->prepare("SELECT COUNT(*) total FROM medio_pago WHERE id_cuenta = :id");
        $stmMedioPago->execute(array(':id' => $id));
        $rowMedioPago = $stmMedioPago->fetch(PDO::FETCH_ASSOC);
        if (isset($rowMedioPago['total']) && (int) $rowMedioPago['total'] > 0) {
          $dependencias[] = 'medio_pago';
        }

        $cuenta = $this->getById($id);
        if (is_array($cuenta) && !empty($cuenta['id_empleado'])) {
          $dependencias[] = 'empleado';
        }

        return $dependencias;
    }
    catch (PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }


/*Aquí Insertamos un animal, tenemos que crear forzosamente este método porque en el CRUD lo agregamos como **abstract** sino lo agregamos obtendremos un error.*/
  public function create(){
    try{
        $stm=$this->pdo->prepare("INSERT INTO ".self::TABLE." (nombre, tipo, tipo_saldo, ambito_uso, id_empleado, fecha_consolidado, saldo_consolidado, saldo_inicial) VALUES (?,?,?,?,?,?,?,?)");
        $stm->execute(array($this->nombre,$this->tipo,$this->tipo_saldo,$this->ambito_uso,$this->id_empleado,$this->fecha_consolidado,$this->saldo_consolidado,$this->saldo_inicial));
    }catch(PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }

  public function update(){
    try{
        $params = array();
        $sql = "";
        if (isset($this->nombre)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'nombre = :nombre';
          $params[':nombre'] = $this->nombre;
        }
        if (isset($this->tipo)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'tipo = :tipo';
          $params[':tipo'] = $this->tipo;
        }
        if (isset($this->tipo_saldo)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'tipo_saldo = :tipo_saldo';
          $params[':tipo_saldo'] = $this->tipo_saldo;
        }
        if (isset($this->saldo_inicial)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'saldo_inicial = :saldo_inicial';
          $params[':saldo_inicial'] = $this->saldo_inicial;
        }
        if (isset($this->ambito_uso)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'ambito_uso = :ambito_uso';
          $params[':ambito_uso'] = $this->ambito_uso;
        }
        if (property_exists($this, 'id_empleado')){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'id_empleado = :id_empleado';
          $params[':id_empleado'] = $this->id_empleado;
        }
        if (isset($this->activa)){
          $sql = !empty($sql) ? $sql .= ', ' : $sql;
          $sql .= 'activa = :activa';
          $params[':activa'] = $this->activa;
        }
        if (!empty($sql)){
          $params[':id'] = $this->id;
          $stm=$this->pdo->prepare("UPDATE ".self::TABLE." SET ".$sql." WHERE id=:id");
          $stm->execute($params);
        }
    }catch(PDOException $e){
        error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$e->getMessage(), 3, "my-errors.log");
        die($e->getMessage());
    }
  }
}

 ?>
