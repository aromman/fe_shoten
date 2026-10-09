<?php include_once('config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowComisiones"){
    ?>
    <tr>
        
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>">        </td>
        <td><input type="text" name="detalle[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td>
        <select name="empleado[]" id="empleado" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
            <option value="">Seleccione</option>
			<?php
				$result	=	$db->query("select id, concat(apellido, ' ', nombre) nombre from empleados order by apellido, nombre");
				while($val  =   $result->fetch_assoc()){
				?>
				<option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['nombre']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
			<?php }?>
        </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar esta comision?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveComisionesAddMore"){
    extract($_REQUEST);
     
    foreach($detalle as $key=>$un){
        
        // importe en comision pasar a negativo
        $importe = $monto[$key] > 0 ? $monto[$key] * -1 : $monto[$key];

        // resto comision
         
        $sqlInsert = 'INSERT INTO comisiones (fecha, detalle, monto, id_empleado, id_liquidacion) VALUES ("'.$fecha[$key].'", "'.$un.'", '.$importe.', "'.$empleado[$key].'", null) ';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

        // registro gasto
        $importe = $importe < 0 ? $importe * -1 : $importe;

        $sqlInsert = 'INSERT INTO gastos (fecha, detalle, monto, tipo) VALUES ("'.$fecha[$key].'", "'.$un.'", '.$importe.', "C") ';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}