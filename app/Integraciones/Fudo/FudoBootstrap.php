<?php

namespace App\Integraciones\Fudo;

final class FudoBootstrap
{
    public static function cliente()
    {
        self::cargarDependencias();

        return new FudoClient(self::configuracion());
    }

    public static function obtenerVentasMensuales()
    {
        self::cargarDependencias();

        return new ObtenerVentasMensualesFudo(
            new FudoClient(self::configuracion())
        );
    }

    public static function obtenerCuentasACobrar()
    {
        self::cargarDependencias();

        return new ObtenerCuentasACobrarFudo(
            new FudoClient(self::configuracion())
        );
    }

    public static function obtenerGastosAPagar()
    {
        self::cargarDependencias();

        return new ObtenerGastosAPagarFudo(
            new FudoClient(self::configuracion())
        );
    }

    public static function obtenerGastos()
    {
        self::cargarDependencias();

        return new ObtenerGastosFudo(
            new FudoClient(self::configuracion())
        );
    }

    public static function obtenerPromedioSueldos()
    {
        self::cargarDependencias();

        return new ObtenerPromedioSueldosFudo(
            new FudoClient(self::configuracion())
        );
    }

    public static function repositorioCredenciales()
    {
        self::cargarDependencias();

        return new FudoCredentialsRepository(self::cipher(false));
    }

    public static function repositorioCredencialesParaConfigurar()
    {
        self::cargarDependencias();

        return new FudoCredentialsRepository(self::cipher(true));
    }

    private static function configuracion()
    {
        if (self::keyProvider()->obtener() === '') {
            return FudoConfig::fromCredentials(array());
        }

        $credentials = self::repositorioCredenciales()->obtener();

        return FudoConfig::fromCredentials($credentials);
    }

    private static function cipher($crearSiFalta)
    {
        $provider = self::keyProvider();
        $key = $crearSiFalta ? $provider->obtenerOCrear() : $provider->obtener();

        return new \App\Integraciones\Seguridad\SecretCipher($key);
    }

    private static function keyProvider()
    {
        $root = dirname(__DIR__, 3);

        return new \App\Integraciones\Seguridad\AppSecretsKey(
            $root . '/app/config/secrets.local.php'
        );
    }

    private static function cargarDependencias()
    {
        $root = dirname(__DIR__, 3);

        require_once $root . '/app/models/constantes.php';
        require_once $root . '/app/models/connection.php';
        require_once dirname(__DIR__) . '/Seguridad/AppSecretsKey.php';
        require_once dirname(__DIR__) . '/Seguridad/SecretCipher.php';
        require_once __DIR__ . '/FudoException.php';
        require_once __DIR__ . '/FudoConfig.php';
        require_once __DIR__ . '/FudoCredentialsRepository.php';
        require_once __DIR__ . '/FudoClient.php';
        require_once __DIR__ . '/ObtenerVentasMensualesFudo.php';
        require_once __DIR__ . '/ObtenerCuentasACobrarFudo.php';
        require_once __DIR__ . '/ObtenerGastosAPagarFudo.php';
        require_once __DIR__ . '/ObtenerGastosFudo.php';
        require_once __DIR__ . '/ObtenerPromedioSueldosFudo.php';
    }
}
