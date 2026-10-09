<?php

namespace App\Turnos\Application;

interface TurnoReadRepository
{
    public function obtenerContextoActual($canalId);
}
