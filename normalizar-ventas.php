<?php 
require_once "server.php";

$sql = 

$result = mysqli_query($link,$sql);


$fecha = $_POST["fecha"];
$cliente = $_POST["cliente"];
$medioPago = $_POST["medioPago"];
$cantidad = $_POST["cantidad"];
$subTotal = $_POST["subTotal"];
$descuento = $_POST["descuento"]; 
$total = $_POST["total"];
$id_canal = $_SESSION["user.canal"];

$sqlCampos = 'id_canal, fecha, id_cliente, id_medio_pago, cantidad, subTotal, total';
$sqlValues =  $id_canal .', "'.$fecha.'",'.$cliente.','.$medioPago.','.$cantidad.','.$subTotal.','.$total;

if (!empty($descuento)){
    $sqlCampos = $sqlCampos . ', descuento';
    $sqlValues = $sqlValues . ','. $descuento;
}

$sqlInsert = 'INSERT INTO ventas_header ('.$sqlCampos.') VALUES ('.$sqlValues.')';
//error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
$db->query($sqlInsert);

$id_header = $db->insert_id;



// vuelvo a liquidaciones
header('location: ' . $_SERVER['HTTP_REFERER']);
exit;

?>