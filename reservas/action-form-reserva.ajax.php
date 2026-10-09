<?php 
include_once('../config.php');

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowReservas"){
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td align="center"><input type="time" name="hora[]" value="<?php echo date("h-M");?>"></td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td><input type="text" name="clave[]" class="form-control" required="required"></td>
        <td><input type="number" step="0" name="horas[]" class="form-control"></td>
        <td><input type="number" step="0" name="mesas[]" class="form-control"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este pedido?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) && $_REQUEST['action']=="saveReservasAddMore"){
    extract($_REQUEST);
    foreach($fecha as $key=>$un){
        $sqlInsert = 'INSERT INTO reservas (fecha, hora, cliente, clave, horas, mesas, id_estado) values (
        "'.$fecha[$key].'",
        "'.$hora[$key].'",
        "'.$nombre[$key].'",
        "'.$clave[$key].'",
        '.$horas[$key].',
        '.$mesas[$key].',
        1)'; // se registra en estado pendiente
        error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");
        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}