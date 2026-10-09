<?php 
require_once "../server.php";


// recupero ultimo id liquidacion
$id_liquidacion = 0;
$sql = "select max(id) as id_liquidacion from liquidacion";
if($stmt = mysqli_prepare($link, $sql)){
    // Attempt to execute the prepared statement
    if(mysqli_stmt_execute($stmt)){
        // Store result
        mysqli_stmt_store_result($stmt);
        
        // Check if username exists, if yes then verify password
        if(mysqli_stmt_num_rows($stmt) == 1){                    
            // Bind result variables
            mysqli_stmt_bind_result($stmt, $id_liquidacion);
            if(mysqli_stmt_fetch($stmt)){
            } 
        } 
    } 
}   

// inserto liquidacion
$sql = "delete from liquidacion where id = " . $id_liquidacion;
if($stmt = mysqli_prepare($link, $sql)){
    if(mysqli_stmt_execute($stmt)){
    } 
}   

// vuelvo a liquidaciones
header('location: ' . $_SERVER['HTTP_REFERER']);
exit;

?>