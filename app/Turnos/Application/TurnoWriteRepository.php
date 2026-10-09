<?php

namespace App\Turnos\Application;

interface TurnoWriteRepository
{
    public function obtenerResultadoIdempotente($idempotencyKey, $tipo, $canalId, $username);

    public function abrir($canalId, $fecha, $montoApertura, $billetes, $username, $idempotencyKey);

    public function registrarArqueo(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    );

    public function cerrar(
        $turnoId,
        $canalId,
        $fecha,
        $montoEsperado,
        $montoContado,
        $billetes,
        $username,
        $idempotencyKey
    );

    public function registrarMovimiento(
        $turnoId,
        $canalId,
        $fecha,
        $tipo,
        $detalle,
        $monto,
        $username,
        $idempotencyKey
    );
}
