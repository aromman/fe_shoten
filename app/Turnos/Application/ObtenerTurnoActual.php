<?php

namespace App\Turnos\Application;

use App\Turnos\Domain\SaldoTurno;

final class ObtenerTurnoActual
{
    private $repository;

    public function __construct(TurnoReadRepository $repository)
    {
        $this->repository = $repository;
    }

    public function ejecutar($canalId)
    {
        $contexto = $this->repository->obtenerContextoActual((int) $canalId);
        $contexto['resumen'] = SaldoTurno::calcular($contexto['montos']);
        unset($contexto['montos']);

        return $contexto;
    }
}
