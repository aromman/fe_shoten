<?php

namespace App\Turnos\Application;

use DateTimeImmutable;
use App\Turnos\Domain\ConteoBilletes;

final class AbrirTurno
{
    private $repository;

    public function __construct(TurnoWriteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function ejecutar($canalId, $fecha, $billetes, $username, $idempotencyKey)
    {
        $canalId = (int) $canalId;
        $fecha = trim((string) $fecha);
        $username = trim((string) $username);
        $idempotencyKey = $this->validarIdempotencyKey($idempotencyKey);

        if ($canalId <= 0) {
            throw new \InvalidArgumentException('CANAL_REQUERIDO');
        }

        $fechaValida = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new \InvalidArgumentException('FECHA_INVALIDA');
        }

        $conteo = ConteoBilletes::calcular($billetes);
        $montoApertura = $conteo['total'];
        if ($montoApertura > 999999999.99) {
            throw new \InvalidArgumentException('MONTO_INVALIDO');
        }

        if ($username === '') {
            throw new \InvalidArgumentException('USUARIO_REQUERIDO');
        }

        $resultadoAnterior = $this->repository->obtenerResultadoIdempotente(
            $idempotencyKey,
            'ABRIR_TURNO',
            $canalId,
            $username
        );
        if ($resultadoAnterior !== null) {
            return $resultadoAnterior;
        }

        return $this->repository->abrir(
            $canalId,
            $fecha,
            $montoApertura,
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
