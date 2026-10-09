<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/helpers/CronExpressionHelper.php';
require_once dirname(__DIR__, 2) . '/app/models/proveedores.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

function evaluarDiaTexto($colValue)
{
    $retVal = '';
    if ($colValue >= 64) {
        $retVal = 'SA';
        $colValue -= 64;
    }
    if ($colValue >= 32) {
        $retVal = 'VI ' . $retVal;
        $colValue -= 32;
    }
    if ($colValue >= 16) {
        $retVal = 'JU ' . $retVal;
        $colValue -= 16;
    }
    if ($colValue >= 8) {
        $retVal = 'MI ' . $retVal;
        $colValue -= 8;
    }
    if ($colValue >= 4) {
        $retVal = 'MA ' . $retVal;
        $colValue -= 4;
    }
    if ($colValue >= 2) {
        $retVal = 'LU ' . $retVal;
        $colValue -= 2;
    }
    if ($colValue >= 1) {
        $retVal = 'DO ' . $retVal;
        $colValue -= 1;
    }

    return trim($retVal);
}

function cronDesdeDias($colValue)
{
    if ($colValue <= 0) {
        return '';
    }

    $arrayDiaPotencia = array(1, 2, 4, 8, 16, 32, 64);
    $dias = array();
    foreach ($arrayDiaPotencia as $diaCron => $diaPotencia) {
        if (($colValue & $diaPotencia) === $diaPotencia) {
            $dias[] = $diaCron;
        }
    }

    return empty($dias) ? '' : '0 0 * * ' . implode(',', $dias);
}

function normalizarCron($cron, $colValue)
{
    $cron = trim((string) $cron);
    if ($cron !== '') {
        return $cron;
    }

    return cronDesdeDias($colValue);
}

function describirCampoCron($campo, $tipo)
{
    $campo = strtoupper(trim($campo));
    if ($campo === '*' || $campo === '?') {
        return $tipo === 'diaMes' ? 'todos los dias' : 'todos los dias';
    }

    $nombresDias = array(
        '0' => 'domingo',
        '1' => 'lunes',
        '2' => 'martes',
        '3' => 'miercoles',
        '4' => 'jueves',
        '5' => 'viernes',
        '6' => 'sabado',
        '7' => 'domingo',
    );

    if ($tipo === 'diaSemana') {
        $partes = explode(',', $campo);
        $descripciones = array();

        foreach ($partes as $parte) {
            $parte = normalizarValorCron($parte);

            if (strpos($parte, '#') !== false) {
                list($dia, $ocurrencia) = explode('#', $parte, 2);
                $dia = normalizarValorCron($dia);
                $ordinales = array(
                    1 => 'primer',
                    2 => 'segundo',
                    3 => 'tercer',
                    4 => 'cuarto',
                    5 => 'quinto',
                );
                $descripciones[] = (isset($ordinales[(int) $ocurrencia]) ? $ordinales[(int) $ocurrencia] : $ocurrencia . 'o') . ' ' . (isset($nombresDias[$dia]) ? $nombresDias[$dia] : $dia) . ' de cada mes';
                continue;
            }

            if (isset($nombresDias[$parte])) {
                $descripciones[] = $nombresDias[$parte];
            } else {
                $descripciones[] = $parte;
            }
        }

        if (count($descripciones) === 1) {
            return $descripciones[0];
        }

        $ultimo = array_pop($descripciones);
        return implode(', ', $descripciones) . ' y ' . $ultimo;
    }

    if (strpos($campo, '*/') === 0) {
        return 'cada ' . substr($campo, 2) . ' dias';
    }

    return 'dia ' . strtolower($campo);
}

function describirCron($cron)
{
    $cron = trim((string) $cron);
    if ($cron === '') {
        return '';
    }

    $partes = preg_split('/\s+/', $cron);
    if (count($partes) !== 5) {
        return $cron;
    }

    $diaMes = $partes[2];
    $mes = $partes[3];
    $diaSemana = $partes[4];

    if ($diaSemana !== '*' && $diaSemana !== '?') {
        if ($diaMes !== '*' && $diaMes !== '?') {
            return describirCampoCron($diaSemana, 'diaSemana') . ' cuando coincide con ' . describirCampoCron($diaMes, 'diaMes');
        }

        return describirCampoCron($diaSemana, 'diaSemana');
    }

    if ($diaMes !== '*' && $diaMes !== '?') {
        return describirCampoCron($diaMes, 'diaMes') . ($mes !== '*' ? ' del mes ' . $mes : ' de cada mes');
    }

    return 'todos los dias';
}

function normalizarValorCron($valor)
{
    $valor = strtoupper(trim($valor));
    $mapa = array(
        'SUN' => '0', 'DOM' => '0',
        'MON' => '1', 'LUN' => '1',
        'TUE' => '2', 'MAR' => '2',
        'WED' => '3', 'MIE' => '3', 'MIERCOLES' => '3',
        'THU' => '4', 'JUE' => '4',
        'FRI' => '5', 'VIE' => '5',
        'SAT' => '6', 'SAB' => '6',
    );

    return isset($mapa[$valor]) ? $mapa[$valor] : $valor;
}

