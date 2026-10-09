<?php
error_reporting(0);
//define('DB_NAME', 'saikono_roi'); //Your DB Name
define('DB_NAME', 'roi_saiko'); //Your DB Name
define('DB_USER', 'root'); //Your DB User Name
define('DB_PASSWORD', ''); //Your DB Password
define('DB_HOST', 'localhost'); //Your Host Name
   
// Create connection
$db     =   new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
