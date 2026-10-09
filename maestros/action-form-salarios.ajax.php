<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowSalarios"){
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este salarios?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveSalariosAddMore"){
    extract($_REQUEST);
    foreach($fecha as $key=>$un){
        $sqlCampos = 'fecha';
        $sqlValues = '"'.$fecha[$key].'"';

        $sqlCampos = $sqlCampos . ',monto';
        $sqlValues = $sqlValues . ',"'.$monto[$key].'"';

        $sqlInsert = 'INSERT INTO salario ('.$sqlCampos.') VALUES ('.$sqlValues.')';

        error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  

        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}
