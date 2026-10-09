<?php

namespace App\Finanzas\Application;

final class ObtenerResumenFinanciero
{
    private $repository;

    public function __construct(FinanzasReadRepository $repository)
    {
        $this->repository = $repository;
    }

    public function ejecutar($canalId)
    {
        $canalId = (int) $canalId;
        if ($canalId <= 0) {
            throw new \InvalidArgumentException('El canal es obligatorio.');
        }

        return $this->repository->obtenerResumen($canalId);
    }
}
