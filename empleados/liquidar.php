<?php 

include_once('../config.php');
require_once('../server.php');

if(isset($_REQUEST['id']) and $_REQUEST['id']!=""
  and isset($_REQUEST['fecha']) and $_REQUEST['fecha']!=""
){
    // recupero la venta
    
    $fecha = $_REQUEST['fecha'];
    $id_editorial = $_REQUEST['id'];

    $sql = "select 
            v.id,
            v.fecha,
            v.id_producto,
            v.unidades,
            v.precio_unitario,
            v.precio_unitario - ((IFNULL(e.monto_fijo, 0) + ( (v.precio_unitario - IFNULL(e.monto_fijo, 0)) * IFNULL(e.porcentaje,0)/100)))  precio_compra
            from ventas v, productos p, editoriales e 
            where e.id = ".$id_editorial." and v.fecha = '".$fecha."' and v.id_producto = p.id and p.id_editorial = e.id";

    //error_log(PHP_EOL.$sql, 3, "my-errors.log");              

    $rsventas = mysqli_query($link,$sql);
    if (mysqli_num_rows($rsventas) > 0) {

        while($row = mysqli_fetch_assoc($rsventas))
        {
            $id_venta = $row['id'];
            $id_estado = 5; // Estado Entregado

            $fecha = $row['fecha'];
            $id_producto = $row['id_producto'];
            $cantidad = $row['unidades'];
            $precio_lista = $row['precio_unitario'];
            $precio_costo = $row['precio_compra'];

            $sqlInsert = "INSERT INTO compras (fecha, id_producto, cantidad, precio_lista, precio_costo, id_estado)
            values ('".$fecha."',".$id_producto.",".$cantidad.", ".$precio_lista.",".$precio_costo.",".$id_estado.");";  

            //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");            
            $db->query($sqlInsert);

            // inserto la consignacion    
            $id_compra = $db->insert_id;

            $sqlInsert = "INSERT INTO consignaciones (fecha, id_venta, id_compra, id_estado) 
            values ('".$fecha."',".$id_venta.",".$id_compra.", ".$id_estado.");";  
            //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");
            $db->query($sqlInsert);
        }
    }
    header('location:'.$_REQUEST['forwardOk']);
    exit;
}
?>