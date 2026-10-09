<?php

namespace App\Turnos\Application;

use App\Turnos\Domain\ConteoBilletes;
use App\Turnos\Domain\SaldoTurno;

final class RegistrarArqueo
{
    private $readRepository;
    private $writeRepository;

    public function __construct(TurnoReadRepository $readRepository, TurnoWriteRepository $writeRepository)
    {
        $this->readRepository = $readRepository;
        $this->writeRepository = $writeRepository;
    }

    public function ejecutar($canalId, $billetes, $username, $idempotencyKey)
    {
        $canalId = (int) $canalId;
        $username = trim((string) $username);
        $idempotencyKey = $this->validarIdempotencyKey($idempotencyKey);

        if ($canalId <= 0) {
            throw new \InvalidArgumentException('CANAL_REQUERIDO');
        }

        if ($username === '') {
            throw new \InvalidArgumentException('USUARIO_REQUERIDO');
        }

        $resultadoAnterior = $this->writeRepository->obtenerResultadoIdempotente(
            $idempotencyKey,
            'REGISTRAR_ARQUEO',
            $canalId,
            $username
        );
        if ($resultadoAnterior !== null) {
            return $resultadoAnterior;
        }

        $contexto = $this->readRepository->obtenerContextoActual($canalId);
        if (!isset($contexto['turno']) || $contexto['turno'] === null) {
            throw new \DomainException('TURNO_NO_ABIERTO');
        }

        $conteo = ConteoBilletes::calcular($billetes);
        $resumen = SaldoTurno::calcular($contexto['montos']);

        return $this->writeRepository->registrarArqueo(
            $contexto['turno']['id'],
            $canalId,
            $contexto['turno']['fecha'],
            $resumen['saldoEsperado'],
            $conteo['total'],
            $conteo['cantidades'],
            $username,
            $idempotencyKey
        );
    }

    private function validarIdempotencyKey($idempotencyKey)
    {
        $idempotencyKey = trim((string) $idempotencyKey);
        if ($idempotencyKey === '' || strlen($idempotencyKey) > 64
            || !preg_match('/^[A-Za-z0-9._:-]+$/', $idempotencyKey)
        ) {
            throw new \InvalidArgumentException('IDEMPOTENCY_KEY_INVALIDA');
        }

        return $idempotencyKey;
    }
}
