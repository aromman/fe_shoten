<?php 

include_once('../config.php');
require_once "../app/models/editoriales.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowProductoStock"){
        $idProducto =  trim($_REQUEST["producto"]);
    ?>
    <tr>

        <td>
            <select name="editorial[]" id="editorial" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $editorialPDO = new Editorial();
                    $editoriales = $editorialPDO->getAll('nombre');
                    if (!is_null($editoriales)){
                        foreach ($editoriales as $val) {
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php 
                        }
                    }
                ?>
            </select>
        </td>
        <td><input type="number" step="0.1" name="cantidad[]" class="form-control" required="required"></td>
        <td>
            <select name="ubicacion[]" id="ubicacion" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query('select 
                                            pu.id id,
                                            concat(c.nombre, " - ", put1.nombre, " ", pu.nivel_codigo_01, " - ", put2.nombre, " ", pu.nivel_codigo_02 )  nombre
                                            from productos_ubicacion pu, 
                                            canal c,
                                            productos_ubicacion_tipo put1,
                                            productos_ubicacion_tipo put2
                                            where pu.id_canal = c.id
                                            and pu.id_nivel_tipo_01 = put1.id
                                            and pu.id_nivel_tipo_02 = put2.id');
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
                
            </select>
        </td>
        <td><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>" class="form-control" required="required"></td>
        
        <td><input type="number" step=".01" name="costo[]" class="form-control" required="required"></td>
        <td>
            <select name="esNuevo[]" id="esNuevo" class="form-control" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <option value="1" data-subtext="(TRUE)" <?php if(1==$esNuevo) echo 'selected="selected"'; ?>>Si</option>
                <option value="0" data-subtext="(FALSE)" <?php if(0==$esNuevo) echo 'selected="selected"'; ?>>No</option>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveProductoStockAddMore"){
    extract($_REQUEST);

    $idProducto =  trim($_REQUEST["producto"]);

    foreach($editorial as $key=>$un){
        
        $sqlInsert = 'INSERT INTO productos_stock (id_producto, id_editorial, cantidad, id_ubicacion, fecha, costo, nuevo) 
                      VALUES ('.$idProducto.','.$editorial[$key].', '.$cantidad[$key].', '.$ubicacion[$key].', "'.$fecha[$key].'", '.$costo[$key].','.$esNuevo[$key].')';
        error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}