<?php

require_once dirname(__DIR__, 2) . '/app/Turnos/Domain/ConteoBilletes.php';

use App\Turnos\Domain\ConteoBilletes;

$resultado = ConteoBilletes::calcular(array(
    '10' => 2,
    '500' => 3,
    '20000' => 1,
));

if ($resultado['total'] !== 21520.0) {
    throw new RuntimeException('El total del conteo de billetes no coincide.');
}

if ($resultado['cantidades']['1000'] !== 0) {
    throw new RuntimeException('Las denominaciones ausentes deben valer cero.');
}

echo "ConteoBilletesTest OK\n";
