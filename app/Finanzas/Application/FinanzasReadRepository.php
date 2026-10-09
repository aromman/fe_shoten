<?php

namespace App\Finanzas\Application;

interface FinanzasReadRepository
{
    public function obtenerResumen($canalId);
}
