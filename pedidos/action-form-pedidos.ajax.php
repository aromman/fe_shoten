<?php 

date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once "../app/models/pedidos.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowPedidos"){
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td><input type="text" name="cliente[]" class="form-control" required="required"></td>
        <td><input type="text" name="contacto[]" class="form-control" required="required"></td>
        <td><input type="text" name="producto[]" class="form-control" required="required"></td>
        <td>
            <select name="estado[]" id="estado" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <option value="1" data-subtext="(1)">Pendiente</option>
                <option value="3" data-subtext="(3)">Disponible</option>
            </select>
        </td> 
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>              
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este pedido?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
        <td></td>              
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="savePedidosAddMore"){
    extract($_REQUEST);

    $newPedidosPDO = new Pedidos();

    foreach($fecha as $key=>$un){

        $newPedidosPDO->fecha = $fecha[$key];
        $newPedidosPDO->cliente = $cliente[$key];
        $newPedidosPDO->contacto = $contacto[$key];
        $newPedidosPDO->producto = $producto[$key];
        $newPedidosPDO->idEstado = $estado[$key];
        $newPedidosPDO->fechaActualizacion = $fecha[$key];
        $newPedidosPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}