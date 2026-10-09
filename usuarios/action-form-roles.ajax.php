<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowRoles"){
    ?>
    <tr>
        
        <td></td>
        <td><input type="text" name="rol[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este rol?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveRolesAddMore"){
    extract($_REQUEST);
    
    foreach($rol as $key=>$un){
        
        $sqlCampos = 'rol';
        $sqlValues = '"'.$rol[$key].'"';
        
        $sqlInsert = 'INSERT INTO roles ('.$sqlCampos.') VALUES ('.$sqlValues.')';
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}