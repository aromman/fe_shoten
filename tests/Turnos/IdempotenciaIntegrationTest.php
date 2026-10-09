<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Application/TurnoWriteRepository.php';
require_once dirname(__DIR__, 2) . '/app/models/turnos.php';
require_once dirname(__DIR__, 2) . '/app/Turnos/Infrastructure/LegacyTurnoWriteRepository.php';

use App\Turnos\Infrastructure\LegacyTurnoWriteRepository;

$repository = new LegacyTurnoWriteRepository();
$turnosModel = new Turnos();
$pdo = $turnosModel->pdo;
$key = 'test-idempotencia-' . bin2hex(random_bytes(8));
$resultadoEsperado = array('id' => 321, 'estado' => 'COMPLETADO');

$iniciar = new ReflectionMethod($repository, 'iniciarComando');
$iniciar->setAccessible(true);
$completar = new ReflectionMethod($repository, 'completarComando');
$completar->setAccessible(true);

try {
    $pdo->beginTransaction();
    $primero = $iniciar->invoke($repository, $pdo, $key, 'PRUEBA', 1, 'tester');
    if ($primero !== null) {
        throw new RuntimeException('La primera ejecución debe crear el comando.');
    }
    $completar->invoke($repository, $pdo, $key, $resultadoEsperado);
    $pdo->commit();

    $resultadoGuardado = $repository->obtenerResultadoIdempotente($key, 'PRUEBA', 1, 'tester');
    if ($resultadoGuardado !== $resultadoEsperado) {
        throw new RuntimeException('No se recuperó la respuesta idempotente guardada.');
    }

    $pdo->beginTransaction();
    $repetido = $iniciar->invoke($repository, $pdo, $key, 'PRUEBA', 1, 'tester');
    $pdo->commit();
    if ($repetido !== $resultadoEsperado) {
        throw new RuntimeException('La repetición no devolvió la respuesta original.');
    }
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $pdo->prepare('DELETE FROM turnos_comandos WHERE idempotency_key = ?')->execute(array($key));
}

echo "IdempotenciaIntegrationTest OK\n";
