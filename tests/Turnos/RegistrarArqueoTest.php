<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoReadRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoWriteRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/ConteoBilletes.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/SaldoTurno.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/RegistrarArqueo.php';

use App\Turnos\Application\RegistrarArqueo;
use App\Turnos\Application\TurnoReadRepository;
use App\Turnos\Application\TurnoWriteRepository;

final class TurnoReadRepositoryArqueoFake implements TurnoReadRepository
{
    public function obtenerContextoActual($canalId)
    {
        return array(
            'turno' => array('id' => 44, 'fecha' => '2026-06-24'),
            'montos' => array(
                'apertura' => 1000,
                'ventas' => 500,
                'depositos' => 200,
                'compras' => 100,
                'gastos' => 50,
                'extracciones' => 0,
            ),
        );
    }
}

final class TurnoWriteRepositoryArqueoFake implements TurnoWriteRepository
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

    public function registrarArqueo(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    ) {
        $this->argumentos = func_get_args();

        return array('id' => 99);
    }

    public function cerrar(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    ) {
        throw new RuntimeException('No se esperaba cerrar un turno.');
    }

    public function registrarMovimiento(
        $turnoId,
        $canalId,
        $fecha,
        $tipo,
        $detalle,
        $monto,
        $username,
        $idempotencyKey
    ) {
        throw new RuntimeException('No se esperaba registrar un movimiento.');
    }
}

$writeRepository = new TurnoWriteRepositoryArqueoFake();
$casoDeUso = new RegistrarArqueo(new TurnoReadRepositoryArqueoFake(), $writeRepository);
$resultado = $casoDeUso->ejecutar(7, array('500' => 3), 'cajero', 'arqueo-test-1');

if ($resultado['id'] !== 99) {
    throw new RuntimeException('Debe devolver el arqueo registrado.');
}

if ($writeRepository->argumentos[0] !== 44
    || $writeRepository->argumentos[1] !== 7
    || $writeRepository->argumentos[3] !== 1550.0
    || $writeRepository->argumentos[4] !== 1500.0
    || $writeRepository->argumentos[6] !== 'cajero'
    || $writeRepository->argumentos[7] !== 'arqueo-test-1'
) {
    throw new RuntimeException('Los valores del arqueo no fueron calculados correctamente.');
}

echo "RegistrarArqueoTest OK\n";
