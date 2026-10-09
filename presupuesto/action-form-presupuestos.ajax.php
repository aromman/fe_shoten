<?php 

include_once('../config.php');
require_once "../app/models/gastosClase.php";
require_once "../app/models/canal.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowPresupuestos"){
    ?>
    <tr>
        
        <td>
            <select name="canal[]" id="canal" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $canalPDO = new Canal();
                    $canales = $canalPDO->getAllActive("nombre ASC");
                    foreach ($canales as $val) {
                ?>
                    <option 
                        value="<?php echo $val['id']?>" 
                        data-subtext="(<?php echo $val['id']?>)"
                        <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                    >
                        <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                    </option>
                <?php 
                    }
                    unset($canalPDO);
                ?>

            </select>
        </td>
        
        
        <td>
            <select name="clase[]" id="clase" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $gastosClasePDO = new GastosClase();
                    $gastosClase = $gastosClasePDO->getAll("nombre ASC");
                    foreach ($gastosClase as $val) {
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>        
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este presupuesto?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="savepresupuestosAddMore"){
    extract($_REQUEST);
     
    foreach($canal as $key=>$un){
        $sqlInsert = 'INSERT INTO presupuesto (id_canal, id_clase_gasto, monto) VALUES ('.$canal[$key].','.$clase[$key].','.$monto[$key].') ';
        error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}