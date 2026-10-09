<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/ticketPromedio.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTORIZADO')), 403);
}

$formatearPeriodo = function ($periodo) {
    $meses = array(
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    );
    $partes = explode('-', (string) $periodo);
    if (count($partes) !== 2 || !isset($meses[$partes[1]])) {
        return (string) $periodo;
    }

    return $meses[$partes[1]] . ' ' . $partes[0];
};

// Gastos operativos: gastos locales de la tabla "gastos" del mes, sin filtrar por canal
// porque Sistema 4C consolida todo el negocio (igual que las ventas).
$gastosOperativosMesActual = function () {
    $modeloGastos = new Gastos();
    $gastosLocal = round((float) $modeloGastos->getTotalMesActual(), 2);

    return array(
        'local' => $gastosLocal,
        'total' => $gastosLocal,
    );
};

try {
    $zona = new DateTimeZone('America/Argentina/Buenos_Aires');
    $fechaActual = (new DateTimeImmutable('first day of this month', $zona))->setTime(0, 0);
    $periodoActual = $fechaActual->format('Y-m');

    $modelo = new TicketPromedio();
    $resumenLocal = $modelo->getResumenMesActual();
    $ventasLocal = isset($resumenLocal['ventas']) ? (float) $resumenLocal['ventas'] : 0;
    $ventasTotal = $ventasLocal;

    $gastosOperativos = $gastosOperativosMesActual();

    // Estimado a hoy: gastos operativos totales / dias del mes (promedio diario), multiplicado
    // por la cantidad de dias que ya pasaron del mes.
    $hoy = new DateTimeImmutable('today', $zona);
    $diasTranscurridos = max(1, (int) $hoy->format('j'));
    $diasEnMes = max(1, (int) $fechaActual->format('t'));
    $estimadoAHoy = round(($gastosOperativos['total'] / $diasEnMes) * $diasTranscurridos, 2);

    $objetivoCostoOperacion = round($ventasTotal * 0.65, 2);
    $ratioEstimado = $objetivoCostoOperacion > 0 ? ($estimadoAHoy * 100) / $objetivoCostoOperacion : 0;
    if ($ratioEstimado > 100) {
        $tonoCostoOperacion = 'danger';
    } elseif ($ratioEstimado >= 50) {
        $tonoCostoOperacion = 'warning';
    } else {
        $tonoCostoOperacion = 'success';
    }

    // Reserva a hoy: lo que queda del total vendido despues de cubrir el estimado de gastos
    // operativos a hoy (nunca negativo).
    $objetivoReservaImpuestos = round($ventasTotal * 0.20, 2);
    $reservaAHoy = $ventasTotal > $estimadoAHoy ? round($ventasTotal - $estimadoAHoy, 2) : 0.0;
    $ratioReserva = $objetivoReservaImpuestos > 0 ? ($reservaAHoy * 100) / $objetivoReservaImpuestos : 0;
    if ($ratioReserva < 50) {
        $tonoReservaImpuestos = 'danger';
    } elseif ($ratioReserva <= 100) {
        $tonoReservaImpuestos = 'warning';
    } else {
        $tonoReservaImpuestos = 'success';
        // por encima del objetivo: se muestra acotado al mismo valor del 20%, no el excedente.
        $reservaAHoy = $objetivoReservaImpuestos;
    }

    // Ganancia hoy: lo que resta del total vendido despues del estimado de gastos operativos y
    // de la reserva a hoy. Mismo criterio de semaforo que Reserva Impuestos.
    $objetivoGananciaReal = round($ventasTotal * 0.10, 2);
    $gananciaHoy = round($ventasTotal - $estimadoAHoy - $reservaAHoy, 2);
    $ratioGanancia = $objetivoGananciaReal > 0 ? ($gananciaHoy * 100) / $objetivoGananciaReal : 0;
    if ($ratioGanancia < 50) {
        $tonoGananciaReal = 'danger';
    } elseif ($ratioGanancia <= 100) {
        $tonoGananciaReal = 'warning';
    } else {
        $tonoGananciaReal = 'success';
        // por encima del objetivo: se muestra acotado al mismo valor del 10%, no el excedente.
        $gananciaHoy = $objetivoGananciaReal;
    }

    // Fondo a hoy: lo que resta del total vendido despues del estimado de gastos operativos, la
    // reserva a hoy y la ganancia hoy. Mismo criterio de semaforo que las anteriores.
    $objetivoFondoCrecimiento = round($ventasTotal * 0.05, 2);
    $fondoAHoy = round($ventasTotal - $estimadoAHoy - $reservaAHoy - $gananciaHoy, 2);
    $ratioFondo = $objetivoFondoCrecimiento > 0 ? ($fondoAHoy * 100) / $objetivoFondoCrecimiento : 0;
    if ($ratioFondo < 50) {
        $tonoFondoCrecimiento = 'danger';
    } elseif ($ratioFondo <= 100) {
        $tonoFondoCrecimiento = 'warning';
    } else {
        $tonoFondoCrecimiento = 'success';
        // por encima del objetivo: se muestra acotado al mismo valor del 5%, no el excedente.
        $fondoAHoy = $objetivoFondoCrecimiento;
    }

    // Resultado: lo que queda del total vendido despues de los 4 estimados a hoy.
    $resultado = round($ventasTotal - ($estimadoAHoy + $reservaAHoy + $gananciaHoy + $fondoAHoy), 2);
    $porcentajeResultado = $ventasTotal > 0 ? round(($resultado * 100) / $ventasTotal, 2) : 0;
    $tonoResultado = $resultado > 0 ? 'success' : 'danger';

    $cards = array(
        array(
            'titulo' => 'Costo Operación',
            'porcentaje' => 65,
            'valor' => $objetivoCostoOperacion,
            'segundaFilaEtiqueta' => 'Estimado a hoy',
            'segundaFilaValor' => $estimadoAHoy,
            'icono' => 'fa-gears',
            'tono' => $tonoCostoOperacion,
        ),
        array(
            'titulo' => 'Reserva Impuestos',
            'porcentaje' => 20,
            'valor' => $objetivoReservaImpuestos,
            'segundaFilaEtiqueta' => 'Reserva a hoy',
            'segundaFilaValor' => $reservaAHoy,
            'icono' => 'fa-file-invoice-dollar',
            'tono' => $tonoReservaImpuestos,
        ),
        array(
            'titulo' => 'Ganancia Real',
            'porcentaje' => 10,
            'valor' => $objetivoGananciaReal,
            'segundaFilaEtiqueta' => 'Ganancia hoy',
            'segundaFilaValor' => $gananciaHoy,
            'icono' => 'fa-sack-dollar',
            'tono' => $tonoGananciaReal,
        ),
        array(
            'titulo' => 'Fondo de Crecimiento',
            'porcentaje' => 5,
            'valor' => $objetivoFondoCrecimiento,
            'segundaFilaEtiqueta' => 'Fondo a hoy',
            'segundaFilaValor' => $fondoAHoy,
            'icono' => 'fa-seedling',
            'tono' => $tonoFondoCrecimiento,
        ),
        array(
            'titulo' => 'Resultado',
            'porcentaje' => $porcentajeResultado,
            'valor' => $resultado,
            'primeraFilaEtiqueta' => 'Saldo',
            'segundaFilaEtiqueta' => 'Rentabilidad',
            'segundaFilaValor' => $porcentajeResultado,
            'segundaFilaFormato' => 'porcentaje',
            'icono' => 'fa-scale-balanced',
            'tono' => $tonoResultado,
        ),
    );

    JsonResponse::enviar(array(
        'data' => array(
            'periodo' => $periodoActual,
            'periodoEtiqueta' => $formatearPeriodo($periodoActual),
            'ventasLocal' => round($ventasLocal, 2),
            'ventasTotal' => round($ventasTotal, 2),
            'gastosOperativos' => $gastosOperativos,
            'cards' => $cards,
        ),
        'meta' => array(
            'version' => 'v1',
            'actualizadoEn' => date(DATE_ATOM),
        ),
    ), 200);
} catch (Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
