<?php

namespace App\Turnos;

use App\Turnos\Application\AbrirTurno;
use App\Turnos\Application\CerrarTurno;
use App\Turnos\Application\ObtenerTurnoActual;
use App\Turnos\Application\RegistrarArqueo;
use App\Turnos\Application\RegistrarMovimiento;
use App\Turnos\Infrastructure\LegacyTurnoReadRepository;
use App\Turnos\Infrastructure\LegacyTurnoWriteRepository;

final class TurnosBootstrap
{
    public static function obtenerTurnoActual()
    {
        self::cargarDependencias();

        return new ObtenerTurnoActual(new LegacyTurnoReadRepository());
    }

    public static function abrirTurno()
    {
        self::cargarDependencias();

        return new AbrirTurno(new LegacyTurnoWriteRepository());
    }

    public static function registrarArqueo()
    {
        self::cargarDependencias();

        return new RegistrarArqueo(
            new LegacyTurnoReadRepository(),
            new LegacyTurnoWriteRepository()
        );
    }

    public static function cerrarTurno()
    {
        self::cargarDependencias();

        return new CerrarTurno(
            new LegacyTurnoReadRepository(),
            new LegacyTurnoWriteRepository()
        );
    }

    public static function registrarMovimiento()
    {
        self::cargarDependencias();

        return new RegistrarMovimiento(
            new LegacyTurnoReadRepository(),
            new LegacyTurnoWriteRepository()
        );
    }

    private static function cargarDependencias()
    {
        $root = dirname(__DIR__, 2);

        require_once __DIR__ . '/Application/TurnoReadRepository.php';
        require_once __DIR__ . '/Application/TurnoWriteRepository.php';
        require_once __DIR__ . '/Application/ObtenerTurnoActual.php';
        require_once __DIR__ . '/Application/AbrirTurno.php';
        require_once __DIR__ . '/Application/CerrarTurno.php';
        require_once __DIR__ . '/Application/RegistrarArqueo.php';
        require_once __DIR__ . '/Application/RegistrarMovimiento.php';
        require_once __DIR__ . '/Domain/SaldoTurno.php';
        require_once __DIR__ . '/Domain/ConteoBilletes.php';
        require_once __DIR__ . '/Infrastructure/LegacyTurnoReadRepository.php';
        require_once __DIR__ . '/Infrastructure/LegacyTurnoWriteRepository.php';
        require_once $root . '/app/models/canal.php';
        require_once $root . '/app/models/turnos.php';
        require_once $root . '/app/models/turnoParcial.php';
        require_once $root . '/app/models/ventas.php';
        require_once $root . '/app/models/gastos.php';
        require_once $root . '/app/models/compras.php';
        require_once $root . '/app/models/cuentasMovimientos.php';
    }
}
