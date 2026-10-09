<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoWriteRepository.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/ConteoBilletes.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Application/AbrirTurno.php';

use App\Turnos\Application\AbrirTurno;
use App\Turnos\Application\TurnoWriteRepository;

final class TurnoWriteRepositoryFake implements TurnoWriteRepository
{
    public $argumentos;

    public function obtenerResultadoIdempotente($idempotencyKey, $tipo, $canalId, $username)
    {
        return null;
    }

    public function abrir($canalId, $fecha, $montoApertura, $billetes, $username, $idempotencyKey)
    {
        $this->argumentos = func_get_args();

        return array('id' => 123, 'estado' => 'A');
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

function assertTrue($condicion, $mensaje)
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

$repository = new TurnoWriteRepositoryFake();
$casoDeUso = new AbrirTurno($repository);
$resultado = $casoDeUso->ejecutar(7, '2026-06-24', array(
    '500' => 1,
    '1000' => 1,
    '2000' => 2,
), 'operador', 'abrir-test-1');

assertTrue($resultado['id'] === 123, 'Debe devolver el turno creado.');
assertTrue($repository->argumentos === array(
    7,
    '2026-06-24',
    5500.0,
    array(
        '10' => 0,
        '20' => 0,
        '50' => 0,
        '100' => 0,
        '200' => 0,
        '500' => 1,
        '1000' => 1,
        '2000' => 2,
        '10000' => 0,
        '20000' => 0,
    ),
    'operador',
    'abrir-test-1',
), 'Debe calcular y normalizar el conteo de billetes.');

foreach (array(
    array(7, '24-06-2026', array(), 'operador', 'abrir-test-2'),
    array(7, '2026-02-30', array(), 'operador', 'abrir-test-3'),
    array(7, '2026-06-24', array('1000' => -1), 'operador', 'abrir-test-4'),
    array(7, '2026-06-24', array('1000' => '1.5'), 'operador', 'abrir-test-5'),
) as $argumentosInvalidos) {
    try {
        call_user_func_array(array($casoDeUso, 'ejecutar'), $argumentosInvalidos);
        throw new RuntimeException('Se esperaba una validación fallida.');
    } catch (InvalidArgumentException $error) {
        // Resultado esperado.
    }
}

echo "AbrirTurnoTest OK\n";
