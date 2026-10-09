<?php

/**
 * Configuración única de la base de datos.
 *
 * Los valores se leen de app/config/db.local.php (no versionado). Para crearlo,
 * copiar app/config/db.example.php y completar los datos del entorno.
 *
 * Define las constantes que usan las distintas capas del sistema:
 * - app/models/constantes.php (PDO):  DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_DRIVER, DB_CHARSET
 * - config.php (mysqli, legacy):      DB_HOST, DB_USER, DB_PASSWORD, DB_NAME
 * - server.php (mysqli, legacy):      DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME
 */

if (!function_exists('app_database_config')) {
    function app_database_config()
    {
        static $config = null;

        if ($config !== null) {
            return $config;
        }

        $path = __DIR__ . '/db.local.php';
        if (!is_file($path)) {
            error_log('[database] Falta ' . $path . ' (copiar app/config/db.example.php).');
            die('Falta la configuración de la base de datos (app/config/db.local.php).');
        }

        $local = require $path;
        $config = array_merge(
            array(
                'host' => 'localhost',
                'name' => '',
                'user' => '',
                'pass' => '',
                'driver' => 'mysql',
                'charset' => 'utf8',
            ),
            is_array($local) ? $local : array()
        );

        return $config;
    }
}

$appDbConfig = app_database_config();
$appDbConstants = array(
    'DB_HOST' => $appDbConfig['host'],
    'DB_SERVER' => $appDbConfig['host'],
    'DB_NAME' => $appDbConfig['name'],
    'DB_USER' => $appDbConfig['user'],
    'DB_USERNAME' => $appDbConfig['user'],
    'DB_PASS' => $appDbConfig['pass'],
    'DB_PASSWORD' => $appDbConfig['pass'],
    'DB_DRIVER' => $appDbConfig['driver'],
    'DB_CHARSET' => $appDbConfig['charset'],
);
foreach ($appDbConstants as $appDbConstant => $appDbValue) {
    if (!defined($appDbConstant)) {
        define($appDbConstant, $appDbValue);
    }
}
unset($appDbConfig, $appDbConstants, $appDbConstant, $appDbValue);
