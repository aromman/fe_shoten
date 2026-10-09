<?php 

// definir un array
$usuarios_a = "aromman";
$usuarios_l = "lorena";
$usuario_e = "ezequiel";

$usuarios = array( $usuarios_a, $usuarios_l, $usuario_e );

// alta de usuarios

foreach ($usuarios as $key => $value) {
    
$password = $value;

$hash_password = password_hash($password , PASSWORD_DEFAULT); // Creates a password hash


$sqlInsert = "INSERT INTO users (username)
SELECT * FROM (SELECT '".$value."') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = '".$value."'
) LIMIT 1;
update users set password = '".$hash_password."' where username = '".$value."'
;";

//error_log(PHP_EOL.$sqlInsert, 3, "alta_db.sql");    
}


// alta de prestadoras


$arr_cuits = array("1234564654","1321321546");
$arr_razon = array("Banco", "Obra Social");


foreach ($arr_cuits as $key => $cuit) {
    
    $razon = $arr_razon[$key];
    
    $sqlInsert = "INSERT INTO prestadoras (cuit)
    SELECT * FROM (SELECT '".$cuit."'  ) AS tmp
    WHERE NOT EXISTS (
        SELECT cuit FROM prestadoras WHERE cuit = '".$cuit."'
    ) LIMIT 1;
    update prestadoras set razon_social = '".$razon."' where cuit = '".$cuit."'
    ;";
    
    //error_log(PHP_EOL.$sqlInsert, 3, "alta_db.sql");    
    }
    

// alta de profesionales

    $arr_dni = array("41785485", "47865989");
    $arr_nombre = array("Ezequiel", "Jon");
    $arr_apellido = array("Romero Mansilla", "Doe");

    // $profesionales = array($arr_dni,$arr_nombre,$arr_apellido);
    
    foreach ($arr_dni as $key => $value) {
    
        $dni = $arr_dni[$key];
        $nombre = $arr_nombre[$key];
        $apellido = $arr_apellido[$key];
        
    
    $sqlInsert = "INSERT INTO profesionales (dni)
    SELECT * FROM (SELECT '".$dni."') AS tmp
    WHERE NOT EXISTS (
        SELECT dni FROM profesionales WHERE dni = '".$dni."'
    ) LIMIT 1;
    update profesionales set nombre = '".$nombre."',apellido = '".$apellido."' where dni = '".$dni."'
    ;";
    //error_log(PHP_EOL.$sqlInsert, 3, "alta_db.sql");    
    }
    