<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoReadRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoWriteRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/ConteoBilletes.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/SaldoTurno.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/CerrarTurno.php';

use App\Turnos\Application\CerrarTurno;
use App\Turnos\Application\TurnoReadRepository;
use App\Turnos\Application\TurnoWriteRepository;

final class TurnoReadRepositoryCierreFake implements TurnoReadRepository
{
    public function obtenerContextoActual($canalId)
    {
        return array(
            'turno' => array('id' => 55, 'fecha' => '2026-06-24'),
            'montos' => array(
                'apertura' => 2000,
                'ventas' => 1000,
                'depositos' => 0,
                'compras' => 250,
                'gastos' => 100,
                'extracciones' => 0,
            ),
        );
    }
}

final class TurnoWriteRepositoryCierreFake implements TurnoWriteRepository
{
    public $argumentos;
    public $resultadoIdempotente;

    public function obtenerResultadoIdempotente($idempotencyKey, $tipo, $canalId, $username)
    {
        return $this->resultadoIdempotente;
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
        throw new RuntimeException('No se esperaba registrar un arqueo.');
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
        $this->argumentos = func_get_args();

        return array('id' => $turnoId, 'estado' => 'C');
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

final class TurnoReadRepositoryNoDebeEjecutarse implements TurnoReadRepository
{
    public function obtenerContextoActual($canalId)
    {
        throw new RuntimeException('Una repetición idempotente no debe volver a consultar el turno.');
    }
}

$writeRepository = new TurnoWriteRepositoryCierreFake();
$casoDeUso = new CerrarTurno(new TurnoReadRepositoryCierreFake(), $writeRepository);
$resultado = $casoDeUso->ejecutar(8, array('1000' => 2, '500' => 1), 'admin', 'cierre-test-1');

if ($resultado['estado'] !== 'C') {
    throw new RuntimeException('Debe devolver el turno cerrado.');
}

if ($writeRepository->argumentos[0] !== 55
    || $writeRepository->argumentos[1] !== 8
    || $writeRepository->argumentos[3] !== 2650.0
    || $writeRepository->argumentos[4] !== 2500.0
    || $writeRepository->argumentos[6] !== 'admin'
    || $writeRepository->argumentos[7] !== 'cierre-test-1'
) {
    throw new RuntimeException('Los valores del cierre no fueron calculados correctamente.');
}

$replayRepository = new TurnoWriteRepositoryCierreFake();
$replayRepository->resultadoIdempotente = array('id' => 55, 'estado' => 'C');
$replay = new CerrarTurno(new TurnoReadRepositoryNoDebeEjecutarse(), $replayRepository);
$resultadoReplay = $replay->ejecutar(8, array(), 'admin', 'cierre-test-replay');

if ($resultadoReplay !== $replayRepository->resultadoIdempotente) {
    throw new RuntimeException('La repetición debe devolver la respuesta idempotente original.');
}

echo "CerrarTurnoTest OK\n";
