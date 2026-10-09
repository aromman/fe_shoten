<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/config/url.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentas.php';
require_once dirname(__DIR__, 2) . '/app/models/cuentasMovimientos.php';
require_once dirname(__DIR__, 2) . '/app/models/ventasHeader.php';
require_once dirname(__DIR__, 2) . '/app/models/ordenCompra.php';
require_once dirname(__DIR__, 2) . '/app/models/gastos.php';
require_once dirname(__DIR__, 2) . '/app/models/medioPago.php';
require_once dirname(__DIR__, 2) . '/app/models/gastosClase.php';
require_once dirname(__DIR__, 2) . '/app/models/empleados.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../../login.php');
    exit;
}

function normalizarFecha($valor)
{
    $texto = trim((string) $valor);
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $texto, $m)) {
        return $m[1] . '-' . $m[2] . '-' . $m[3];
    }

    $timestamp = strtotime($texto);
    if ($timestamp === false) {
        return $texto;
    }

    return date('Y-m-d', $timestamp);
}

function normalizarMonto($valor)
{
    $texto = trim((string) $valor);
    $texto = str_replace(' ', '', $texto);
    if (strpos($texto, ',') !== false) {
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
    } else {
        $texto = str_replace(',', '', $texto);
    }

    return (float) $texto;
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

function normalizarAmbitoUsoCuenta($ambitoUsoPost, $idEmpleadoPost)
{
    $ambitoUso = trim((string) $ambitoUsoPost) !== '' ? trim((string) $ambitoUsoPost) : 'COMERCIAL';
    if (!in_array($ambitoUso, array('COMERCIAL', 'PERSONAL', 'EMPLEADO'), true)) {
        throw new \RuntimeException('AMBITO_USO_INVALIDO');
    }

    $idEmpleado = $idEmpleadoPost !== null && trim((string) $idEmpleadoPost) !== '' ? (int) $idEmpleadoPost : null;

    if ($ambitoUso === 'EMPLEADO') {
        if ($idEmpleado === null || $idEmpleado <= 0) {
            throw new \RuntimeException('EMPLEADO_REQUERIDO');
        }
    } else {
        $idEmpleado = null;
    }

    return array($ambitoUso, $idEmpleado);
}

function resolverNombreCuenta($ambitoUso, $idEmpleado, $nombrePost)
{
    if ($ambitoUso === 'EMPLEADO' && $idEmpleado !== null) {
        $empleadoPDO = new \Empleado();
        $empleado = $empleadoPDO->getById($idEmpleado);
        if (is_array($empleado) && isset($empleado['nombre']) && trim($empleado['nombre']) !== '') {
            return trim($empleado['nombre']);
        }
    }

    return trim((string) $nombrePost);
}

function normalizarForwardOkCuentas($valor)
{
    $forwardOkPermitidos = array('cuentas.php', 'cuentas-personales.php', 'cuentas-empleados.php');
    $forwardOk = trim((string) $valor);
    return in_array($forwardOk, $forwardOkPermitidos, true) ? $forwardOk : 'cuentas.php';
}

try {
    if ($action === 'create') {
        list($ambitoUso, $idEmpleado) = normalizarAmbitoUsoCuenta(
            isset($_POST['ambitoUso']) ? $_POST['ambitoUso'] : null,
            isset($_POST['idEmpleado']) ? $_POST['idEmpleado'] : null
        );

        $cuenta = new Cuentas();
        $cuenta->nombre = resolverNombreCuenta($ambitoUso, $idEmpleado, isset($_POST['nombre']) ? $_POST['nombre'] : '');
        $cuenta->tipo = $ambitoUso === 'EMPLEADO' ? 'A' : trim($_POST['tipo']);
        $cuenta->tipo_saldo = $ambitoUso === 'EMPLEADO' ? 'D' : trim($_POST['tipoSaldo']);
        $cuenta->ambito_uso = $ambitoUso;
        $cuenta->id_empleado = $idEmpleado;
        $cuenta->fecha_consolidado = trim($_POST['fecha']);
        $cuenta->saldo_consolidado = (float) $_POST['saldo'];
        $cuenta->saldo_inicial = (float) $_POST['saldo'];
        $cuenta->create();
        header('location: ../../cuentas/' . ($ambitoUso === 'EMPLEADO' ? 'cuentas-empleados.php' : 'cuentas.php'));
        exit;
    }

    if ($action === 'update') {
        list($ambitoUso, $idEmpleado) = normalizarAmbitoUsoCuenta(
            isset($_POST['ambitoUso']) ? $_POST['ambitoUso'] : null,
            isset($_POST['idEmpleado']) ? $_POST['idEmpleado'] : null
        );

        $cuenta = new Cuentas();
        $cuenta->id = (int) $_POST['id'];
        $cuenta->nombre = trim($_POST['nombre']);
        $cuenta->tipo = trim($_POST['tipo']);
        $cuenta->tipo_saldo = trim($_POST['tipoSaldo']);
        $cuenta->ambito_uso = $ambitoUso;
        $cuenta->id_empleado = $idEmpleado;
        $cuenta->saldo_inicial = (float) $_POST['saldoInicial'];
        $cuenta->update();
        header('location: ../../cuentas/cuentas.php');
        exit;
    }

    if ($action === 'bajaCuenta') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        $forwardOk = normalizarForwardOkCuentas(isset($_POST['forwardOk']) ? $_POST['forwardOk'] : '');

        $cuentasPDO = new Cuentas();
        $dependencias = $cuentasPDO->tieneDependencias($id);

        if (empty($dependencias)) {
            $cuentasPDO->delete($id);
        } else {
            $cuenta = new Cuentas();
            $cuenta->id = $id;
            $cuenta->activa = 0;
            $cuenta->update();
        }

        header('location: ../../cuentas/' . $forwardOk);
        exit;
    }

    if ($action === 'activarCuenta') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        $forwardOk = normalizarForwardOkCuentas(isset($_POST['forwardOk']) ? $_POST['forwardOk'] : '');

        $cuenta = new Cuentas();
        $cuenta->id = $id;
        $cuenta->activa = 1;
        $cuenta->update();

        header('location: ../../cuentas/' . $forwardOk);
        exit;
    }

    if ($action === 'updateSaldo') {
        $cuenta = new Cuentas();
        $cuenta->id = (int) $_POST['id'];
        $cuenta->saldo_inicial = (float) $_POST['saldoInicial'];
        $cuenta->update();
        header('location: ../../cuentas/view-cuenta.php?id=' . (int) $_POST['id']);
        exit;
    }

    if ($action === 'updateMovimiento') {
        $movimiento = new CuentasMovimientos();
        $movimiento->setId((int) $_POST['id']);
        $movimiento->setFecha(normalizarFecha($_POST['fecha']));
        $movimiento->setDescripcion(trim($_POST['descripcion']));
        $movimiento->setTipoMovimiento(trim($_POST['tipoMovimiento']));
        $movimiento->setMonto(normalizarMonto($_POST['monto']));
        $movimiento->setConsolidado(isset($_POST['consolidado']) ? $_POST['consolidado'] : 0);
        $movimiento->update();
        header('location: ../../cuentas/view-cuenta.php?id=' . (int) $_POST['idCuenta']);
        exit;
    }

    if ($action === 'createMovimiento') {
        $idCuenta = isset($_POST['idCuenta']) ? (int) $_POST['idCuenta'] : 0;
        $monto = isset($_POST['monto']) ? normalizarMonto($_POST['monto']) : 0;
        $tipoMovimiento = isset($_POST['tipoMovimiento']) ? strtoupper(trim($_POST['tipoMovimiento'])) : '';
        $fecha = isset($_POST['fecha']) ? normalizarFecha($_POST['fecha']) : date('Y-m-d');
        $idCanal = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0;

        if ($idCuenta <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        if (!in_array($tipoMovimiento, array('D', 'C'), true)) {
            throw new \RuntimeException('TIPO_MOVIMIENTO_INVALIDO');
        }

        if ($monto <= 0) {
            throw new \RuntimeException('MONTO_INVALIDO');
        }

        $movimiento = new CuentasMovimientos();
        $movimiento->setIdCuenta($idCuenta);
        $movimiento->setIdCanal($idCanal);
        $movimiento->setFecha($fecha);
        $movimiento->setDescripcion(trim($_POST['descripcion']));
        $movimiento->setTipoMovimiento($tipoMovimiento);
        $movimiento->setMonto($monto);
        $movimiento->setConsolidado(isset($_POST['consolidado']) ? $_POST['consolidado'] : 0);
        $movimiento->setIdCausal(isset($_POST['idCausal']) ? (int) $_POST['idCausal'] : 5);
        $movimiento->setOrigenTipo('manual');
        $movimiento->create();

        header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        exit;
    }

    if ($action === 'crearAjusteMovimiento') {
        $idCuenta = isset($_POST['idCuenta']) ? (int) $_POST['idCuenta'] : 0;
        if ($idCuenta <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        $fechaAjuste = isset($_POST['fechaAjuste']) ? normalizarFecha($_POST['fechaAjuste']) : (isset($_POST['fecha']) ? date('Y-m-d', strtotime(normalizarFecha($_POST['fecha']) . ' -1 day')) : date('Y-m-d'));
        $tipoMovimientoAjuste = strtoupper(trim((string) ($_POST['tipoMovimientoAjuste'] ?? '')));
        $montoAjuste = isset($_POST['montoAjuste']) ? (float) $_POST['montoAjuste'] : 0.0;
        $descripcionAjuste = isset($_POST['descripcionAjuste']) ? trim((string) $_POST['descripcionAjuste']) : 'Ajuste automatico por saldo negativo';
        $idCanal = isset($_POST['idCanal']) ? (int) $_POST['idCanal'] : (isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0);

        if (!in_array($tipoMovimientoAjuste, array('D', 'C'), true)) {
            throw new \RuntimeException('TIPO_MOVIMIENTO_INVALIDO');
        }

        if ($montoAjuste <= 0) {
            throw new \RuntimeException('MONTO_INVALIDO');
        }

        $movimientoAjuste = new CuentasMovimientos();
        $movimientoAjuste->setIdCuenta($idCuenta);
        $movimientoAjuste->setIdCanal($idCanal);
        $movimientoAjuste->setFecha($fechaAjuste);
        $movimientoAjuste->setDescripcion($descripcionAjuste);
        $movimientoAjuste->setTipoMovimiento($tipoMovimientoAjuste);
        $movimientoAjuste->setMonto($montoAjuste);
        $movimientoAjuste->setConsolidado(0);
        $movimientoAjuste->setIdCausal(3);
        $movimientoAjuste->setOrigenTipo('manual');
        $movimientoAjuste->create();

        header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        exit;
    }
    if ($action === 'crearAjusteReferenciaCuenta') {
        $idCuenta = isset($_POST['idCuenta']) ? (int) $_POST['idCuenta'] : 0;
        if ($idCuenta <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        $fechaOriginal = isset($_POST['fecha']) ? normalizarFecha($_POST['fecha']) : date('Y-m-d');
        $fechaAjuste = $fechaOriginal;
        $diferencia = isset($_POST['diferencia']) ? (float) $_POST['diferencia'] : 0.0;
        $descripcionAjuste = isset($_POST['descripcionAjuste']) ? trim((string) $_POST['descripcionAjuste']) : 'Ajuste automatico por referencia';
        $idCanal = isset($_POST['idCanal']) ? (int) $_POST['idCanal'] : (isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : 0);

        if (abs($diferencia) < 0.0001) {
            throw new \RuntimeException('DIFERENCIA_INVALIDA');
        }

        $montoAjuste = abs($diferencia);
        $tipoAjuste = $diferencia > 0 ? 'D' : 'C';

        $movimientoAjuste = new CuentasMovimientos();
        $movimientoAjuste->setIdCuenta($idCuenta);
        $movimientoAjuste->setIdCanal($idCanal);
        $movimientoAjuste->setFecha($fechaAjuste);
        $movimientoAjuste->setDescripcion($descripcionAjuste);
        $movimientoAjuste->setTipoMovimiento($tipoAjuste);
        $movimientoAjuste->setMonto($montoAjuste);
        $movimientoAjuste->setConsolidado(0);
        $movimientoAjuste->setIdCausal(3);
        $movimientoAjuste->setOrigenTipo('manual');
        $movimientoAjuste->create();

        header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        exit;
    }
    if ($action === 'transfer') {
        $idCuentaOrigen = isset($_POST['cuentaOrigen']) ? (int) $_POST['cuentaOrigen'] : 0;
        $idCuentaDestino = isset($_POST['cuentaDestino']) ? (int) $_POST['cuentaDestino'] : 0;
        $importe = isset($_POST['importe']) ? normalizarMonto($_POST['importe']) : 0;
        $fecha = isset($_POST['fecha']) ? normalizarFecha($_POST['fecha']) : date('Y-m-d');
        $idCausal = 3;
        $idCanal = isset($_SESSION['user.canal']) ? (int) $_SESSION['user.canal'] : (isset($_POST['canal']) ? (int) $_POST['canal'] : 0);

        if ($idCuentaOrigen <= 0 || $idCuentaDestino <= 0 || $idCuentaOrigen === $idCuentaDestino) {
            throw new \RuntimeException('CUENTAS_TRANSFERENCIA_INVALIDAS');
        }
        if ($importe <= 0) {
            throw new \RuntimeException('IMPORTE_INVALIDO');
        }

        $cuentasPDO = new Cuentas();
        $origen = $cuentasPDO->getById($idCuentaOrigen);
        $destino = $cuentasPDO->getById($idCuentaDestino);
        if (!is_array($origen) || empty($origen) || !is_array($destino) || empty($destino)) {
            throw new \RuntimeException('CUENTA_NO_ENCONTRADA');
        }

        $nombreCuentaOrigen = isset($origen['nombre']) ? $origen['nombre'] : '';
        $nombreCuentaDestino = isset($destino['nombre']) ? $destino['nombre'] : '';

        $cuentasPDO->pdo->beginTransaction();
        try {
            $movimientoOrigen = new CuentasMovimientos();
            $movimientoOrigen->pdo = $cuentasPDO->pdo;
            $movimientoOrigen->setIdCuenta($idCuentaOrigen);
            $movimientoOrigen->setFecha($fecha);
            $movimientoOrigen->setTipoMovimiento('D');
            $movimientoOrigen->setDescripcion('Transferencia a ' . $nombreCuentaDestino);
            $movimientoOrigen->setMonto($importe);
            $movimientoOrigen->setConsolidado('FALSE');
            $movimientoOrigen->setIdCausal($idCausal);
            $movimientoOrigen->setIdCanal($idCanal);
            $movimientoOrigen->setOrigenTipo('transferencia');
            $idMovimientoOrigen = $movimientoOrigen->create();

            $movimientoDestino = new CuentasMovimientos();
            $movimientoDestino->pdo = $cuentasPDO->pdo;
            $movimientoDestino->setIdCuenta($idCuentaDestino);
            $movimientoDestino->setFecha($fecha);
            $movimientoDestino->setTipoMovimiento('C');
            $movimientoDestino->setDescripcion('Transferencia de ' . $nombreCuentaOrigen);
            $movimientoDestino->setMonto($importe);
            $movimientoDestino->setConsolidado('FALSE');
            $movimientoDestino->setIdCausal($idCausal);
            $movimientoDestino->setIdCanal($idCanal);
            $movimientoDestino->setOrigenTipo('transferencia');
            $movimientoDestino->setOrigenId($idMovimientoOrigen);
            $idMovimientoDestino = $movimientoDestino->create();

            // vincular los movimientos entre si
            $movimientoOrigen->setId($idMovimientoOrigen);
            $movimientoOrigen->setOrigenId($idMovimientoDestino);
            $movimientoOrigen->update();

            $cuentasPDO->pdo->commit();
        } catch (\Throwable $e) {
            if ($cuentasPDO->pdo->inTransaction()) {
                $cuentasPDO->pdo->rollBack();
            }
            throw $e;
        }

        header('location: ../../cuentas/cuentas.php');
        exit;
    }

    if ($action === 'crearNuevaVinculacion') {
        $idMovimiento = (int) $_POST['idMovimiento'];
        $tipoOrigen = trim($_POST['tipoOrigen']);
        $proveedorId = isset($_POST['proveedorId']) ? (int) $_POST['proveedorId'] : 0;
        $claseId = isset($_POST['claseId']) ? (int) $_POST['claseId'] : 0;
        $cuentaDestinoId = isset($_POST['cuentaDestinoId']) ? (int) $_POST['cuentaDestinoId'] : 0;

        $movimientoPDO = new CuentasMovimientos();
        $movimientoBase = $movimientoPDO->getById($idMovimiento);
        if (!is_array($movimientoBase) || empty($movimientoBase)) {
            throw new \RuntimeException('MOVIMIENTO_NO_ENCONTRADO');
        }

        if ($tipoOrigen === 'transferencias' && $cuentaDestinoId <= 0) {
            throw new \RuntimeException('CUENTA_DESTINO_INVALIDA');
        }
        if ($tipoOrigen === 'compras' && $proveedorId <= 0) {
            throw new \RuntimeException('PROVEEDOR_INVALIDO');
        }
        if ($tipoOrigen === 'gastos' && $claseId <= 0) {
            throw new \RuntimeException('CLASE_INVALIDA');
        }

        $cuentaActualId = (int) ($movimientoBase['id_cuenta'] ?? 0);
        $cuentaActualNombre = '';
        if ($cuentaActualId > 0) {
            $cuentasPDO = new Cuentas();
            $cuentaActual = $cuentasPDO->getById($cuentaActualId);
            if (is_array($cuentaActual) && isset($cuentaActual['nombre'])) {
                $cuentaActualNombre = $cuentaActual['nombre'];
            }
        }
        $montoMovimiento = isset($movimientoBase['monto']) ? (float) $movimientoBase['monto'] : 0;
        $fechaMovimiento = isset($movimientoBase['fecha']) ? $movimientoBase['fecha'] : date('Y-m-d H:i:s');
        $idCanalMovimiento = (int) ($movimientoBase['id_canal'] ?? 0);
        $medioPagoId = 0;
        $medioPagoPDO = new MedioPago();
        $medioPagoRow = $medioPagoPDO->getByCuenta($cuentaActualId);
        if (is_array($medioPagoRow) && isset($medioPagoRow['id'])) {
            $medioPagoId = (int) $medioPagoRow['id'];
        }
        if ($medioPagoId <= 0) {
            $medioPagoId = 1;
        }

        if ($tipoOrigen === 'transferencias') {
            $movimientoAsociado = new CuentasMovimientos();
            $movimientoAsociado->setFecha($fechaMovimiento);
            $movimientoAsociado->setMonto($montoMovimiento);
            $movimientoAsociado->setConsolidado(isset($movimientoBase['consolidado']) ? $movimientoBase['consolidado'] : 'FALSE');
            $movimientoAsociado->setIdCausal((int) ($movimientoBase['id_causal'] ?? 0));
            $movimientoAsociado->setIdCanal($idCanalMovimiento);
            $movimientoAsociado->setIdCuenta($cuentaDestinoId);
            $movimientoAsociado->setTipoMovimiento($movimientoBase['tipo_movimiento'] === 'D' ? 'C' : 'D');
            $movimientoAsociado->setDescripcion('Transferencia desde ' . $cuentaActualNombre);
            $movimientoAsociado->setOrigenTipo('transferencia');
            $movimientoAsociado->setOrigenId($idMovimiento);
            $movimientoAsociado->setLogContext('transferencia_contraparte');
            $idMovimientoAsociado = $movimientoAsociado->create();
            // vinculo 
            $movimientoPDO->setId($idMovimiento);
            $movimientoPDO->setOrigenTipo('transferencia');
            $movimientoPDO->setOrigenId($idMovimientoAsociado);
            $movimientoPDO->update();

        } elseif ($tipoOrigen === 'ventas') {
            $ventasHeader = new VentasHeader();
            $ventasHeader->idCanal = $idCanalMovimiento > 0 ? $idCanalMovimiento : (int) ($_SESSION['user.canal'] ?? 0);
            $ventasHeader->fecha = $fechaMovimiento;
            $ventasHeader->idCliente = 1;
            $ventasHeader->idMedioPago = $medioPagoId;
            $ventasHeader->cantidad = 1;
            $ventasHeader->subTotal = $montoMovimiento;
            $ventasHeader->descuento = 0;
            $ventasHeader->total = $montoMovimiento;
            $ventasHeader->updateDate = date('Y-m-d H:i:s');
            $ventasHeader->userName = isset($_SESSION['user.username']) ? $_SESSION['user.username'] : '';
            $idVentaHeader = $ventasHeader->create();
            
            $movimientoPDO->setId($idMovimiento);
            $movimientoPDO->setOrigenTipo('venta');
            $movimientoPDO->setOrigenId($idVentaHeader);
            $movimientoPDO->update();

        } elseif ($tipoOrigen === 'compras') {
            $ordenCompra = new OrdenCompra();
            $ordenCompra->fecha = $fechaMovimiento;
            $ordenCompra->idProveedor = $proveedorId;
            $ordenCompra->cantidad = 1;
            $ordenCompra->monto = $montoMovimiento;
            $ordenCompra->fechaEntrega = $fechaMovimiento;
            $ordenCompra->idEstadoPedido = 1;
            $ordenCompra->idMedioPago = $medioPagoId;
            $idOrdenCompra = $ordenCompra->create();
            
            $movimientoPDO->setId($idMovimiento);
            $movimientoPDO->setOrigenTipo('compra');
            $movimientoPDO->setOrigenId($idOrdenCompra);
            $movimientoPDO->update();
        } elseif ($tipoOrigen === 'gastos') {
            $gasto = new Gastos();
            $gasto->fecha = isset($movimientoBase['fecha']) ? substr((string) $movimientoBase['fecha'], 0, 10) : date('Y-m-d');
            $gasto->detalle = isset($movimientoBase['descripcion']) && trim((string) $movimientoBase['descripcion']) !== '' ? trim((string) $movimientoBase['descripcion']) : 'Gasto vinculado';
            $gasto->monto = $montoMovimiento;
            $gasto->tipo = 'F';
            $gasto->idClase = $claseId;
            $gasto->idMedioPago = $medioPagoId;
            $gasto->idCanal = $idCanalMovimiento;
            $idGasto = $gasto->create();

            $movimientoPDO->setId($idMovimiento);
            $movimientoPDO->setOrigenTipo('gasto');
            $movimientoPDO->setOrigenId($idGasto);
            $movimientoPDO->update();

            header('location: ../../cuentas/conciliar-movimiento.php?id=' . $idMovimiento);
            exit;
        }

        header('location: ../../cuentas/conciliar-movimiento.php?id=' . $idMovimiento);
        exit;
    }

    if ($action === 'unlinkMovimiento') {
        $idMovimiento = (int) $_POST['idMovimiento'];
        $movimientoPDO = new CuentasMovimientos();
        $movimiento = $movimientoPDO->getById($idMovimiento);
        if (!is_array($movimiento) || empty($movimiento)) {
            throw new \RuntimeException('MOVIMIENTO_NO_ENCONTRADO');
        }
        // limpio contraparte si es transferencia
        if (($movimiento['origen_tipo'] ?? '') === 'transferencia' && !empty($movimiento['origen_id'])) {
            $movimientoPDO->setId((int) $movimiento['origen_id']);
            $movimientoPDO->setIdCausal(3);
            $movimientoPDO->clearOrigenTipo();
            $movimientoPDO->clearOrigenId();
            $movimientoPDO->update();
        }
        // limpio el movimiento actual
        $movimientoPDO->setId($idMovimiento);
        $movimientoPDO->setIdCausal(3);
        $movimientoPDO->clearOrigenTipo();
        $movimientoPDO->clearOrigenId();
        $movimientoPDO->update();

        header('location: ../../cuentas/conciliar-movimiento.php?id=' . $idMovimiento);
        exit;
    }

    if ($action === 'vincularOrigenExistente') {
        $idMovimiento = (int) $_POST['idMovimiento'];
        $tipoOrigen = trim($_POST['tipoOrigen']);
        $origenId = isset($_POST['origenId']) ? (int) $_POST['origenId'] : 0;

        $movimientoPDO = new CuentasMovimientos();
        $movimientoBase = $movimientoPDO->getById($idMovimiento);
        if (!is_array($movimientoBase) || empty($movimientoBase)) {
            throw new \RuntimeException('MOVIMIENTO_NO_ENCONTRADO');
        }

        if ($origenId <= 0 || $tipoOrigen === '') {
            throw new \RuntimeException('ORIGEN_INVALIDO');
        }

        $idCausal = 0;
        if ($tipoOrigen === 'ventas') {
            $idCausal = 1;
        } elseif ($tipoOrigen === 'compras') {
            $idCausal = 2;
        } elseif ($tipoOrigen === 'transferencias') {
            $idCausal = 3;
        } elseif ($tipoOrigen === 'gastos') {
            $idCausal = 4;
        }

        $movimientoPDO->setId($idMovimiento);
        $movimientoPDO->setOrigenTipo(rtrim($tipoOrigen, 's'));
        $movimientoPDO->setOrigenId($origenId);
        if ($idCausal > 0) {
            $movimientoPDO->setIdCausal($idCausal);
        }
        $movimientoPDO->update();

        if ($tipoOrigen === 'transferencias') {
            $movimientoPDO->setId($origenId);
            $movimientoPDO->setOrigenTipo('transferencia');
            $movimientoPDO->setOrigenId($idMovimiento);
            $movimientoPDO->setIdCausal(3);
            $movimientoPDO->update();
        }

        header('location: ../../cuentas/conciliar-movimiento.php?id=' . $idMovimiento);
        exit;
    }

    if ($action === 'deleteMovimiento') {
        $idMovimiento = isset($_POST['idMovimiento']) ? (int) $_POST['idMovimiento'] : 0;
        if ($idMovimiento <= 0) {
            throw new \RuntimeException('MOVIMIENTO_INVALIDO');
        }

        $movimiento = new CuentasMovimientos();
        $movimientoBase = $movimiento->getById($idMovimiento);
        if (!is_array($movimientoBase) || empty($movimientoBase)) {
            throw new \RuntimeException('MOVIMIENTO_NO_ENCONTRADO');
        }

        $esConsolidado = false;
        if (isset($movimientoBase['consolidado'])) {
            $valorConsolidado = strtoupper(trim((string) $movimientoBase['consolidado']));
            $esConsolidado = in_array($valorConsolidado, array('1', 'TRUE', 'SI', 'S', 'YES', 'Y'), true);
        }

        if ($esConsolidado) {
            throw new \RuntimeException('MOVIMIENTO_CONSOLIDADO_NO_ELIMINABLE');
        }

        $idCuenta = isset($movimientoBase['id_cuenta']) ? (int) $movimientoBase['id_cuenta'] : 0;
        $movimiento->delete($idMovimiento);

        if ($idCuenta > 0) {
            header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        } else {
            header('location: ../../cuentas/cuentas.php');
        }
        exit;
    }
    if ($action === 'consolidarTodoCuenta') {
        $idCuenta = isset($_POST['idCuenta']) ? (int) $_POST['idCuenta'] : 0;
        if ($idCuenta <= 0) {
            throw new \RuntimeException('CUENTA_INVALIDA');
        }

        $movimientoPDO = new CuentasMovimientos();
        $movimientoPDO->consolidarHastaFecha($idCuenta);

        header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        exit;
    }

    if ($action === 'conciliarMovimiento') {
        $idMovimiento = isset($_POST['idMovimiento']) ? (int) $_POST['idMovimiento'] : 0;
        if ($idMovimiento <= 0) {
            throw new \RuntimeException('MOVIMIENTO_INVALIDO');
        }

        $movimiento = new CuentasMovimientos();
        $movimientoBase = $movimiento->getById($idMovimiento);
        $movimiento->setId($idMovimiento);
        $movimiento->setConsolidado(1);
        $movimiento->update();

        $idCuenta = is_array($movimientoBase) && isset($movimientoBase['id_cuenta']) ? (int) $movimientoBase['id_cuenta'] : 0;
        if ($idCuenta > 0) {
            header('location: ../../cuentas/view-cuenta.php?id=' . $idCuenta);
        } else {
            header('location: ../../cuentas/conciliar-movimiento.php?id=' . $idMovimiento);
        }
        exit;
    }

    header('location: ../../cuentas/cuentas.php');
    exit;
} catch (\Throwable $error) {
    error_log($error->getMessage());
    header('location: ../../errors.php');
    exit;
}
