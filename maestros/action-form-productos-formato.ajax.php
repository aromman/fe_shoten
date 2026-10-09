<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProductosFormato"){
    ?>
    <tr>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este formato?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProductosFormatoAddMore"){
    extract($_REQUEST);
    foreach($nombre as $key=>$un){
        $sqlCampos = 'nombre, monto_objetivo';
        $sqlValues = '"'.$nombre[$key].'", '.$monto[$key];
        $sqlInsert = 'INSERT INTO productos_formato ('.$sqlCampos.') VALUES ('.$sqlValues.')';
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}