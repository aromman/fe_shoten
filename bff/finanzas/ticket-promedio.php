<?php

session_start();
ini_set('serialize_precision', '-1');

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/models/ticketPromedio.php';
require_once dirname(__DIR__, 2) . '/app/models/objetivo.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

$rol = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;
if ($rol !== 0) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTORIZADO')), 403);
}

$normalizarObjetivo = function ($row, $actual) {
    $objetivo = is_array($row) && isset($row['objetivo']) ? (float) $row['objetivo'] : 0;
    $porcentaje = $objetivo > 0 ? min(999, round(((float) $actual * 100) / $objetivo, 2)) : 0;
    $estado = 'sin_objetivo';
    if ($objetivo > 0) {
        if ($porcentaje > 80) {
            $estado = 'ok';
        } elseif ($porcentaje >= 30) {
            $estado = 'alerta';
        } else {
            $estado = 'riesgo';
        }
    }

    return array(
        'id' => is_array($row) && isset($row['id']) ? (int) $row['id'] : 0,
        'nombre' => is_array($row) && isset($row['nombre']) ? (string) $row['nombre'] : '',
        'objetivo' => round($objetivo, 2),
        'logrado' => round((float) $actual, 2),
        'porcentaje' => $porcentaje,
        'estado' => $estado,
    );
};

$cardsObjetivo = array(
    array(
        'titulo' => 'Ventas',
        'formato' => 'numero',
        'clave' => 'operaciones',
    ),
    array(
        'titulo' => 'Promedio de Venta',
        'formato' => 'moneda',
        'clave' => 'ticketPromedio',
    ),
    array(
        'titulo' => 'Personas',
        'formato' => 'numero',
        'clave' => 'personas',
    ),
    array(
        'titulo' => 'Promedio por persona',
        'formato' => 'moneda',
        'clave' => 'promedioPersona',
    ),
    array(
        'titulo' => 'Monto total vendido',
        'formato' => 'moneda',
        'clave' => 'ventas',
    ),
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = isset($_POST['accion']) ? (string) $_POST['accion'] : '';
    if ($accion !== 'guardarObjetivo') {
        JsonResponse::enviar(array('error' => array('codigo' => 'ACCION_INVALIDA')), 400);
    }

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $objetivoValor = isset($_POST['objetivo']) ? str_replace(',', '.', (string) $_POST['objetivo']) : '';
    if ($id <= 0 || $objetivoValor === '' || !is_numeric($objetivoValor) || (float) $objetivoValor < 0) {
        JsonResponse::enviar(array('error' => array('codigo' => 'DATOS_INVALIDOS')), 400);
    }

    $modeloObjetivo = new Objetivo();
    $rowEditado = $modeloObjetivo->obtenerPorId($id);
    if (!is_array($rowEditado)) {
        JsonResponse::enviar(array('error' => array('codigo' => 'OBJETIVO_NO_ENCONTRADO')), 404);
    }

    $claveEditada = null;
    foreach ($cardsObjetivo as $card) {
        if ($card['titulo'] === $rowEditado['nombre']) {
            $claveEditada = $card['clave'];
            break;
        }
    }
    if ($claveEditada === null) {
        JsonResponse::enviar(array('error' => array('codigo' => 'OBJETIVO_NO_COMPATIBLE')), 400);
    }

    $valores = array(
        'operaciones' => 0,
        'personas' => 0,
        'ventas' => 0,
        'ticketPromedio' => 0,
        'promedioPersona' => 0,
    );
    foreach ($cardsObjetivo as $index => $card) {
        $row = $modeloObjetivo->obtenerPorNombre($card['titulo']);
        if (!is_array($row)) {
            $row = $modeloObjetivo->obtenerOCrear($card['titulo'], 100 + $index, 0);
        }

        $objetivoActual = is_array($row) && isset($row['objetivo']) ? (float) $row['objetivo'] : 0;
        $logradoActual = is_array($row) && isset($row['logrado']) ? (float) $row['logrado'] : 0;
        $valores[$card['clave']] = $objetivoActual > 0 ? $objetivoActual : $logradoActual;
    }

    $nuevoObjetivo = (float) $objetivoValor;
    $operaciones = (float) $valores['operaciones'];
    $personas = (float) $valores['personas'];
    $ventas = (float) $valores['ventas'];

    if ($claveEditada === 'operaciones') {
        $operaciones = $nuevoObjetivo;
    } elseif ($claveEditada === 'personas') {
        $personas = $nuevoObjetivo;
    } elseif ($claveEditada === 'ventas') {
        $ventas = $nuevoObjetivo;
    } elseif ($claveEditada === 'ticketPromedio') {
        $ventas = $operaciones > 0 ? $operaciones * $nuevoObjetivo : $ventas;
    } elseif ($claveEditada === 'promedioPersona') {
        $ventas = $personas > 0 ? $personas * $nuevoObjetivo : $ventas;
    }

    $recalculados = array(
        'Ventas' => round($operaciones, 0),
        'Promedio de Venta' => $operaciones > 0 ? round($ventas / $operaciones, 2) : 0,
        'Personas' => round($personas, 0),
        'Promedio por persona' => $personas > 0 ? round($ventas / $personas, 2) : 0,
        'Monto total vendido' => round($ventas, 2),
    );

    if (!$modeloObjetivo->actualizarObjetivosPorNombre($recalculados)) {
        JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_GUARDADO')), 500);
    }

    JsonResponse::enviar(array(
        'data' => array(
            'objetivos' => $recalculados,
        ),
    ), 200);
}

