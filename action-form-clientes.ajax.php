<?php include_once('config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowClientes"){
    ?>
    <tr>
        
        <td><input type="text" name="apellido[]" class="form-control" required="required"></td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td><input type="text" name="dni[]" class="form-control"></td>
        <td><input type="text" name="email[]" class="form-control"></td>

        <td>
            <select name="descuento[]" id="descuento" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM descuentos");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>

        
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este cliente?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveClientesAddMore"){
    extract($_REQUEST);
     
    foreach($apellido as $key=>$un){
        $fields = "apellido, nombre";
        $values = '"'.$un.'", "'.$nombre[$key].'"';
        if (!empty($dni[$key])){
            $fields = $fields . ", dni";
            $values = $values. ", " .$dni[$key];
        }
        if (!empty($email[$key])){
            $fields = $fields . ", email";
            $values = $values . ', "' . $email[$key]. '"';
        }

        if (!empty($descuento[$key])){
            $fields = $fields . ", id_descuento";
            $values = $values . ', ' . $descuento[$key];
        }

        $sqlInsert = 'INSERT INTO clientes ('.$fields.') VALUES ('.$values.')';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}