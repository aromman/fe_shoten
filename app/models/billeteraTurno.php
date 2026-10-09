<?php

require_once 'crud.php';
require_once 'logger.php';
require_once 'cuentasMovimientos.php';
require_once dirname(__DIR__) . '/Turnos/Domain/ConteoBilletes.php';

use App\Turnos\Domain\ConteoBilletes;

class BilleteraTurno extends Crud
{

  const TABLE = 'billetera_turnos';
  const CAUSAL_TRANSFERENCIA = 3;
  const CAUSAL_CAFETERIA = 5;
  const CUENTA_EFECTIVO = 1;

  public $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo = parent::conexion();
    $this->logger = new Logger();
  }

  public function obtenerAbierto($idCuenta){
    try {
      $stm = $this->pdo->prepare("SELECT * FROM ".self::TABLE." WHERE id_cuenta = ? AND estado = 'A' LIMIT 1");
      $stm->execute(array($idCuenta));
      $row = $stm->fetch(PDO::FETCH_ASSOC);
      return $row ?: null;
    } catch (PDOException $e){
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return null;
    }
  }

  public function obtenerUltimoCerrado($idCuenta){
    try {
      $stm = $this->pdo->prepare("SELECT * FROM ".self::TABLE." WHERE id_cuenta = ? AND estado = 'C' ORDER BY fecha_cierre DESC LIMIT 1");
      $stm->execute(array($idCuenta));
      $row = $stm->fetch(PDO::FETCH_ASSOC);
      return $row ?: null;
    } catch (PDOException $e){
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return null;
    }
  }

  /**
   * Abrir Billetera: solo genera un debito en la cuenta Efectivo.
   * No genera ningun movimiento en la cuenta del empleado.
   */
  public function abrir($idCuenta, $billetes, $username, $idCanal, $nombreEmpleado){
    $idCuenta = (int) $idCuenta;
    if ($idCuenta <= 0) {
      throw new InvalidArgumentException('CUENTA_INVALIDA');
    }

    $conteo = ConteoBilletes::calcular($billetes);

    try {
      $this->pdo->beginTransaction();

      $existente = $this->pdo->prepare("SELECT id FROM ".self::TABLE." WHERE id_cuenta = ? AND estado = 'A' FOR UPDATE");
      $existente->execute(array($idCuenta));
      if ($existente->fetchColumn()) {
        throw new DomainException('BILLETERA_YA_ABIERTA');
      }

      $fechaApertura = date('Y-m-d H:i:s');

      $insert = $this->pdo->prepare(
        "INSERT INTO ".self::TABLE." (id_cuenta, fecha_apertura, monto_apertura, estado, usuario_apertura)
         VALUES (?, ?, ?, 'A', ?)"
      );
      $insert->execute(array($idCuenta, $fechaApertura, $conteo['total'], $username));
      $turnoId = (int) $this->pdo->lastInsertId();

      if ($conteo['total'] > 0) {
        $this->crearMovimiento(
          self::CUENTA_EFECTIVO,
          'D',
          $conteo['total'],
          $this->truncarDescripcion('Apertura Billetera ' . $nombreEmpleado),
          self::CAUSAL_TRANSFERENCIA,
          $idCanal,
          $fechaApertura
        );
      }

      $this->pdo->commit();

      return array(
        'id' => $turnoId,
        'idCuenta' => $idCuenta,
        'fechaApertura' => $fechaApertura,
        'montoApertura' => $conteo['total'],
        'billetes' => $conteo['cantidades'],
      );
    } catch (\Throwable $error) {
      if ($this->pdo->inTransaction()) {
        $this->pdo->rollBack();
      }
      throw $error;
    }
  }

  /**
   * Cerrar Billetera:
   * - Contado < Apertura (falta): credito en Efectivo por el contado + debito
   *   en la cuenta del empleado por la diferencia con la apertura.
   * - Contado = Apertura: credito en Efectivo por el contado.
   * - Contado > Apertura (sobra): credito en Efectivo por la apertura +
   *   credito en Efectivo por separado por el excedente ("Cobro Cafeteria").
   */
  public function cerrar($idCuenta, $billetes, $username, $idCanal, $nombreEmpleado){
    $idCuenta = (int) $idCuenta;
    if ($idCuenta <= 0) {
      throw new InvalidArgumentException('CUENTA_INVALIDA');
    }

    $conteo = ConteoBilletes::calcular($billetes);

    try {
      $this->pdo->beginTransaction();

      $turnoStm = $this->pdo->prepare("SELECT * FROM ".self::TABLE." WHERE id_cuenta = ? AND estado = 'A' FOR UPDATE");
      $turnoStm->execute(array($idCuenta));
      $turno = $turnoStm->fetch(PDO::FETCH_ASSOC);
      if (!$turno) {
        throw new DomainException('BILLETERA_NO_ABIERTA');
      }

      $montoApertura = (float) $turno['monto_apertura'];
      $montoContado = $conteo['total'];
      $diferencia = round($montoContado - $montoApertura, 2);
      $fechaCierre = date('Y-m-d H:i:s');

      $update = $this->pdo->prepare(
        "UPDATE ".self::TABLE."
         SET estado = 'C', fecha_cierre = :fechaCierre, monto_esperado = :montoApertura,
             monto_cierre = :montoCierre, diferencia = :diferencia, usuario_cierre = :usuario
         WHERE id = :id"
      );
      $update->execute(array(
        ':fechaCierre' => $fechaCierre,
        ':montoApertura' => $montoApertura,
        ':montoCierre' => $montoContado,
        ':diferencia' => $diferencia,
        ':usuario' => $username,
        ':id' => $turno['id'],
      ));

      if ($diferencia < -0.005) {
        // Falta: se cobra lo contado y se debita la diferencia al empleado.
        $faltante = round(abs($diferencia), 2);
        $descripcion = $this->truncarDescripcion(
          'Cierre Billetera ' . $nombreEmpleado . ' - Faltante ' . $this->formatearMonto($faltante)
        );

        if ($montoContado > 0) {
          $this->crearMovimiento(self::CUENTA_EFECTIVO, 'C', $montoContado, $descripcion, self::CAUSAL_TRANSFERENCIA, $idCanal, $fechaCierre);
        }
        $this->crearMovimiento($idCuenta, 'D', $faltante, $descripcion, self::CAUSAL_TRANSFERENCIA, $idCanal, $fechaCierre);

      } elseif ($diferencia > 0.005) {
        // Sobra: se acredita la apertura y el excedente por separado, como cobro de cafeteria.
        $descripcionCierre = $this->truncarDescripcion('Cierre Billetera ' . $nombreEmpleado);
        if ($montoApertura > 0) {
          $this->crearMovimiento(self::CUENTA_EFECTIVO, 'C', $montoApertura, $descripcionCierre, self::CAUSAL_TRANSFERENCIA, $idCanal, $fechaCierre);
        }

        $descripcionCobro = $this->truncarDescripcion('Cobro Cafeteria ' . $nombreEmpleado . ' $' . $this->formatearMonto($diferencia));
        $this->crearMovimiento(self::CUENTA_EFECTIVO, 'C', $diferencia, $descripcionCobro, self::CAUSAL_CAFETERIA, $idCanal, $fechaCierre);

      } else {
        // Exacto.
        if ($montoContado > 0) {
          $descripcionCierre = $this->truncarDescripcion('Cierre Billetera ' . $nombreEmpleado);
          $this->crearMovimiento(self::CUENTA_EFECTIVO, 'C', $montoContado, $descripcionCierre, self::CAUSAL_TRANSFERENCIA, $idCanal, $fechaCierre);
        }
      }

      $this->pdo->commit();

      return array(
        'id' => (int) $turno['id'],
        'idCuenta' => $idCuenta,
        'fechaApertura' => $turno['fecha_apertura'],
        'fechaCierre' => $fechaCierre,
        'montoApertura' => $montoApertura,
        'montoCierre' => $montoContado,
        'diferencia' => $diferencia,
        'billetes' => $conteo['cantidades'],
      );
    } catch (\Throwable $error) {
      if ($this->pdo->inTransaction()) {
        $this->pdo->rollBack();
      }
      throw $error;
    }
  }

  /**
   * Compensar: solo aplica cuando la cuenta del empleado tiene saldo negativo
   * (nos debe). Cobra un importe (total o parcial) y lo acredita tanto en
   * Efectivo como en la cuenta del empleado.
   */
  public function compensar($idCuenta, $importePagar, $username, $idCanal, $nombreEmpleado){
    $idCuenta = (int) $idCuenta;
    if ($idCuenta <= 0) {
      throw new InvalidArgumentException('CUENTA_INVALIDA');
    }

    $importePagar = round((float) $importePagar, 2);
    if ($importePagar <= 0) {
      throw new InvalidArgumentException('IMPORTE_INVALIDO');
    }

    try {
      $this->pdo->beginTransaction();

      $abierto = $this->pdo->prepare("SELECT id FROM ".self::TABLE." WHERE id_cuenta = ? AND estado = 'A' FOR UPDATE");
      $abierto->execute(array($idCuenta));
      if ($abierto->fetchColumn()) {
        throw new DomainException('BILLETERA_ABIERTA');
      }

      $saldoStm = $this->pdo->prepare(
        "SELECT c.saldo_inicial + COALESCE((
            SELECT SUM(CASE WHEN cm.tipo_movimiento = 'C' THEN cm.monto ELSE -cm.monto END)
            FROM cuentas_movimientos cm WHERE cm.id_cuenta = c.id
         ), 0) saldo
         FROM cuentas c WHERE c.id = :idCuenta FOR UPDATE"
      );
      $saldoStm->execute(array(':idCuenta' => $idCuenta));
      $saldo = round((float) $saldoStm->fetchColumn(), 2);

      if ($saldo >= -0.005) {
        throw new DomainException('SIN_SALDO_A_COMPENSAR');
      }

      $cobrar = round(abs($saldo), 2);
      if ($importePagar > $cobrar + 0.005) {
        throw new InvalidArgumentException('IMPORTE_MAYOR_A_DEUDA');
      }

      $fecha = date('Y-m-d H:i:s');
      $descripcion = $this->truncarDescripcion('Compensacion Saldo ' . $nombreEmpleado);

      $this->crearMovimiento(self::CUENTA_EFECTIVO, 'C', $importePagar, $descripcion, self::CAUSAL_TRANSFERENCIA, $idCanal, $fecha);
      $this->crearMovimiento($idCuenta, 'C', $importePagar, $descripcion, self::CAUSAL_TRANSFERENCIA, $idCanal, $fecha);

      $this->pdo->commit();

      return array(
        'idCuenta' => $idCuenta,
        'cobrar' => $cobrar,
        'importePagado' => $importePagar,
        'resto' => round($cobrar - $importePagar, 2),
        'fecha' => $fecha,
      );
    } catch (\Throwable $error) {
      if ($this->pdo->inTransaction()) {
        $this->pdo->rollBack();
      }
      throw $error;
    }
  }

  private function crearMovimiento($idCuenta, $tipoMovimiento, $monto, $descripcion, $idCausal, $idCanal, $fecha){
    $movimiento = new CuentasMovimientos();
    $movimiento->pdo = $this->pdo;
    $movimiento->idCuenta = $idCuenta;
    $movimiento->fecha = $fecha;
    $movimiento->tipoMovimiento = $tipoMovimiento;
    $movimiento->descripcion = $descripcion;
    $movimiento->monto = $monto;
    $movimiento->consolidado = false;
    $movimiento->idCausal = $idCausal;
    $movimiento->idCanal = $idCanal;
    return $movimiento->create();
  }

  private function formatearMonto($valor){
    $valor = round((float) $valor, 2);
    if (abs($valor - round($valor)) < 0.001) {
      return number_format($valor, 0, '.', '');
    }
    return number_format($valor, 2, '.', '');
  }

  private function truncarDescripcion($texto){
    return mb_substr((string) $texto, 0, 50);
  }

  public function create(){
    // No se usa: los altas se hacen mediante abrir().
  }

  public function update(){
    // No se usa: las modificaciones se hacen mediante cerrar().
  }

}