$normalizarResumen = function ($row) {
    $ventas = isset($row['ventas']) ? (float) $row['ventas'] : 0;
    $operaciones = isset($row['operaciones']) ? (int) $row['operaciones'] : 0;
    $unidades = isset($row['unidades']) ? (float) $row['unidades'] : 0;
    $personas = $operaciones;

    return array(
        'ventas' => round($ventas, 2),
        'operaciones' => $operaciones,
        'personas' => $personas,
        'unidades' => round($unidades, 2),
        'ticketPromedio' => $operaciones > 0 ? round($ventas / $operaciones, 2) : 0,
        'promedioPersona' => $personas > 0 ? round($ventas / $personas, 2) : 0,
        'unidadesPromedio' => $operaciones > 0 ? round($unidades / $operaciones, 2) : 0,
    );
};

$periodosUltimosMeses = function ($cantidad) {
    $zona = new DateTimeZone('America/Argentina/Buenos_Aires');
    $actual = (new DateTimeImmutable('first day of this month', $zona))->setTime(0, 0);
    $periodos = array();

    for ($i = 0; $i < $cantidad; $i++) {
        $mes = $actual->modify('-' . $i . ' month');
        $periodos[] = array(
            'periodo' => $mes->format('Y-m'),
            'fecha' => $mes,
        );
    }

    return $periodos;
};

$formatearPeriodo = function ($periodo) {
    $meses = array(
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
    );
    $partes = explode('-', (string) $periodo);
    if (count($partes) !== 2 || !isset($meses[$partes[1]])) {
        return (string) $periodo;
    }

    return $meses[$partes[1]] . ' ' . $partes[0];
};

$diasUltimos30 = function ($cantidad) {
    $zona = new DateTimeZone('America/Argentina/Buenos_Aires');
    $hoy = (new DateTimeImmutable('today', $zona));
    $dias = array();

    for ($i = $cantidad - 1; $i >= 0; $i--) {
        $dias[] = $hoy->modify('-' . $i . ' day');
    }

    return $dias;
};

$formatearDia = function ($periodo) {
    $partes = explode('-', (string) $periodo);
    if (count($partes) !== 3) {
        return (string) $periodo;
    }

    return $partes[2] . '/' . $partes[1];
};

