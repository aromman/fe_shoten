<?php

class CronExpressionHelper
{
    public static function cronDesdeDias($colValue)
    {
        $colValue = (int) $colValue;
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

    public static function normalizarCron($cron, $colValue = 0)
    {
        $cron = trim((string) $cron);
        if ($cron !== '') {
            return $cron;
        }

        return self::cronDesdeDias($colValue);
    }

    public static function proximaFecha($cron, DateTime $desde = null, $formato = 'd/m/Y')
    {
        $cron = trim((string) $cron);
        if ($cron === '') {
            return null;
        }

        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = $desde instanceof DateTime ? clone $desde : new DateTime();

        for ($i = 0; $i <= 370; $i++) {
            if (self::fechaCoincide($cron, $fecha)) {
                return array(
                    'fecha' => $fecha->format($formato),
                    'diasHasta' => $i,
                );
            }

            $fecha->modify('+1 day');
        }

        return null;
    }

    public static function describirCron($cron)
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
                return self::describirCampo($diaSemana, 'diaSemana') . ' cuando coincide con ' . self::describirCampo($diaMes, 'diaMes');
            }

            return self::describirCampo($diaSemana, 'diaSemana');
        }

        if ($diaMes !== '*' && $diaMes !== '?') {
            return self::describirCampo($diaMes, 'diaMes') . ($mes !== '*' ? ' del mes ' . $mes : ' de cada mes');
        }

        return 'todos los dias';
    }

    public static function fechaCoincide($cron, DateTime $fecha)
    {
        $partes = preg_split('/\s+/', trim($cron));
        if (count($partes) !== 5) {
            return false;
        }

        return self::campoCoincide($partes[2], (int) $fecha->format('j'), 1, 31)
            && self::campoCoincide($partes[3], (int) $fecha->format('n'), 1, 12)
            && self::diaSemanaCoincide($partes[4], $fecha);
    }

    private static function normalizarValor($valor)
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

    private static function campoCoincide($campo, $valor, $min, $max)
    {
        $campo = strtoupper(trim($campo));
        if ($campo === '*' || $campo === '?') {
            return true;
        }

        $partes = explode(',', $campo);
        foreach ($partes as $parte) {
            $parte = self::normalizarValor($parte);
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
                $inicio = (int) self::normalizarValor($inicio);
                $fin = (int) self::normalizarValor($fin);
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

    private static function diaSemanaCoincide($campo, DateTime $fecha)
    {
        $campo = strtoupper(trim($campo));
        if (strpos($campo, '#') === false) {
            return self::campoCoincide($campo, (int) $fecha->format('w'), 0, 6);
        }

        foreach (explode(',', $campo) as $parte) {
            if (strpos($parte, '#') === false) {
                continue;
            }

            list($dia, $ocurrencia) = explode('#', $parte, 2);
            $dia = (int) self::normalizarValor($dia);
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

    private static function describirCampo($campo, $tipo)
    {
        $campo = strtoupper(trim($campo));
        if ($campo === '*' || $campo === '?') {
            return 'todos los dias';
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
            $descripciones = array();

            foreach (explode(',', $campo) as $parte) {
                $parte = self::normalizarValor($parte);

                if (strpos($parte, '#') !== false) {
                    list($dia, $ocurrencia) = explode('#', $parte, 2);
                    $dia = self::normalizarValor($dia);
                    $ordinales = array(1 => 'primer', 2 => 'segundo', 3 => 'tercer', 4 => 'cuarto', 5 => 'quinto');
                    $descripciones[] = (isset($ordinales[(int) $ocurrencia]) ? $ordinales[(int) $ocurrencia] : $ocurrencia . 'o') . ' ' . (isset($nombresDias[$dia]) ? $nombresDias[$dia] : $dia) . ' de cada mes';
                    continue;
                }

                $descripciones[] = isset($nombresDias[$parte]) ? $nombresDias[$parte] : $parte;
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
}
