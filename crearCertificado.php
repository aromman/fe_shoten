<?php 
include 'afip/Afip.php'; 

// CUIT al cual le queremos generar el certificado
$tax_id = 27440427109; 

// Usuario para ingresar a AFIP.
// Para la mayoria es el mismo CUIT, pero al administrar
// una sociedad el CUIT con el que se ingresa es el del administrador
// de la sociedad.
$username = '27440427109'; 

// Contraseña para ingresar a AFIP.
$password = '2008Romero';

// Alias para el certificado (Nombre para reconocerlo en AFIP)
// un alias puede tener muchos certificados, si estas renovando
// un certificado podes utilizar el mismo alias
$alias = 'saikoProd';

// Creamos una instancia de la libreria
$afip = new Afip(array('CUIT' => $tax_id, 	'access_token' => 'bdcvzbs4zqZdoRKnMOj38KtL98FILMEyi0NJquQwTmnFmq5aquqRGijYUk8Dr7Kc',
'production' => TRUE
));

// Creamos el certificado (¡Paciencia! Esto toma unos cuantos segundos)
//$res = $afip->CreateCert($username, $password, $alias);

// Mostramos el certificado por pantalla
//var_dump($res->cert);

// Mostramos la key por pantalla
//var_dump($res->key);

// ATENCION! Recorda guardar el cert y key ya que 
// la libreria por seguridad no los guarda, esto depende de vos.
// Si no lo guardas vas tener que generar uno nuevo con este metodo

// Id del web service a autorizar
$wsid = 'wsfe';

// Creamos la autorizacion (¡Paciencia! Esto toma unos cuantos segundos)
$res = $afip->CreateWSAuth($username, $password, $alias, $wsid);

// Mostramos el resultado por pantalla
var_dump($res);


?>