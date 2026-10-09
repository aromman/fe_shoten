<?php

require_once dirname(__DIR__) . '/bff/gastos/control.php';

$action = isset($_REQUEST['action']) ? trim((string) $_REQUEST['action']) : '';

if ($action === 'addDataRowGastos') {
    gastos_bff_render_row();
    echo '|***|addmore';
    exit;
}

if ($action === 'savegastosAddMore') {
    $detalles = isset($_REQUEST['detalle']) && is_array($_REQUEST['detalle']) ? $_REQUEST['detalle'] : array();
    $fechas = isset($_REQUEST['fecha']) && is_array($_REQUEST['fecha']) ? $_REQUEST['fecha'] : array();
    $montos = isset($_REQUEST['monto']) && is_array($_REQUEST['monto']) ? $_REQUEST['monto'] : array();
    $clases = isset($_REQUEST['clase']) && is_array($_REQUEST['clase']) ? $_REQUEST['clase'] : array();
    $mediosPago = isset($_REQUEST['medioPago']) && is_array($_REQUEST['medioPago']) ? $_REQUEST['medioPago'] : array();
    $canales = isset($_REQUEST['canal']) && is_array($_REQUEST['canal']) ? $_REQUEST['canal'] : array();

    try {
        gastos_bff_guardar_lote($detalles, $fechas, $montos, $clases, $mediosPago, $canales);
        echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
        exit;
    } catch (\Throwable $error) {
        error_log($error->getMessage());
        echo '<div class="alert alert-danger"><i class="fa fa-fw fa-triangle-exclamation"></i> Error al grabar el gasto</div>|***|error';
        exit;
    }
}
