<?php 
include_once('config.php');

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowOrdenes"){
    ?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td>
            <select name="proveedor[]" id="proveedor" class="form-control selectpicker" data-live-search="true" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM proveedores order by nombre");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td colspan="4"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este orden?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveOrdenesAddMore"){
    extract($_REQUEST);
    foreach($fecha as $key=>$un){
        $sqlInsert = 'INSERT INTO orden_compra (fecha, id_proveedor, id_estado_pedido) VALUES ("'.$fecha[$key].'",'.$proveedor[$key].', 1)';
        // //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");
        $db->query($sqlInsert); 
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}