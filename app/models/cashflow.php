<?php
require_once 'crud.php';
require_once 'logger.php';

class Cashflow extends Crud
{
  const TABLE = 'ventas';
  public $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo = parent::conexion();
    $this->logger = new Logger();
  }

  private function mesesVacios(){
    $meses = array();
    for ($i = 1; $i <= 12; $i++) {
      $meses[$i] = 0;
    }

    return $meses;
  }

  private function ejecutarMontosMensuales($sql){
    try {
      $stm = $this->pdo->prepare($sql);
      $stm->execute();
      $row = $stm->fetch(PDO::FETCH_ASSOC);
      $meses = $this->mesesVacios();

      if (!$row) {
        return $meses;
      }

      for ($i = 1; $i <= 12; $i++) {
        $meses[$i] = isset($row['mes_'.$i]) ? (float) $row['mes_'.$i] : 0;
      }

      return $meses;
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return $this->mesesVacios();
    }
  }

  private function selectMensual($expresionMonto, $tablaYJoins, $where){
    $campos = array();
    for ($i = 1; $i <= 12; $i++) {
      $campos[] = "SUM(IF(month(fecha_referencia)=".$i.", ".$expresionMonto.", 0)) AS mes_".$i;
    }

    return "select ".implode(",\n", $campos)."
            from (
              select ".$tablaYJoins."
              where ".$where."
            ) datos";
  }

  public function getMesActual(){
    try {
      $stm = $this->pdo->prepare("select MONTH(CURRENT_DATE()) mes_actual");
      $stm->execute();
      $row = $stm->fetch(PDO::FETCH_ASSOC);

      return $row && isset($row['mes_actual']) ? (int) $row['mes_actual'] : (int) date('n');
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return (int) date('n');
    }
  }

  public function getVentasMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'unidades * precio_unitario',
      'v.fecha fecha_referencia, v.unidades, v.precio_unitario from ventas v',
      'YEAR(v.fecha) = YEAR(CURRENT_DATE())'
    ));
  }

  public function getTotalVentasEntreFechas($desde, $hasta){
    $row = $this->consultarUno("
      SELECT COALESCE(SUM(v.unidades * v.precio_unitario), 0) monto
      FROM ventas v
      WHERE v.fecha >= :desde
        AND v.fecha < :hasta
    ", array(':desde' => $desde, ':hasta' => $hasta));

    return isset($row['monto']) ? (float) $row['monto'] : 0;
  }

  public function getVentasMensualesEntreFechas($desde, $hasta){
    return $this->consultarTodos("
      SELECT
        DATE_FORMAT(v.fecha, '%Y-%m') mes,
        COALESCE(SUM(v.unidades * v.precio_unitario), 0) monto
      FROM ventas v
      WHERE v.fecha >= :desde
        AND v.fecha < :hasta
      GROUP BY DATE_FORMAT(v.fecha, '%Y-%m')
      ORDER BY mes
    ", array(':desde' => $desde, ':hasta' => $hasta));
  }

  public function getComprasMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'precio_costo * cantidad',
      'c.fecha fecha_referencia, c.precio_costo, c.cantidad from compras c',
      'c.id_estado != 1 and YEAR(c.fecha) = YEAR(CURRENT_DATE())'
    ));
  }

  public function getGastosMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'monto',
      'g.fecha fecha_referencia, g.monto from gastos g',
      'g.monto > 0 and YEAR(g.fecha) = YEAR(CURRENT_DATE())'
    ));
  }

  public function getComprasFinanciadasMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'precio_costo * cantidad',
      'c.fecha fecha_referencia, c.precio_costo, c.cantidad from compras c join medio_pago mp on c.id_medio_pago = mp.id join cuentas cu on mp.id_cuenta = cu.id',
      "c.id_estado != 1 and YEAR(c.fecha) = YEAR(CURRENT_DATE()) and cu.tipo = 'P'"
    ));
  }

  public function getGastosFinanciadosMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'monto',
      'g.fecha fecha_referencia, g.monto from gastos g join medio_pago mp on g.id_medio_pago = mp.id join cuentas cu on mp.id_cuenta = cu.id',
      "g.monto > 0 and YEAR(g.fecha) = YEAR(CURRENT_DATE()) and cu.tipo = 'P'"
    ));
  }

  public function getVentasFinanciadasMensualesAnioActual(){
    return $this->ejecutarMontosMensuales($this->selectMensual(
      'unidades * precio_unitario',
      'v.fecha fecha_referencia, v.unidades, v.precio_unitario from ventas v join medio_pago mp on v.id_medio_pago = mp.id join cuentas cu on mp.id_cuenta = cu.id',
      "YEAR(v.fecha) = YEAR(CURRENT_DATE()) and cu.tipo = 'P'"
    ));
  }

  public function getGastoPresupuestadoMensual(){
    try {
      $stm = $this->pdo->prepare("select sum(presupuesto) monto from gastos_clase");
      $stm->execute();
      $row = $stm->fetch(PDO::FETCH_ASSOC);

      return $row && isset($row['monto']) ? (float) $row['monto'] : 0;
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return 0;
    }
  }

  private function consultarTodos($sql, $params = array()){
    try {
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  private function consultarUno($sql, $params = array()){
    $rows = $this->consultarTodos($sql, $params);
    return count($rows) > 0 ? $rows[0] : array();
  }

  public function getSaldoDisponibleActual(){
    $row = $this->consultarUno("
      SELECT COALESCE(SUM(
        c.saldo_inicial
        + (SELECT COALESCE(SUM(credito.monto), 0)
           FROM cuentas_movimientos credito
           WHERE credito.id_cuenta = c.id AND credito.tipo_movimiento = 'C')
        - (SELECT COALESCE(SUM(debito.monto), 0)
           FROM cuentas_movimientos debito
           WHERE debito.id_cuenta = c.id AND debito.tipo_movimiento = 'D')
      ), 0) monto
      FROM cuentas c
      WHERE c.tipo = 'A'
        AND c.tipo_saldo IN ('E', 'B')
    ");

    return isset($row['monto']) ? (float) $row['monto'] : 0;
  }

  public function getMovimientosFuturosMensuales($desde, $hasta){
    return $this->consultarTodos("
      SELECT
        cm.fecha,
        cm.tipo_movimiento tipo,
        cm.monto
      FROM cuentas_movimientos cm
      JOIN cuentas c ON c.id = cm.id_cuenta
      WHERE cm.fecha >= :desde
        AND cm.fecha < :hasta
        AND c.tipo = 'A'
        AND c.tipo_saldo IN ('E', 'B')
      ORDER BY cm.fecha
    ", array(':desde' => $desde, ':hasta' => $hasta));
  }

  public function getClasesGastoPresupuestadas(){
    return $this->consultarTodos("
      SELECT nombre, presupuesto, dia_pago
      FROM gastos_clase
      WHERE presupuesto > 0
      ORDER BY prioridad, nombre
    ");
  }

  public function getOrdenesCompraPendientesMensuales($desde, $hasta){
    return $this->consultarTodos("
      SELECT
        COALESCE(oc.fecha_entrega, oc.fecha) fecha,
        COALESCE(oc.monto, 0) monto
      FROM orden_compra oc
      WHERE oc.id_estado_pedido NOT IN (5, 7)
        AND COALESCE(oc.fecha_entrega, oc.fecha) >= :desde
        AND COALESCE(oc.fecha_entrega, oc.fecha) < :hasta
      ORDER BY COALESCE(oc.fecha_entrega, oc.fecha)
    ", array(':desde' => $desde, ':hasta' => $hasta));
  }

  public function getSueldosPendientesMensuales($desde, $hasta){
    return $this->consultarTodos("
      SELECT
        s.fecha,
        COALESCE(s.neto, 0) monto
      FROM sueldos s
      WHERE s.fecha >= :desde
        AND s.fecha < :hasta
      ORDER BY s.fecha
    ", array(':desde' => $desde, ':hasta' => $hasta));
  }

  public function getConsignacionesPendientesPagoTotal(){
    $row = $this->consultarUno("
      SELECT COALESCE(SUM(c.cantidad * c.precio_lista), 0) monto
      FROM compras c
      JOIN productos p ON c.id_producto = p.id
      JOIN editoriales e ON p.id_editorial = e.id
      WHERE e.consignacion = TRUE
        AND c.id_estado != 6
        AND c.id IN (SELECT co.id_compra FROM consignaciones co)
    ");

    return isset($row['monto']) ? (float) $row['monto'] : 0;
  }

  public function create(){}

  public function update(){}
}
?>
