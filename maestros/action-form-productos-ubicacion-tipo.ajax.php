<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProductosUbicacionTipo"){
    ?>
    <tr>
        <td><input type="text" name="codigo[]" class="form-control" required="required"></td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProductosUbicacionTipoAddMore"){
    extract($_REQUEST);
    foreach($nombre as $key=>$un){
        
        $sqlInsert = 'INSERT INTO productos_ubicacion_tipo (codigo, nombre) VALUES ("'.$codigo[$key].'", "'.$nombre[$key].'")';
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}