<?php

/**
 * Devuelve la URL base de la aplicación sin una barra final.
 *
 * Si está definida la variable de entorno APP_URL se usa tal cual, por ejemplo:
 * APP_URL=https://app.shoten.com.ar
 *
 * Si no, se arma con el host de la petición y la carpeta donde está instalada
 * la aplicación respecto del document root del servidor. Así funciona igual en
 * http://localhost/shoten/fe_shoten y en la raíz de un subdominio.
 */
function app_base_url()
{
    static $baseUrl = null;

    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $configuredUrl = getenv('APP_URL');
    if ($configuredUrl !== false && trim($configuredUrl) !== '') {
        $baseUrl = rtrim(trim($configuredUrl), '/');
        return $baseUrl;
    }

    $httpsEnabled = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $httpsEnabled ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

    // Evita incorporar caracteres inesperados provenientes del encabezado Host.
    if (!preg_match('/^[a-z0-9._-]+(?::[0-9]+)?$/i', $host)) {
        $host = 'localhost';
    }

    $baseUrl = $scheme . '://' . $host . app_base_path();

    return $baseUrl;
}

/**
 * Carpeta de la aplicación respecto del document root ('' si está en la raíz),
 * por ejemplo '/shoten/fe_shoten'.
 */
function app_base_path()
{
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $appRoot = realpath(dirname(__DIR__, 2));

    if ($documentRoot === false || $appRoot === false) {
        return '';
    }

    $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
    $appRoot = rtrim(str_replace('\\', '/', $appRoot), '/');

    // En Windows las rutas no distinguen mayúsculas (c:/winnmp/www vs C:\WinNMP\WWW).
    if (stripos($appRoot, $documentRoot) !== 0) {
        return '';
    }

    $relative = substr($appRoot, strlen($documentRoot));
    if ($relative === '' || $relative === false) {
        return '';
    }

    return '/' . implode('/', array_map('rawurlencode', explode('/', trim($relative, '/'))));
}

/**
 * Construye una URL absoluta hacia una ruta interna de la aplicación.
 */
function app_url($path = '')
{
    return app_base_url() . ($path === '' ? '' : '/' . ltrim($path, '/'));
}
