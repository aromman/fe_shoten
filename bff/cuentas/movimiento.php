<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/Http/JsonResponse.php';
require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';
require_once dirname(__DIR__, 2) . '/app/models/canal.php';
require_once dirname(__DIR__, 2) . '/app/models/compras.php';
require_once dirname(__DIR__, 2) . '/app/models/ordenCompra.php';
require_once dirname(__DIR__, 2) . '/app/models/ventasHeader.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';
require_once dirname(__DIR__, 2) . '/app/models/turnos.php';
require_once dirname(__DIR__, 2) . '/app/models/turnoParcial.php';

use App\Http\JsonResponse;

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    JsonResponse::enviar(array('error' => array('codigo' => 'NO_AUTENTICADO')), 401);
}

if (!isset($_GET['id']) || trim($_GET['id']) === '') {
    JsonResponse::enviar(array('error' => array('codigo' => 'PARAMETRO_INVALIDO')), 422);
}

try {
    $id = (int) $_GET['id'];
    $cuentaPDO = new Cuentas();
    $movimientoPDO = new CuentasMovimientos();
    $canalPDO = new Canal();

    $movimiento = $movimientoPDO->getById($id);
    if (!is_array($movimiento) || empty($movimiento)) {
        JsonResponse::enviar(array('error' => array('codigo' => 'NO_ENCONTRADO')), 404);
    }

    $cuenta = array();
    if (isset($movimiento['id_cuenta'])) {
        $cuenta = $cuentaPDO->getById((int) $movimiento['id_cuenta']);
        if (!is_array($cuenta)) {
            $cuenta = array();
        }
    }

    $canales = $canalPDO->getAllActive('nombre ASC');
    if (!is_array($canales)) {
        $canales = array();
    }

    $causalNombre = '';
    if (isset($movimiento['id_causal'])) {
        $causalId = (int) $movimiento['id_causal'];
        $causales = array(
            1 => 'VENTAS',
            2 => 'COMPRAS',
            3 => 'TRANSFERENCIAS',
            4 => 'PAGOS',
        );
        if (isset($causales[$causalId])) {
            $causalNombre = $causales[$causalId];
        }
    }

    $tipoMovimiento = isset($movimiento['tipo_movimiento']) ? strtoupper(trim($movimiento['tipo_movimiento'])) : '';
    $tipoMovimientoEtiqueta = $tipoMovimiento === 'D' ? 'Debito' : ($tipoMovimiento === 'C' ? 'Credito' : $tipoMovimiento);
    $fechaMovimiento = isset($movimiento['fecha']) ? substr($movimiento['fecha'], 0, 10) : null;
    $fechaDesde = $fechaMovimiento ? date('Y-m-d', strtotime($fechaMovimiento . ' -15 days')) : null;
    $fechaHasta = $fechaMovimiento ? date('Y-m-d', strtotime($fechaMovimiento . ' +15 days')) : null;
    $fechaHastaExclusiva = $fechaHasta ? date('Y-m-d', strtotime($fechaHasta . ' +1 day')) : null;
    $idCuenta = isset($movimiento['id_cuenta']) ? (int) $movimiento['id_cuenta'] : 0;
    $esEfectivo = isset($cuenta['tipo_saldo']) && $cuenta['tipo_saldo'] === 'E';
    $origenTipo = isset($movimiento['origen_tipo']) ? trim((string) $movimiento['origen_tipo']) : '';
    $origenId = isset($movimiento['origen_id']) ? (int) $movimiento['origen_id'] : 0;

    $formatear = function ($rows, $campos) {
        $items = array();
        if (!is_array($rows)) {
            return $items;
        }

        foreach ($rows as $row) {
            $item = array(
                'id' => isset($row['id']) ? (int) $row['id'] : 0,
                'fecha' => isset($row[$campos['fecha']]) ? substr((string) $row[$campos['fecha']], 0, 10) : '',
                'monto' => isset($row[$campos['monto']]) ? (float) $row[$campos['monto']] : 0,
                'descripcion' => isset($row[$campos['descripcion']]) ? $row[$campos['descripcion']] : '',
                'tipo' => isset($row[$campos['tipo']]) ? $row[$campos['tipo']] : '',
            );

            if (isset($row['saldo'])) {
                $item['saldo'] = (float) $row['saldo'];
            }
            if (isset($row['diferencia'])) {
                $item['diferencia'] = (float) $row['diferencia'];
            }

            $items[] = $item;
        }

        return $items;
    };

    $calcularSaldoCuentaHastaFecha = function ($idCuentaSaldo, $fechaSaldo) use ($movimientoPDO) {
        return $movimientoPDO->getSaldoHastaFecha($idCuentaSaldo, $fechaSaldo);
    };

    $ventas = array();
    $compras = array();
    $gastos = array();
    $transferencias = array();
    $arqueos = array();
    $cierres = array();
    $movimientoAsociado = array();

    $tieneVinculo = ($origenTipo !== '' && $origenId > 0);

    if ($fechaDesde && $fechaHasta && !$tieneVinculo) {
        if ($tipoMovimiento === 'C') {
            $ventasHeaderPDO = new VentasHeader();
            $ventasHeaderPDO->pdo = $movimientoPDO->pdo;
            $ventas = $ventasHeaderPDO->getCandidatasConciliacion($fechaDesde . ' 00:00:00', $fechaHastaExclusiva . ' 00:00:00', $idCuenta);

            $transferencias = $movimientoPDO->getTransferenciasCandidatasConciliacion($fechaDesde . ' 00:00:00', $fechaHastaExclusiva . ' 00:00:00', $idCuenta, $tipoMovimiento);
        }


        if ($tipoMovimiento === 'D') {
            $ordenCompraPDO = new OrdenCompra();
            $ordenCompraPDO->pdo = $movimientoPDO->pdo;
            $compras = $ordenCompraPDO->getCandidatasConciliacion($fechaDesde . ' 00:00:00', $fechaHastaExclusiva . ' 00:00:00', $idCuenta);

            $gastosPDO = new Gastos();
            $gastosPDO->pdo = $movimientoPDO->pdo;
            $gastos = $gastosPDO->getCandidatasConciliacion($fechaDesde . ' 00:00:00', $fechaHastaExclusiva . ' 00:00:00', $idCuenta);

            $transferencias = $movimientoPDO->getTransferenciasCandidatasConciliacion($fechaDesde . ' 00:00:00', $fechaHastaExclusiva . ' 00:00:00', $idCuenta, $tipoMovimiento);
        }

    }

    if ($fechaMovimiento && $esEfectivo && !$tieneVinculo) {
        $canalId = isset($movimiento['id_canal']) ? (int) $movimiento['id_canal'] : 0;
        if ($canalId <= 0 && isset($_SESSION['user.canal'])) {
            $canalId = (int) $_SESSION['user.canal'];
        }

        $turnosPDO = new Turnos();
        $turnosPDO->pdo = $movimientoPDO->pdo;

        $turnoParcialPDO = new TurnoParcial();
        $turnoParcialPDO->pdo = $movimientoPDO->pdo;
        $arqueos = $turnoParcialPDO->getArqueoConciliacion($fechaMovimiento, $canalId);
        $cierres = $turnosPDO->getCierresConciliacion($fechaMovimiento, $canalId);

        if (!empty($arqueos)) {
            foreach ($arqueos as &$arqueo) {
                $saldoCalculado = $calcularSaldoCuentaHastaFecha($idCuenta, isset($arqueo['fecha']) ? substr((string) $arqueo['fecha'], 0, 10) : '');
                $montoArqueo = isset($arqueo['monto']) ? (float) $arqueo['monto'] : 0.0;
                $arqueo['saldo'] = $saldoCalculado;
                $arqueo['diferencia'] = $saldoCalculado - $montoArqueo;
            }
            unset($arqueo);
        }

        if (!empty($cierres)) {
            foreach ($cierres as &$cierre) {
                $saldoCalculado = $calcularSaldoCuentaHastaFecha($idCuenta, isset($cierre['fecha']) ? substr((string) $cierre['fecha'], 0, 10) : '');
                $montoCierre = isset($cierre['monto']) ? (float) $cierre['monto'] : 0.0;
                $cierre['saldo'] = $saldoCalculado;
                $cierre['diferencia'] = $saldoCalculado - $montoCierre;
            }
            unset($cierre);
        }
    }

    $detalleOrigen = null;

    if ($tieneVinculo) {
        if ($origenTipo === 'venta') {
                $ventasHeaderPDO = new VentasHeader();
                $ventasHeaderPDO->pdo = $movimientoPDO->pdo;
                $detalleOrigen = $ventasHeaderPDO->getConciliacionDetalleById($origenId);
        } elseif ($origenTipo === 'compra') {
                $ordenCompraPDO = new OrdenCompra();
                $ordenCompraPDO->pdo = $movimientoPDO->pdo;
                $detalleOrigen = $ordenCompraPDO->getConciliacionDetalleById($origenId);
            } elseif ($origenTipo === 'gasto') {
                $gastosPDO = new Gastos();
                $gastosPDO->pdo = $movimientoPDO->pdo;
                $detalleOrigen = $gastosPDO->getConciliacionDetalleById($origenId);
        } elseif ($origenTipo === 'transferencia') {
                if ($origenId > 0) {
                    $detalleOrigen = $movimientoPDO->getConciliacionDetalleById($origenId);
                }

                if (empty($detalleOrigen)) {
                    $detalleOrigen = $movimientoPDO->getTransferenciaConciliacionFallback(
                        $idCuenta,
                        isset($movimiento['fecha']) ? substr((string) $movimiento['fecha'], 0, 10) : '',
                        isset($movimiento['monto']) ? (float) $movimiento['monto'] : 0
                    );
                }
        }
    }

    $ventas = $formatear($ventas, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));
    $compras = $formatear($compras, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));
    $gastos = $formatear($gastos, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));
    $transferencias = $formatear($transferencias, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));
    $arqueos = $formatear($arqueos, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));
    $cierres = $formatear($cierres, array('fecha' => 'fecha', 'monto' => 'monto', 'descripcion' => 'descripcion', 'tipo' => 'tipo'));

    JsonResponse::enviar(array(
        'data' => array(
            'movimiento' => $movimiento,
            'cuenta' => $cuenta,
            'cuentaNombre' => isset($cuenta['nombre']) ? $cuenta['nombre'] : '',
            'tipoMovimientoEtiqueta' => $tipoMovimientoEtiqueta,
            'causalNombre' => $causalNombre,
            'descripcion' => isset($movimiento['descripcion']) ? $movimiento['descripcion'] : '',
            'idCausal' => isset($movimiento['id_causal']) ? (int) $movimiento['id_causal'] : 0,
            'rango' => array(
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
            ),
            'grillas' => array(
                'ventas' => $ventas,
                'compras' => $compras,
                'gastos' => $gastos,
                'transferencias' => $transferencias,
                'arqueos' => $arqueos,
                'cierres' => $cierres,
            ),
            'movimientoAsociado' => !empty($detalleOrigen) ? array(array(
                'id' => isset($detalleOrigen['id']) ? (int) $detalleOrigen['id'] : 0,
                'fecha' => isset($detalleOrigen['fecha']) ? substr((string) $detalleOrigen['fecha'], 0, 10) : '',
                'monto' => isset($detalleOrigen['monto']) ? (float) $detalleOrigen['monto'] : 0,
                'descripcion' => isset($detalleOrigen['descripcion']) ? $detalleOrigen['descripcion'] : '',
                'tipo' => isset($detalleOrigen['tipo']) ? $detalleOrigen['tipo'] : '',
                'extra' => isset($detalleOrigen['extra']) ? $detalleOrigen['extra'] : '',
                'origen_tipo' => isset($detalleOrigen['origen_tipo']) ? $detalleOrigen['origen_tipo'] : '',
                'origen_id' => isset($detalleOrigen['origen_id']) ? (int) $detalleOrigen['origen_id'] : 0,
            )) : array(),
            'movimientoAsociadoTipo' => !empty($detalleOrigen) ? (isset($detalleOrigen['origen_tipo']) ? $detalleOrigen['origen_tipo'] : (isset($detalleOrigen['tipo']) ? $detalleOrigen['tipo'] : '')) : '',
            'canales' => $canales,
        ),
        'meta' => array('version' => 'v1'),
    ), 200);
} catch (\Throwable $error) {
    error_log($error->getMessage());
    JsonResponse::enviar(array('error' => array('codigo' => 'ERROR_INTERNO')), 500);
}