try {
    $modelo = new TicketPromedio();
    $modeloObjetivo = new Objetivo();
    $periodos = $periodosUltimosMeses(12);

    $localActual = $normalizarResumen($modelo->getResumenMesActual());
    $localHoy = $normalizarResumen($modelo->getResumenDiaActual());
    $periodoActual = $periodos[0]['periodo'];
    $operacionesTotal = $localActual['operaciones'];
    $personasTotal = $localActual['personas'];
    $ventasTotal = $localActual['ventas'];
    $operacionesTotalHoy = $localHoy['operaciones'];
    $personasTotalHoy = $localHoy['personas'];
    $ventasTotalHoy = $localHoy['ventas'];

    $evolucionLocal = array();
    foreach ($modelo->getEvolucionMensual(12) as $row) {
        $periodo = isset($row['periodo']) ? (string) $row['periodo'] : '';
        if ($periodo === '') {
            continue;
        }
        $evolucionLocal[$periodo] = $normalizarResumen($row);
    }

    $evolucion = array();
    foreach ($periodos as $periodo) {
        $key = $periodo['periodo'];
        $local = isset($evolucionLocal[$key])
            ? $evolucionLocal[$key]
            : array('ventas' => 0, 'operaciones' => 0, 'personas' => 0, 'unidades' => 0, 'ticketPromedio' => 0, 'promedioPersona' => 0, 'unidadesPromedio' => 0);
        $ventas = $local['ventas'];
        $operaciones = $local['operaciones'];
        $personas = $local['personas'];

        $evolucion[] = array(
            'periodo' => $key,
            'local' => $local,
            'consolidado' => array(
                'ventas' => round($ventas, 2),
                'operaciones' => $operaciones,
                'personas' => $personas,
                'ticketPromedio' => $operaciones > 0 ? round($ventas / $operaciones, 2) : 0,
                'promedioPersona' => $personas > 0 ? round($ventas / $personas, 2) : 0,
            ),
        );
    }

    $dias30 = $diasUltimos30(30);

    $evolucionDiariaLocal = array();
    foreach ($modelo->getEvolucionDiaria(30) as $row) {
        $periodo = isset($row['periodo']) ? (string) $row['periodo'] : '';
        if ($periodo === '') {
            continue;
        }
        $evolucionDiariaLocal[$periodo] = $normalizarResumen($row);
    }

    $evolucionDiaria = array();
    foreach ($dias30 as $dia) {
        $key = $dia->format('Y-m-d');
        $local = isset($evolucionDiariaLocal[$key])
            ? $evolucionDiariaLocal[$key]
            : array('ventas' => 0, 'operaciones' => 0, 'personas' => 0, 'unidades' => 0, 'ticketPromedio' => 0, 'promedioPersona' => 0, 'unidadesPromedio' => 0);
        $ventas = $local['ventas'];
        $operaciones = $local['operaciones'];
        $personas = $local['personas'];

        $evolucionDiaria[] = array(
            'periodo' => $key,
            'etiqueta' => $formatearDia($key),
            'local' => $local,
            'consolidado' => array(
                'ventas' => round($ventas, 2),
                'operaciones' => $operaciones,
                'personas' => $personas,
                'ticketPromedio' => $operaciones > 0 ? round($ventas / $operaciones, 2) : 0,
                'promedioPersona' => $personas > 0 ? round($ventas / $personas, 2) : 0,
            ),
        );
    }

    $porCanal = array_map(function ($row) {
        $ventas = isset($row['ventas']) ? (float) $row['ventas'] : 0;
        $operaciones = isset($row['operaciones']) ? (int) $row['operaciones'] : 0;

        return array(
            'canal' => isset($row['canal']) && $row['canal'] !== '' ? $row['canal'] : 'Sin canal',
            'ventas' => round($ventas, 2),
            'operaciones' => $operaciones,
            'ticketPromedio' => $operaciones > 0 ? round($ventas / $operaciones, 2) : 0,
        );
    }, $modelo->getMesActualPorCanal());

    $cards = $cardsObjetivo;

    $consolidadoActual = array(
        'ventas' => round($ventasTotal, 2),
        'operaciones' => $operacionesTotal,
        'personas' => $personasTotal,
        'ticketPromedio' => $operacionesTotal > 0 ? round($ventasTotal / $operacionesTotal, 2) : 0,
        'promedioPersona' => $personasTotal > 0 ? round($ventasTotal / $personasTotal, 2) : 0,
        'unidadesPromedio' => $localActual['unidadesPromedio'],
    );
    $consolidadoHoy = array(
        'ventas' => round($ventasTotalHoy, 2),
        'operaciones' => $operacionesTotalHoy,
        'personas' => $personasTotalHoy,
        'ticketPromedio' => $operacionesTotalHoy > 0 ? round($ventasTotalHoy / $operacionesTotalHoy, 2) : 0,
        'promedioPersona' => $personasTotalHoy > 0 ? round($ventasTotalHoy / $personasTotalHoy, 2) : 0,
        'unidadesPromedio' => $localHoy['unidadesPromedio'],
    );

    foreach ($cards as $index => $card) {
        $actual = isset($consolidadoActual[$card['clave']]) ? (float) $consolidadoActual[$card['clave']] : 0;
        $rowObjetivo = $modeloObjetivo->obtenerOCrear($card['titulo'], 100 + $index, $actual);
        $cards[$index]['objetivo'] = $normalizarObjetivo($rowObjetivo, $actual);
    }

    JsonResponse::enviar(array(
        'data' => array(
            'periodo' => $periodoActual,
            'periodoEtiqueta' => $formatearPeriodo($periodoActual),
            'resumen' => array(
                'local' => $localActual,
                'consolidado' => $consolidadoActual,
            ),
            'resumenHoy' => array(
                'local' => $localHoy,
                'consolidado' => $consolidadoHoy,
            ),
            'cards' => $cards,
            'cardsHoy' => $cards,
            'evolucion' => $evolucion,
            'evolucionDiaria' => $evolucionDiaria,
            'porCanal' => $porCanal,
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
