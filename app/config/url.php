<?php

/**
 * Devuelve la URL base de la aplicación sin una barra final.
 *
 * En producción se puede definir APP_URL, por ejemplo:
 * APP_URL=https://sistema.ejemplo.com/saiko
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

    $hostWithoutPort = preg_replace('/:[0-9]+$/', '', $host);
    $basePath = $hostWithoutPort === 'saikono_roi.test' ? '' : '/saiko';
    $baseUrl = $scheme . '://' . $host . $basePath;

    return $baseUrl;
}

/**
 * Construye una URL absoluta hacia una ruta interna de la aplicación.
 */
function app_url($path = '')
{
    return app_base_url() . ($path === '' ? '' : '/' . ltrim($path, '/'));
}
