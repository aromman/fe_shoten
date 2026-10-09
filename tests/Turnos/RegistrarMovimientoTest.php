<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoReadRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoWriteRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/RegistrarMovimiento.php';

use App\Turnos\Application\RegistrarMovimiento;
use App\Turnos\Application\TurnoReadRepository;
use App\Turnos\Application\TurnoWriteRepository;

final class TurnoReadRepositoryMovimientoFake implements TurnoReadRepository
{
    public function obtenerContextoActual($canalId)
    {
        return array('turno' => array('id' => 77, 'fecha' => '2026-06-20'));
    }
}

final class TurnoWriteRepositoryMovimientoFake implements TurnoWriteRepository
{
    public $argumentos;

    public function obtenerResultadoIdempotente($idempotencyKey, $tipo, $canalId, $username)
    {
        return null;
    }

    public function abrir($canalId, $fecha, $montoApertura, $billetes, $username, $idempotencyKey)
    {
        throw new RuntimeException('No se esperaba abrir un turno.');
    }

    public function registrarArqueo($turnoId, $canalId, $fecha, $montoEsperado, $montoContado, $billetes, $username, $idempotencyKey)
    {
        throw new RuntimeException('No se esperaba registrar un arqueo.');
    }

    public function cerrar($turnoId, $canalId, $fecha, $montoEsperado, $montoContado, $billetes, $username, $idempotencyKey)
    {
        throw new RuntimeException('No se esperaba cerrar un turno.');
    }

    public function registrarMovimiento($turnoId, $canalId, $fecha, $tipo, $detalle, $monto, $username, $idempotencyKey)
    {
        $this->argumentos = func_get_args();

        return array('id' => 88);
    }
}

$repository = new TurnoWriteRepositoryMovimientoFake();
$casoDeUso = new RegistrarMovimiento(new TurnoReadRepositoryMovimientoFake(), $repository);
$resultado = $casoDeUso->ejecutar(
    3,
    '2026-06-24',
    'c',
    'Ingreso manual',
    '1500.50',
    'cajero',
    'movimiento-test-1'
);

if ($resultado['id'] !== 88) {
    throw new RuntimeException('Debe devolver el movimiento registrado.');
}

if ($repository->argumentos !== array(
    77,
    3,
    '2026-06-24',
    'C',
    'Ingreso manual',
    1500.50,
    'cajero',
    'movimiento-test-1',
)) {
    throw new RuntimeException('Los datos del movimiento no fueron normalizados correctamente.');
}

echo "RegistrarMovimientoTest OK\n";
