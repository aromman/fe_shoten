<?php

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

		$id = $_GET["id"];
		$importe_total = $_GET["total"];
		$fecha = $_GET["fecha"];
		//$fecha = "20241023";
		$punto_de_venta = $_GET["puntoVenta"];



include_once ('../neofactura/wsfev1.php');
include_once ('../neofactura/wsaa.php');


require_once "../server.php";


$CUIT = "27440427109"; // CUIT del emisor
//$MODO = Wsaa::MODO_HOMOLOGACION;
$MODO = Wsaa::MODO_PRODUCCION;

/**
 * Numero del punto de venta
 **/
//$punto_de_venta = 1;

/**
 * Tipo de factura
 **/
$tipo_de_comprobante = 11; // 11 = Factura C

/**
 * Número de la ultima Factura C
 **/
$last_voucher = 1;

/**
 * Concepto de la factura
 *
 * Opciones:
 *
 * 1 = Productos 
 * 2 = Servicios 
 * 3 = Productos y Servicios
 **/
$concepto = 1;

/**
 * Tipo de documento del comprador
 *
 * Opciones:
 *
 * 80 = CUIT 
 * 86 = CUIL 
 * 96 = DNI
 * 99 = Consumidor Final 
 **/
$tipo_de_documento = 99;

/**
 * Numero de documento del comprador (0 para consumidor final)
 **/
$numero_de_documento = 0;

/**
 * Numero de comprobante
 **/
$numero_de_factura = $last_voucher+1;

/**
 * Fecha de la factura en formato aaaa-mm-dd (hasta 10 dias antes y 10 dias despues)
 **/
// $fecha = date('Y-m-d');

/**
 * Importe de la Factura
 **/
//$importe_total = 100;

/**
 * Los siguientes campos solo son obligatorios para los conceptos 2 y 3
 **/
if ($concepto === 2 || $concepto === 3) {
	/**
	 * Fecha de inicio de servicio en formato aaaammdd
	 **/
	$fecha_servicio_desde = intval(date('Ymd'));

	/**
	 * Fecha de fin de servicio en formato aaaammdd
	 **/
	$fecha_servicio_hasta = intval(date('Ymd'));

	/**
	 * Fecha de vencimiento del pago en formato aaaammdd
	 **/
	$fecha_vencimiento_pago = intval(date('Ymd'));
}
else {
	$fecha_servicio_desde = null;
	$fecha_servicio_hasta = null;
	$fecha_vencimiento_pago = null;
}

    $afip = new Wsfev1($CUIT,$MODO);

	$last_voucher = $afip->consultarUltimoComprobanteAutorizado($punto_de_venta,$tipo_de_comprobante);

	//error_log(PHP_EOL."Ultima Factura : " . $last_voucher, 3, "my-errors.log");            

	$numero_de_factura = $last_voucher+1;

	$voucher = Array
	(
		"idVoucher" => 1,
		"numeroComprobante" => $numero_de_factura,
		"numeroPuntoVenta" => $punto_de_venta,
		"cae" => 0,
		"letra" => "C",
		"fechaVencimientoCAE" => "",
		"tipoResponsable" => "6",
		"nombreCliente" =>  "",
		"domicilioCliente" => "",
		"fechaComprobante" => $fecha,
		"codigoTipoComprobante" => $tipo_de_comprobante,
		"TipoComprobante" => "Factura",
		"codigoConcepto" => $concepto,
		"codigoMoneda" => "PES",
		"cotizacionMoneda" => 1.000,
		"fechaDesde" => $fecha_servicio_desde,
		"fechaHasta" => $fecha_servicio_hasta,
		"fechaVtoPago" => $fecha_vencimiento_pago,
		"codigoTipoDocumento" => $tipo_de_documento,
		"TipoDocumento" => "Consumidor Final",
		"numeroDocumento" => $numero_de_documento, // Debe ser diferente al DNI del emisor
		"importeTotal" => $importe_total,
		"importeOtrosTributos" => 0.000,
		"importeGravado" => $importe_total,
		"importeNoGravado" => 0.000,
		"importeExento" => 0.000,
		"importeIVA" => 0.000,
		"codigoPais" => 200,
		"idiomaComprobante" => 1,
		"NroRemito" => 0,
		"CondicionVenta" => "Efectivo",
		"items" => Array(),
		"subtotivas" => Array(),
		"Tributos" => Array(),
		"CbtesAsoc" => Array()
	);
	
	error_log(PHP_EOL.$voucher, 3, "my-errors.log");

	$res = $afip->emitirComprobante($voucher);

	//error_log(PHP_EOL."cae : " . $result['cae'], 3, "my-errors.log");            
	//error_log(PHP_EOL."Fecha Vencimiento : ". $result['fechaVencimientoCAE'], 3, "my-errors.log");            
	//error_log(PHP_EOL."Numero Factura : ". $numero_de_factura, 3, "my-errors.log");            


	$sql = "update ventas_header set comprobante = '" . $numero_de_factura ."', cae = '" . $res['cae'] . "' where id = " . $id ;
	error_log(PHP_EOL.$sql, 3, "my-errors.log");            
	$db->query($sql);

}

header('location: ventas-a-facturar.php');
exit;

?>