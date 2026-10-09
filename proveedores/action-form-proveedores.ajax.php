<?php 

require_once "../app/models/proveedores.php";

function calcularDia($nombreCampo, $offset)
{
    return !empty($_REQUEST[$nombreCampo]) ? $offset : 0;
}

function calcularDias($prefijo)
{
    $total = 0;
    $total += calcularDia($prefijo . 'Domingo', 1);
    $total += calcularDia($prefijo . 'Lunes', 2);
    $total += calcularDia($prefijo . 'Martes', 4);
    $total += calcularDia($prefijo . 'Miercoles', 8);
    $total += calcularDia($prefijo . 'Jueves', 16);
    $total += calcularDia($prefijo . 'Viernes', 32);
    $total += calcularDia($prefijo . 'Sabado', 64);

    return $total;
}

function obtenerCron($nombreCampo)
{
    return isset($_REQUEST[$nombreCampo]) ? trim($_REQUEST[$nombreCampo]) : '';
}

function cronValido($cron)
{
    return $cron === '' || count(preg_split('/\s+/', $cron)) === 5;
}

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProveedores"){
    ?>
    <tr>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td></td>
        <td></td>
        <td><input type="number" step="0" name="cantidadMinima[]" class="form-control"></td>
        <td><input type="number" step=".01" name="montoMinimo[]" class="form-control"></td>
        <td></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar esta Proveedor?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}

if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProveedorPopup"){
    $cronPedido = obtenerCron('cronPedido');
    $cronEntrega = obtenerCron('cronEntrega');
    if (!cronValido($cronPedido) || !cronValido($cronEntrega)) {
        echo '<div class="alert alert-danger"><i class="fa fa-fw fa-warning"></i> La expresion cron debe tener 5 campos.</div>|***|error';
        exit;
    }

    $proveedoresPDO = new Proveedor();
    $proveedoresPDO->nombre = trim($_REQUEST['nombre']);
    $proveedoresPDO->cronPedido = $cronPedido;
    $proveedoresPDO->cronEntrega = $cronEntrega;
    $proveedoresPDO->cantidadMinima = isset($_REQUEST['cantidadMinima']) && $_REQUEST['cantidadMinima'] !== '' ? $_REQUEST['cantidadMinima'] : 0;
    $proveedoresPDO->montoMinimo = isset($_REQUEST['montoMinimo']) && $_REQUEST['montoMinimo'] !== '' ? $_REQUEST['montoMinimo'] : 0;
    $proveedoresPDO->activo = isset($_REQUEST['activo']) ? $_REQUEST['activo'] : 1;
    $proveedoresPDO->create();

    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Proveedor creado correctamente.</div>|***|add';
}

if(isset($_REQUEST['action']) and $_REQUEST['action']=="updateProveedorPopup"){
    $cronPedido = obtenerCron('cronPedido');
    $cronEntrega = obtenerCron('cronEntrega');
    if (!cronValido($cronPedido) || !cronValido($cronEntrega)) {
        echo '<div class="alert alert-danger"><i class="fa fa-fw fa-warning"></i> La expresion cron debe tener 5 campos.</div>|***|error';
        exit;
    }

    $proveedoresPDO = new Proveedor();
    $proveedoresPDO->id = $_REQUEST['id'];
    if (!isset($_REQUEST['origen']) || $_REQUEST['origen'] === 'local') {
        $proveedoresPDO->nombre = trim($_REQUEST['nombre']);
    }
    $proveedoresPDO->cronPedido = $cronPedido;
    $proveedoresPDO->cronEntrega = $cronEntrega;
    $proveedoresPDO->cantidadMinima = isset($_REQUEST['cantidadMinima']) && $_REQUEST['cantidadMinima'] !== '' ? $_REQUEST['cantidadMinima'] : 0;
    $proveedoresPDO->montoMinimo = isset($_REQUEST['montoMinimo']) && $_REQUEST['montoMinimo'] !== '' ? $_REQUEST['montoMinimo'] : 0;
    $proveedoresPDO->activo = isset($_REQUEST['activo']) ? $_REQUEST['activo'] : 1;
    $proveedoresPDO->update();

    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Proveedor actualizado correctamente.</div>|***|update';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProveedoresAddMore"){
    extract($_REQUEST);

    $proveedoresPDO = new Proveedor();

    foreach($nombre as $key=>$un){

        $proveedoresPDO->nombre = $nombre[$key];
        $proveedoresPDO->cronPedido = '';
        $proveedoresPDO->cronEntrega = '';
        $proveedoresPDO->cantidadMinima = $cantidadMinima[$key];
        $proveedoresPDO->montoMinimo = $montoMinimo[$key];
        $proveedoresPDO->activo = TRUE;
        $proveedoresPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}
