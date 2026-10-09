<?php 

include_once('../config.php');

if (isset($_POST['id'])) {
    $sql = "Select * from productos where id = " . $_POST['id'];
    $productByCode = $db->query($sql);
    $row  =   $productByCode->fetch_assoc();
    $data['id'] = $row['id'];
    $data['sku'] = $row['sku'] . ' (CLONE)';
    $data['titulo'] = $row['titulo'] . ' (CLONE)';
    $data['tomo'] = $row['tomo'];

    echo json_encode($data);
}        
?>