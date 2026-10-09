<?php 

session_start();

require_once('../app/models/producto.php');
require_once('../app/models/compras.php');

if(isset($_REQUEST['action']) and $_REQUEST['action']!=""){

    if(isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
        $forwardOK = $_REQUEST['forwardOk'];
    } else {
        $forwardOK = "ubicar.php";
    }
    
    switch($_REQUEST["action"]) {
	case "remove":
		if(!empty($_SESSION["stock_item"])) {
            if(isset($_REQUEST['id']) and $_REQUEST['id']!=""){
                $id = $_REQUEST['id'];
                foreach($_SESSION["stock_item"] as $k => $v) {
                    if($id == $v["id"]) {
                          unset($_SESSION["stock_item"][$k]);
                    }
                    if(empty($_SESSION["stock_item"]))
                        unset($_SESSION["stock_item"]);
                    
                }
		    }
        }
	    break;
	case "empty":
		unset($_SESSION["stock_item"]);
        unset($_SESSION["stock_header"]);
	    break;
    case "ubicar":

        // ubico productos
        $ubicacion = $_REQUEST['ubicacion'];

        error_log(PHP_EOL."Ubicacion  2:  " . $ubicacion, 3, "my-errors.log");  

        $productoPDO = new Producto();
        $compraPDO = new Compra();

        foreach ($_SESSION["stock_item"] as $item){
            // 
            $idProducto =  $item["id"];
            $titulo = $item["titulo"];
            $cantidad = $item["cantidad"];
            
            // datos del producto 
            $rsProducto = $productoPDO->getById($idProducto);

            $editorial = $rsProducto["id_editorial"];
            $esNuevo = $rsProducto["nuevo"];
            $fecha = date("Y-m-d"); // valor por defecto
            $costo = $rsProducto["precio_costo"]; // valor por defecto
            
            // datos ultima compra
            $rsCompra = $compraPDO->getLastByProducto($idProducto);
            if (!is_null($rsCompra)){
                $fecha = $rsCompra["fecha"]; // fecha ultima compra
                $costo = $rsCompra["precio_costo"]; // consto ultima compra
            } 
            

            $sqlInsert = 'INSERT INTO productos_stock (id_producto, id_editorial, cantidad, id_ubicacion, fecha, costo, nuevo) 
            VALUES ('.$idProducto.','.$editorial.', '.$cantidad.', '.$ubicacion.', "'.$fecha.'", '.$costo.','.$esNuevo.')';
            
            error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  

            // $db->query($sqlInsert);
  
        }
    
        unset($productoPDO);

		unset($_SESSION["stock_item"]);
        unset($_SESSION["stock_header"]);
	    break;
    }

    header("location:". $forwardOK);
    exit;

}   else{
    // URL doesn't contain id parameter. Redirect to error page
    header("location: /error.php");
    exit();
}
?>