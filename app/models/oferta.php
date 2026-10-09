<?php
require_once 'crud.php';
require_once 'logger.php';

class Oferta extends Crud
{
  const TABLE='productos';
  public $pdo;
  public $logger;

  public function __construct(){
    parent::__construct(self::TABLE);
    $this->pdo=parent::conexion();
    $this->logger = new Logger();
  }

  public function getPosiblesOfertas(){
    try {
      $sql = "select
              x.id,
              x.titulo,
              x.stock,
              x.precio,
              x.precio_costo,
              x.precio_oferta,
              x.nuevo,
              x.precio_compra,
              case
                when x.fecha_ultima_venta is not null and x.fecha_ultima_venta > x.fecha_ultima_compra then x.fecha_ultima_venta
                else x.fecha_ultima_compra
              end fecha_ultima_accion,
              case
                when x.fecha_ultima_venta is not null and x.fecha_ultima_venta > x.fecha_ultima_compra then 'Venta'
                else 'Compra'
              end tipo_ultima_accion,
              x.porcentaje,
              TIMESTAMPDIFF(MONTH,
                case
                  when x.fecha_ultima_venta is not null and x.fecha_ultima_venta > x.fecha_ultima_compra then x.fecha_ultima_venta
                  else x.fecha_ultima_compra
                end,
                NOW()
              ) meses_sin_accion
              from (
                select
                p.id,
                p.titulo,
                p.stock,
                p.precio,
                p.precio_costo,
                p.precio_oferta,
                p.nuevo,
                max(c.precio_costo) precio_compra,
                max(c.fecha) fecha_ultima_compra,
                max(v.fecha) fecha_ultima_venta,
                e.porcentaje
                from productos p
                join compras c on c.id_producto = p.id
                left join ventas v on v.id_producto = p.id
                join editoriales e on p.id_editorial = e.id
                where p.stock > 0
                and e.consignacion = false
                and (
                  COALESCE(p.precio_oferta, 0) <= 0
                  or p.updateDate is null
                  or p.updateDate < (NOW() - INTERVAL 30 DAY)
                )
                group by p.id, p.titulo, p.stock, p.precio, p.precio_costo, p.precio_oferta, p.nuevo, e.porcentaje
              ) x
              where TIMESTAMPDIFF(MONTH,
                case
                  when x.fecha_ultima_venta is not null and x.fecha_ultima_venta > x.fecha_ultima_compra then x.fecha_ultima_venta
                  else x.fecha_ultima_compra
                end,
                NOW()
              ) > 3
              order by fecha_ultima_accion asc, x.titulo";
      $stm = $this->pdo->prepare($sql);
      $stm->execute();
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getOfertasActivas($nombre = ''){
    try {
      $params = array();
      $filtroNombre = '';
      if (trim((string) $nombre) !== '') {
        $filtroNombre = ' and p.titulo like :nombre';
        $params[':nombre'] = '%' . trim((string) $nombre) . '%';
      }

      $sql = "select
              p.id,
              p.titulo,
              p.stock,
              p.precio,
              p.precio_costo,
              p.precio_oferta,
              p.updateDate
              from productos p
              where p.precio_oferta is not null
              and p.precio_oferta > 0
              and p.stock > 0
              " . $filtroNombre . "
              order by p.titulo";
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getOfertasVendidasUltimosMeses($meses = 3, $nombre = ''){
    try {
      $meses = max(1, (int) $meses);
      $params = array();
      $filtroNombre = '';
      if (trim((string) $nombre) !== '') {
        $filtroNombre = ' and p.titulo like :nombre';
        $params[':nombre'] = '%' . trim((string) $nombre) . '%';
      }

      $sql = "select
              p.id,
              p.titulo,
              p.stock,
              p.precio,
              p.precio_costo,
              p.precio_oferta,
              p.updateDate,
              min(v.fecha) primera_venta,
              max(v.fecha) ultima_venta,
              sum(v.unidades) unidades_vendidas,
              sum(v.total) total_vendido,
              sum(v.descuento) descuento_total,
              count(distinct v.id_ventas_header) operaciones
              from productos p
              join ventas v on v.id_producto = p.id
              where p.precio_oferta is not null
              and p.precio_oferta > 0
              and v.fecha >= (CURRENT_DATE - INTERVAL " . $meses . " MONTH)
              " . $filtroNombre . "
              group by p.id, p.titulo, p.stock, p.precio, p.precio_costo, p.precio_oferta, p.updateDate
              order by ultima_venta desc, p.titulo";
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getOfertasVendidasPorMes($meses = 3, $nombre = ''){
    try {
      $meses = max(1, (int) $meses);
      $params = array();
      $filtroNombre = '';
      if (trim((string) $nombre) !== '') {
        $filtroNombre = ' and p.titulo like :nombre';
        $params[':nombre'] = '%' . trim((string) $nombre) . '%';
      }

      $sql = "select
              DATE_FORMAT(v.fecha, '%Y-%m') mes,
              DATE_FORMAT(v.fecha, '%m/%Y') etiqueta,
              sum(v.total) total_vendido
              from productos p
              join ventas v on v.id_producto = p.id
              where p.precio_oferta is not null
              and p.precio_oferta > 0
              and v.fecha >= (CURRENT_DATE - INTERVAL " . $meses . " MONTH)
              " . $filtroNombre . "
              group by DATE_FORMAT(v.fecha, '%Y-%m'), DATE_FORMAT(v.fecha, '%m/%Y')
              order by mes";
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getOfertasVendidasPorSemana($meses = 3, $nombre = ''){
    try {
      $meses = max(1, (int) $meses);
      $params = array();
      $filtroNombre = '';
      if (trim((string) $nombre) !== '') {
        $filtroNombre = ' and p.titulo like :nombre';
        $params[':nombre'] = '%' . trim((string) $nombre) . '%';
      }

      $sql = "select
              YEARWEEK(v.fecha, 3) semana,
              DATE_FORMAT(DATE_SUB(DATE(v.fecha), INTERVAL WEEKDAY(v.fecha) DAY), '%d/%m') etiqueta,
              sum(v.total) total_vendido
              from productos p
              join ventas v on v.id_producto = p.id
              where p.precio_oferta is not null
              and p.precio_oferta > 0
              and v.fecha >= (CURRENT_DATE - INTERVAL " . $meses . " MONTH)
              " . $filtroNombre . "
              group by YEARWEEK(v.fecha, 3), DATE_SUB(DATE(v.fecha), INTERVAL WEEKDAY(v.fecha) DAY)
              order by semana";
      $stm = $this->pdo->prepare($sql);
      $stm->execute($params);
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getEditorialesConsignacion(){
    try {
      $stm = $this->pdo->prepare("select id, nombre from editoriales where consignacion = true order by nombre");
      $stm->execute();
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function getMediosPagoProveedor(){
    try {
      $sql = "select mp.id, mp.nombre
              from medio_pago mp
              join cuentas c on mp.id_cuenta = c.id
              where c.tipo = 'P'
              order by mp.nombre";
      $stm = $this->pdo->prepare($sql);
      $stm->execute();
      return $stm->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      return array();
    }
  }

  public function actualizarStock($idProducto, $stock){
    try {
      $stm = $this->pdo->prepare("update productos set stock = :stock where id = :id");
      $stm->execute(array(
        ':stock' => (int) $stock,
        ':id' => (int) $idProducto,
      ));
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      throw $e;
    }
  }

  public function actualizarPrecioOferta($idProducto, $precioOferta){
    try {
      $stm = $this->pdo->prepare("update productos set precio_oferta = :precioOferta, updateDate = NOW() where id = :id");
      $stm->execute(array(
        ':precioOferta' => (float) $precioOferta,
        ':id' => (int) $idProducto,
      ));
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      throw $e;
    }
  }

  public function actualizarPreciosProducto($idProducto, $precioCompra, $precioLista, $precioOferta){
    try {
      $stm = $this->pdo->prepare("update productos set precio_costo = :precioCompra, precio = :precioLista, precio_oferta = :precioOferta, updateDate = NOW() where id = :id");
      $stm->execute(array(
        ':precioCompra' => (float) $precioCompra,
        ':precioLista' => (float) $precioLista,
        ':precioOferta' => (float) $precioOferta,
        ':id' => (int) $idProducto,
      ));
    } catch (PDOException $e) {
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      throw $e;
    }
  }

  public function pasarAConsignacion($idProducto, $idEditorial, $idMedioPago, $cantidad, $precioUnitario){
    try {
      $this->pdo->beginTransaction();

      $producto = $this->getProductoResumen($idProducto);
      $titulo = is_array($producto) && isset($producto['titulo']) ? $producto['titulo'] : (string) $idProducto;

      $stmProducto = $this->pdo->prepare("update productos set id_editorial = :idEditorial where id = :id");
      $stmProducto->execute(array(
        ':idEditorial' => (int) $idEditorial,
        ':id' => (int) $idProducto,
      ));

      $medioPago = $this->getMedioPagoResumen($idMedioPago);
      if (!is_array($medioPago) || !isset($medioPago['id_cuenta'])) {
        throw new RuntimeException('MEDIO_PAGO_INVALIDO');
      }

      $monto = (float) $precioUnitario * (int) $cantidad;
      $descripcion = 'Pago Deuda con libro ' . $titulo;
      $stmMovimiento = $this->pdo->prepare("insert into cuentas_movimientos (id_cuenta, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal) values (:idCuenta, :fecha, :tipoMovimiento, :descripcion, :monto, :consolidado, :idCausal)");
      $stmMovimiento->execute(array(
        ':idCuenta' => (int) $medioPago['id_cuenta'],
        ':fecha' => date('Y-m-d'),
        ':tipoMovimiento' => 'C',
        ':descripcion' => $descripcion,
        ':monto' => $monto,
        ':consolidado' => 0,
        ':idCausal' => 2,
      ));

      $this->pdo->commit();
    } catch (Exception $e) {
      if ($this->pdo->inTransaction()) {
        $this->pdo->rollBack();
      }
      $this->logger->log(__FILE__, $e->getMessage(), $this->logger::CRITICAL);
      throw $e;
    }
  }

  private function getProductoResumen($idProducto){
    $stm = $this->pdo->prepare("select id, titulo from productos where id = :id limit 1");
    $stm->execute(array(':id' => (int) $idProducto));
    return $stm->fetch(PDO::FETCH_ASSOC);
  }

  private function getMedioPagoResumen($idMedioPago){
    $stm = $this->pdo->prepare("select id, id_cuenta from medio_pago where id = :id limit 1");
    $stm->execute(array(':id' => (int) $idMedioPago));
    return $stm->fetch(PDO::FETCH_ASSOC);
  }

  public function create(){}

  public function update(){}
}
?>
