<?php 
include_once('../config.php');
require_once "../app/models/medioPago.php";
require_once "../app/models/cuentasMovimientos.php";


abstract class Pagos
{
    const Efectivo = 1;
    const Tarjeta = 2;
    const Cancelado = 3;
}

error_log(PHP_EOL.'Iniciando =====> ', 3, "my-errors.log");  

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['id_pago']) and $_REQUEST['id_pago']!=""
  and isset($_REQUEST['cantidad']) and $_REQUEST['cantidad']!=""
){
    
  error_log(PHP_EOL.'Parametros =====> ', 3, "my-errors.log");   
    // reduce de a un item
  $id_evento = $_REQUEST['id'];
  $id_pago = $_REQUEST['id_pago'];
  $cantidad = $_REQUEST['cantidad'];

  if ($id_pago!=Pagos::Cancelado){
    // venta realizada
    error_log(PHP_EOL.'Header =====> ', 3, "my-errors.log");   
    // ventas header
    $sqlInsert = 'INSERT INTO ventas_header (id_canal, fecha, id_cliente, id_medio_pago, cantidad, subTotal, total, sorteo) 
                  select 
                  s.id_canal,
                  c.fechaFin fecha,
                  1 id_cliente,
                  '.$id_pago.' id_medio_pago, 
                  1 cantidad, 
                  s.precio subTotal, 
                  s.precio total, 
                  false sorteo
                  from stock s,
                  canal c
                  where s.id = '.$id_evento.'
                  and s.id_canal = c.id;';

    error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
    $db->query($sqlInsert);
    $id_header = $db->insert_id;

    // ventas detalle
    error_log(PHP_EOL.'Detalle =====> ', 3, "my-errors.log");   
    $sqlInsert = 'INSERT INTO ventas (id_ventas_header, fecha, total, id_canal, id_producto, unidades, id_equipo, id_medio_pago, id_cliente, precio_unitario, descuento, costo) 
      select 
        '.$id_header.',
        c.fechaFin fecha,
        s.precio total, 
        s.id_canal,
        s.id_producto,
        1 cantidad, 
        1 id_equipo, 
        '.$id_pago.' id_medio_pago, 
        1 id_cliente,
        s.precio precio,
        0 descuento, 
        0 costo
        from stock s,
        canal c
        where s.id = '.$id_evento.'
        and s.id_canal = c.id';
      error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
      $db->query($sqlInsert);

      $sqlUpdate = 'update productos set stock = stock - 1 where id in (select s.id_producto from stock s where s.id = '.$id_evento.' ) and stock > 0';
      error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
      $db->query($sqlUpdate);

      // actualizo costo segun monto fijo
      $sqlUpdate = 'update ventas set costo = precio_unitario - (
      select IFNULL(e.monto_fijo, 0) from editoriales e, productos p where e.id = p.id_editorial and ventas.id_producto = p.id )
      where ventas.id_ventas_header = '.$id_header.';';
      error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
      $db->query($sqlUpdate);
      
      // aplico porcentaje
      $sqlUpdate = 'update ventas set costo = costo * ((100 - (select IFNULL(e.porcentaje, 0) from editoriales e, productos p where e.id = p.id_editorial and ventas.id_producto = p.id)) / 100)
      where ventas.id_ventas_header = '.$id_header.';';
      error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
      $db->query($sqlUpdate);

      $medioPagoPDO = new MedioPago();
      $medioPago = $medioPagoPDO->getById($id_pago);
      $idCuenta = isset($medioPago['id_cuenta']) ? (int) $medioPago['id_cuenta'] : 0;
      if ($idCuenta > 0) {
        $montoMovimiento = 0;
        $rsMovimiento = $db->query('SELECT total FROM ventas_header WHERE id = ' . (int) $id_header . ' LIMIT 1');
        if ($rsMovimiento && ($rowMovimiento = $rsMovimiento->fetch_assoc())) {
          $montoMovimiento = isset($rowMovimiento['total']) ? (float) $rowMovimiento['total'] : 0;
        }

        $cuentaMovimientoPDO = new CuentasMovimientos();
        $cuentaMovimientoPDO->idCuenta = $idCuenta;
        $cuentaMovimientoPDO->fecha = date('Y-m-d H:i:s');
        $cuentaMovimientoPDO->tipoMovimiento = 'C';
        $cuentaMovimientoPDO->descripcion = 'Venta-' . $id_header;
        $cuentaMovimientoPDO->monto = $montoMovimiento;
        $cuentaMovimientoPDO->consolidado = FALSE;
        $cuentaMovimientoPDO->idCausal = 1;
        $cuentaMovimientoPDO->origenTipo = 'venta';
        $cuentaMovimientoPDO->origenId = $id_header;
        $cuentaMovimientoPDO->create();
      }
  }

  // actualizo stock de evento
  if ($cantidad > 1) {
    // actualizo stock
    $sqlUpdate = 'update stock set cantidad = cantidad - 1 where id = '.$id_evento.' and cantidad > 0';
    error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
    $db->query($sqlUpdate);

  } else {
    // elimino stock
    $sqlUpdate = 'delete from stock where id = '.$id_evento;
    error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  
    $db->query($sqlUpdate);

  }

    header('location: finalizar-evento.php');
    exit;
}
?>
