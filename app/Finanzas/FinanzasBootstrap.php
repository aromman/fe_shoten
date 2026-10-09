<?php

namespace App\Finanzas;

use App\Finanzas\Application\ObtenerResumenFinanciero;
use App\Finanzas\Infrastructure\LegacyFinanzasReadRepository;

final class FinanzasBootstrap
{
    public static function obtenerResumen()
    {
        self::cargarDependencias();

        return new ObtenerResumenFinanciero(new LegacyFinanzasReadRepository());
    }

    private static function cargarDependencias()
    {
        $root = dirname(__DIR__, 2);

        require_once $root . '/app/models/constantes.php';
        require_once $root . '/app/models/connection.php';
        require_once __DIR__ . '/Application/FinanzasReadRepository.php';
        require_once __DIR__ . '/Application/ObtenerResumenFinanciero.php';
        require_once __DIR__ . '/Infrastructure/LegacyFinanzasReadRepository.php';
    }
}
