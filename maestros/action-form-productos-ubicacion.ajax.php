<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProductosUbicacion"){
    ?>
    <tr>
        <td>
            <select name="canal[]" id="canal" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM canal where activo=TRUE and vende=TRUE AND fechaInicio <= CURDATE()");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
                
            </select>
        </td>
        <td>
            <select name="tipoNivel01[]" id="tipoNivel01" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM productos_ubicacion_tipo");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
                
            </select>
        </td>
        <td><input type="number" step="0.1" name="codigoNivel01[]" class="form-control"></td>
        <td>
            <select name="tipoNivel02[]" id="tipoNivel02" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM productos_ubicacion_tipo");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
                
            </select>
        </td>
        <td><input type="number" step="0.1" name="codigoNivel02[]" class="form-control"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProductosUbicacionAddMore"){
    extract($_REQUEST);
    foreach($canal as $key=>$un){
        
        $sqlInsert = 'INSERT INTO productos_ubicacion (id_canal, id_nivel_tipo_01, nivel_codigo_01, id_nivel_tipo_02, nivel_codigo_02) 
                      VALUES ("'.$canal[$key].'", "'.$tipoNivel01[$key].'", "'.$codigoNivel01[$key].'", "'.$tipoNivel02[$key].'", "'.$codigoNivel02[$key].'")';

        
        error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}