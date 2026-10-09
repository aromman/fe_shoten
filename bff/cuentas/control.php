<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';

use App\Http\JsonResponse;

$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
if ($method !== 'GET') {
    header('Allow: GET');
    JsonResponse::enviar(array('error' => array('codigo' => 'METODO_NO_PERMITIDO')), 405);
}

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

try {
    $ambitoUsoPermitidos = array('COMERCIAL', 'PERSONAL', 'EMPLEADO');
    $ambitoUsoFiltro = null;
    if (isset($_GET['ambitoUso']) && trim($_GET['ambitoUso']) !== '') {
        $valoresFiltro = array_map('trim', explode(',', strtoupper($_GET['ambitoUso'])));
        $valoresFiltro = array_values(array_intersect($valoresFiltro, $ambitoUsoPermitidos));
        if (!empty($valoresFiltro)) {
            $ambitoUsoFiltro = $valoresFiltro;
        }
    }

    $incluirInactivas = isset($_GET['incluirInactivas']) && $_GET['incluirInactivas'] === '1';

    $cuentasPDO = new Cuentas();
    $result = $cuentasPDO->getAllWithBalance('nombre ASC', $ambitoUsoFiltro, $incluirInactivas);

    if (!is_array($result)) {
        $result = array();
    }

    $tipoCuenta = array('A' => 'Activo', 'P' => 'Pasivo');
    $tipoSaldo = array('E' => 'Efectivo', 'B' => 'Bancario', 'D' => 'Deuda', 'P' => 'Pozo');
    $ambitoUso = array('COMERCIAL' => 'Comercial', 'PERSONAL' => 'Personal', 'EMPLEADO' => 'Empleado');

    $items = array_map(function ($row) use ($tipoCuenta, $tipoSaldo, $ambitoUso) {
        $saldoInicial = isset($row['saldo_inicial']) ? (float) $row['saldo_inicial'] : 0;
        $creditos = isset($row['creditos']) ? (float) $row['creditos'] : 0;
        $debitos = isset($row['debitos']) ? (float) $row['debitos'] : 0;
        $saldo = $saldoInicial + $creditos - $debitos;
        $tipo = isset($row['tipo']) ? $row['tipo'] : '';
        $tipoSaldoValue = isset($row['tipo_saldo']) ? $row['tipo_saldo'] : '';
        $ambitoUsoValue = isset($row['ambito_uso']) ? $row['ambito_uso'] : '';

        return array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'nombre' => isset($row['nombre']) ? $row['nombre'] : '',
            'tipo' => $tipo,
            'tipoEtiqueta' => isset($tipoCuenta[$tipo]) ? $tipoCuenta[$tipo] : $tipo,
            'tipoSaldo' => $tipoSaldoValue,
            'tipoSaldoEtiqueta' => isset($tipoSaldo[$tipoSaldoValue]) ? $tipoSaldo[$tipoSaldoValue] : $tipoSaldoValue,
            'ambitoUso' => $ambitoUsoValue,
            'ambitoUsoEtiqueta' => isset($ambitoUso[$ambitoUsoValue]) ? $ambitoUso[$ambitoUsoValue] : $ambitoUsoValue,
            'idEmpleado' => isset($row['id_empleado']) && $row['id_empleado'] !== null ? (int) $row['id_empleado'] : null,
            'empleadoNombre' => isset($row['empleado_nombre']) ? $row['empleado_nombre'] : '',
            'activa' => !isset($row['activa']) || (int) $row['activa'] === 1,
            'saldoInicial' => $saldoInicial,
            'saldoInicialEtiqueta' => number_format($saldoInicial, 2, '.', ''),
            'creditos' => $creditos,
            'creditosEtiqueta' => number_format($creditos, 2, '.', ''),
            'debitos' => $debitos,
            'debitosEtiqueta' => number_format($debitos, 2, '.', ''),
            'saldo' => $saldo,
            'saldoEtiqueta' => number_format($saldo, 2, '.', ''),
            'puedeInvertir' => $tipo === 'P' && $saldo < 0,
        );
    }, $result);

    JsonResponse::enviar(array(
        'data' => array(
            'cuentas' => $items,
            'tipos' => $tipoCuenta,
            'tiposSaldo' => $tipoSaldo,
            'ambitosUso' => $ambitoUso,
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
