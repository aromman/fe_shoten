<?php include_once('config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProductos"){
    ?>
    <tr>
        <td><input disabled type="text" name="id[]" class="form-control"></td>
        <td><input type="text" name="sku[]" class="form-control" required="required"></td>
        <td><input type="text" name="titulo[]" class="form-control" required="required"></td>
        <td><input type="number" step="0" name="stock[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="precio[]" class="form-control" required="required"></td>
        <td>
            <select name="editorial[]" id="editorial" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM editoriales");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>

        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este producto?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProductosAddMore"){
    extract($_REQUEST);

    foreach($sku as $key=>$un){
        
        $sqlInsert = 'INSERT INTO productos (sku, titulo, id_editorial, stock, precio) VALUES ("'.$sku[$key].'", "'.$titulo[$key]. '" ,'.$editorial[$key]. ','.$stock[$key].','.$precio[$key].')';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

 
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}