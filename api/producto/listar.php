<?php

require_once "../../app/models/producto.php";

$tokens = array("fa3b2c9c-a96d-48a8-82ad-0cb775dd3e5d" => "");

error_log(PHP_EOL.$_SERVER['REQUEST_METHOD'], 3, "my-errors.log");

$headers =  getallheaders();
foreach($headers as $key=>$val){
    error_log(PHP_EOL.$key . ': ' . $val, 3, "my-errors.log");
}


if (isset($_SERVER["HTTP_X_API_KEY"])) {
    $requestData["token"] = $_SERVER["HTTP_X_API_KEY"];
}

error_log(PHP_EOL.$requestData["token"], 3, "my-errors.log");

if (!isset($requestData["token"])) {
    echo json_encode("No token was received to authorize the operation. Verify the information sent");
    exit;
}

if (!isset($tokens[$requestData["token"]])) {
    echo json_encode("The token " . $requestData["token"] . " does not exists or is not authorized to perform this operation.");
    exit;
}


$productoPDO = new Producto();
$productos = $productoPDO->getAllActive();

$responseData = json_encode($productos);
$httpHeaders = array('Content-Type: application/json', 'HTTP/1.1 200 OK');

header_remove('Set-Cookie');
if (is_array($httpHeaders) && count($httpHeaders)) {
    foreach ($httpHeaders as $httpHeader) {
        header($httpHeader);
    }
}
echo $responseData;
exit;