function cronCampoCoincide($campo, $valor, $min, $max)
{
    $campo = strtoupper(trim($campo));
    if ($campo === '*' || $campo === '?') {
        return true;
    }

    $partes = explode(',', $campo);
    foreach ($partes as $parte) {
        $parte = normalizarValorCron($parte);
        $paso = 1;

        if (strpos($parte, '/') !== false) {
            list($parte, $pasoValor) = explode('/', $parte, 2);
            $paso = max(1, (int) $pasoValor);
        }

        if ($parte === '*' || $parte === '?') {
            $inicio = $min;
            $fin = $max;
        } elseif (strpos($parte, '-') !== false) {
            list($inicio, $fin) = explode('-', $parte, 2);
            $inicio = (int) normalizarValorCron($inicio);
            $fin = (int) normalizarValorCron($fin);
        } else {
            $inicio = (int) $parte;
            $fin = (int) $parte;
        }

        if ($max === 6 && $inicio === 7) {
            $inicio = 0;
        }
        if ($max === 6 && $fin === 7) {
            $fin = 0;
        }

        if ($inicio <= $fin) {
            if ($valor >= $inicio && $valor <= $fin && (($valor - $inicio) % $paso) === 0) {
                return true;
            }
        } elseif (($valor >= $inicio && $valor <= $max) || ($valor >= $min && $valor <= $fin)) {
            return true;
        }
    }

    return false;
}

function cronDiaSemanaCoincide($campo, DateTime $fecha)
{
    $campo = strtoupper(trim($campo));
    if (strpos($campo, '#') === false) {
        return cronCampoCoincide($campo, (int) $fecha->format('w'), 0, 6);
    }

    foreach (explode(',', $campo) as $parte) {
        if (strpos($parte, '#') === false) {
            continue;
        }

        list($dia, $ocurrencia) = explode('#', $parte, 2);
        $dia = (int) normalizarValorCron($dia);
        $ocurrencia = (int) $ocurrencia;
        $diaActual = (int) $fecha->format('w');
        $ocurrenciaActual = (int) ceil(((int) $fecha->format('j')) / 7);

        if ($dia === 7) {
            $dia = 0;
        }

        if ($diaActual === $dia && $ocurrenciaActual === $ocurrencia) {
            return true;
        }
    }

    return false;
}

function cronFechaCoincide($cron, DateTime $fecha)
{
    $partes = preg_split('/\s+/', trim($cron));
    if (count($partes) !== 5) {
        return false;
    }

    return cronCampoCoincide($partes[2], (int) $fecha->format('j'), 1, 31)
        && cronCampoCoincide($partes[3], (int) $fecha->format('n'), 1, 12)
        && cronDiaSemanaCoincide($partes[4], $fecha);
}

function evaluarProximaFechaCron($cron)
{
    $cron = trim((string) $cron);
    if ($cron === '') {
        return null;
    }

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $fecha = new DateTime();

    for ($i = 0; $i <= 370; $i++) {
        if (cronFechaCoincide($cron, $fecha)) {
            return array(
                'fecha' => $fecha->format('d/m/Y'),
                'diasHasta' => $i,
            );
        }

        $fecha->modify('+1 day');
    }

    return null;
}

try {
    $proveedoresPDO = new Proveedor();
    $verInactivos = isset($_GET['verInactivos']) && $_GET['verInactivos'] === '1';
    $result = $verInactivos ? $proveedoresPDO->getAllParaCalendario('nombre') : $proveedoresPDO->getAllActiveParaCalendario();

    if (!is_array($result)) {
        $result = array();
    }

    $items = array_map(function ($row) {
        $cronPedido = CronExpressionHelper::normalizarCron(isset($row['cron_pedido']) ? $row['cron_pedido'] : '');
        $cronEntrega = CronExpressionHelper::normalizarCron(isset($row['cron_entrega']) ? $row['cron_entrega'] : '');
        $proximoPedido = CronExpressionHelper::proximaFecha($cronPedido);
        $proximaEntrega = CronExpressionHelper::proximaFecha($cronEntrega);

        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'origen' => isset($row['origen']) ? $row['origen'] : 'local',
            'origenId' => isset($row['origen_id']) ? $row['origen_id'] : '',
            'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
            'diaPedido' => 0,
            'cronPedido' => $cronPedido,
            'diaPedidoEtiqueta' => $cronPedido !== '' ? CronExpressionHelper::describirCron($cronPedido) : '',
            'fechaProximoPedido' => is_array($proximoPedido) ? $proximoPedido['fecha'] : '',
            'diasHastaProximoPedido' => is_array($proximoPedido) ? $proximoPedido['diasHasta'] : null,
            'diaEntrega' => 0,
            'cronEntrega' => $cronEntrega,
            'diaEntregaEtiqueta' => $cronEntrega !== '' ? CronExpressionHelper::describirCron($cronEntrega) : '',
            'fechaProximaEntrega' => is_array($proximaEntrega) ? $proximaEntrega['fecha'] : '',
            'diasHastaProximaEntrega' => is_array($proximaEntrega) ? $proximaEntrega['diasHasta'] : null,
            'cantidadMinima' => isset($row['cantidad_minima']) ? (float) $row['cantidad_minima'] : 0,
            'montoMinimo' => isset($row['monto_minimo']) ? (float) $row['monto_minimo'] : 0,
            'activo' => isset($row['activo']) && (bool) $row['activo'],
        );
    }, $result);

    JsonResponse::enviar(array(
        'data' => array(
            'proveedores' => $items,
        ),
        'meta' => array(
            'total' => count($items),
            'version' => 'v1',
        ),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
