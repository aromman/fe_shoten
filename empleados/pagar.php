<?php 

include_once('../config.php');
require_once('../server.php');

if(isset($_REQUEST['id']) and $_REQUEST['id']!="" ){
    // recupero la venta
    
    $id_consignatario = $_REQUEST['id'];

    $sql = "update compras set id_estado = 6 WHERE id in
    (
        select 
        c.id
        from compras c, productos p
        where c.id_producto = p.id
        and p.id_editorial = ".$id_consignatario."
        and c.id_estado != 6
        and c.id in (select con.id_compra from consignaciones con)
    )
    ";
    //error_log(PHP_EOL.$sql, 3, "my-errors.log");      
    $result =   $db->query($sql);

    header('location: consignaciones.php');
    exit;
}
?>