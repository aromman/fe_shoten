<?php
error_reporting(0);

// La configuración de la base vive en app/config/db.local.php.
require_once __DIR__ . '/app/config/database.php';

// Create connection
$db     =   new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
