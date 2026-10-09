<?php 

echo "Comenzando"."<BR>";

chdir(dirname(__FILE__));

include_once '../neofactura/wsfev1.php';
include_once '../neofactura/wsaa.php';
require_once "../app/models/generica.php";

$genericPDO = new Generica();

echo "Seleccionar Operacion"."<BR>"; 

$sql = "select vh.id, vh.fecha,
CONCAT (cli.apellido,' ',cli.nombre)  cliente,
mp.nombre medioPago,
vh.cantidad unidades,
vh.subTotal,
vh.descuento,
vh.total,
c.nombre canal,
c.punto_venta,
vh.username,
vh.updateDate,
vh.comprobante comprobante,
vh.cae cae
from ventas_header vh, canal c, clientes cli, medio_pago mp, cuentas cu
where vh.id_canal = c.id
and vh.id_cliente = cli.id
and vh.id_medio_pago = mp.id
and mp.id_cuenta = cu.id
and cu.tipo = 'A'
and vh.fecha >=  (CURRENT_DATE - INTERVAL 6 DAY)
and vh.comprobante = ''
order by vh.fecha asc, vh.id asc limit 1";

$result = $genericPDO->getSql($sql);

if (!is_null($result) ) {

    //$CUIT = "27440427109"; // CUIT del emisor
    $CUIT = "20237843097"; // CUIT del emisor
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


    foreach ($result as $row) {    


        $punto_de_venta = $row['punto_venta'];
        $fechaFactura = date('Ymd', strtotime($row['fecha']));
        $importe_total = number_format($row['total'], 2, ".", ""); 
        $id = $row['id'];


        $afip = new Wsfev1($CUIT,$MODO);

	    $last_voucher = $afip->consultarUltimoComprobanteAutorizado($punto_de_venta,$tipo_de_comprobante);

        echo "Ultima Factura : " . $last_voucher ."<BR>"; 

	    $numero_de_factura = $last_voucher+1;

        $voucher = Array
        (
            "idVoucher" => 1,
            "numeroComprobante" => $numero_de_factura,
            "numeroPuntoVenta" => $punto_de_venta,
            "cae" => 0,
            "letra" => "C",
            "fechaVencimientoCAE" => "",
            "tipoResponsable" => "6",  // Consumidor Final
            "nombreCliente" =>  "",
            "domicilioCliente" => "",
            "fechaComprobante" => $fechaFactura,
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
	
    echo "Emitir Factura". "<BR>";    

    try {
        $res = $afip->emitirComprobante($voucher);

        echo "Actualizar Operacion OK"."<BR>" ;    
        $sql = "update ventas_header set comprobante = '" . $numero_de_factura ."', cae = '" . $res['cae'] . "' where id = " . $id ;
        $genericPDO->updateSql($sql);

    } catch (Exception $e) {
            echo "Error Code ".$e->getCode() ."<BR>" ;    
            echo "Error ".$e->getMessage() ."<BR>" ;    

            if ($e->getCode() == 10016){
                echo "Actualizar Operacion Vencida"."<BR>" ;    
                $sql = "update ventas_header set comprobante = 'NA', cae = 'NA' where id = " . $id ;
                $genericPDO->updateSql($sql);

            }

    }


    echo "Fin";        

    }
} else {
    echo "No hay facturas"."<BR>";
}

?>