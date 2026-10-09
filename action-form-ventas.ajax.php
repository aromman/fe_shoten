<?php 

require_once "app/models/medioPago.php";
require_once "app/models/canal.php";
require_once "app/models/clientes.php";
require_once "app/models/medioPago.php";
require_once "app/models/producto.php";
require_once "app/models/equipo.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowVentas"){
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td>
            <select name="canal[]" id="canal" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $canalPDO = new Canal();
                    $canales = $canalPDO->getAll("nombre ASC");
                    foreach ($canales as $val) {
                ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td>
            <select name="producto[]" id="producto" class="form-control selectpicker" data-live-search="true" required="required">
                <option value="">Seleccione</option>
                <?php
                    $productoPDO = new Producto();
                    $productos = $productoPDO->getAll("titulo ASC");
                    foreach ($productos as $val) {
                ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['titulo'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td><input type="number" step="0" name="unidades[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="precioUnitario[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="descuento[]" class="form-control"></td>
        <td><input type="text" name="motivoDescuento[]" class="form-control"></td>
        <td><input type="number" step=".01" name="total[]" class="form-control" required="required"></td>
        <td>
            <select name="medioPago[]" id="medioPago" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
            <option value="">Seleccione</option>
                <?php
                    $medioPagoPDO = new MedioPago();
                    $mediosPago = $medioPagoPDO->getAll("nombre ASC");
                    foreach ($mediosPago as $val) {
                        ?>
                            <option 
                                value="<?php echo $val['id']?>" 
                                data-subtext="(<?php echo $val['id']?>)"
                            >
                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                            </option>
                        <?php 
                            }
                            unset($medioPagoPDO);
                        ?>
            </select>
        </td>
        <td>
            <select name="cliente[]" id="cliente" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $clientePDO = new Cliente();
                    $clientes = $clientePDO->getAll("apellido ASC");
                    foreach ($clientes as $val) {
                ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['apellido'].' '.$val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td>
            <select name="equipo[]" id="equipo" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $equipoPDO = new Equipo();
                    $equipos = $equipoPDO->getAll("equipo ASC");
                    foreach ($equipos as $val) {
                ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['equipo'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar esta venta?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveVentasAddMore"){
    extract($_REQUEST);
    
    foreach($fecha as $key=>$un){
        $ventaPDO = new Ventas();
        $ventaPDO->fecha = $fecha[$key];
        $ventaPDO->total = $total[$key];
        $ventaPDO->idCanal = $canal[$key];
        $ventaPDO->idProducto = $producto[$key];
        $ventaPDO->unidades = $unidades[$key];
        $ventaPDO->idEquipo = $equipo[$key];
        $ventaPDO->idMedioPago = $medioPago[$key];
        $ventaPDO->descuento = $descuento[$key];
        $ventaPDO->motivoDescuento = $motivoDescuento[$key];
        $ventaPDO->idCliente = $cliente[$key];
        $ventaPDO->precioUnitario = $precioUnitario[$key];
        $ventaPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}