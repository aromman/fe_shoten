<?php

namespace App\Turnos\Application;

use DateTimeImmutable;
use DateTimeZone;

final class RegistrarMovimiento
{
    private $readRepository;
    private $writeRepository;

    public function __construct(TurnoReadRepository $readRepository, TurnoWriteRepository $writeRepository)
    {
        $this->readRepository = $readRepository;
        $this->writeRepository = $writeRepository;
    }

    public function ejecutar($canalId, $fecha, $tipo, $detalle, $monto, $username, $idempotencyKey)
    {
        $canalId = (int) $canalId;
        $fecha = trim((string) $fecha);
        $tipo = strtoupper(trim((string) $tipo));
        $detalle = trim((string) $detalle);
        $username = trim((string) $username);
        $idempotencyKey = $this->validarIdempotencyKey($idempotencyKey);

        if ($canalId <= 0) {
            throw new \InvalidArgumentException('CANAL_REQUERIDO');
        }

        $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('FECHA_INVALIDA');
        }

        if (!in_array($tipo, array('C', 'D'), true)) {
            throw new \InvalidArgumentException('TIPO_MOVIMIENTO_INVALIDO');
        }

        if ($detalle === '' || strlen($detalle) > 50) {
            throw new \InvalidArgumentException('DETALLE_MOVIMIENTO_INVALIDO');
        }

        if (!is_numeric($monto) || (float) $monto <= 0 || (float) $monto > 999999999.99) {
            throw new \InvalidArgumentException('MONTO_INVALIDO');
        }
        $monto = round((float) $monto, 2);

        if ($username === '') {
            throw new \InvalidArgumentException('USUARIO_REQUERIDO');
        }

        $resultadoAnterior = $this->writeRepository->obtenerResultadoIdempotente(
            $idempotencyKey,
            'REGISTRAR_MOVIMIENTO',
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

        if ($fecha < $contexto['turno']['fecha']) {
            throw new \InvalidArgumentException('FECHA_ANTERIOR_AL_TURNO');
        }

        $horaActual = (new DateTimeImmutable('now', new DateTimeZone('America/Argentina/Buenos_Aires')))->format('H:i:s');
        $fechaHora = $fecha . ' ' . $horaActual;

        return $this->writeRepository->registrarMovimiento(
            $contexto['turno']['id'],
            $canalId,
            $fechaHora,
            $tipo,
            $detalle,
            $monto,
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
