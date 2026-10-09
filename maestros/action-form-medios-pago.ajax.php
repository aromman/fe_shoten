<?php include_once('../config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowMediosDePago"){
    ?>
    <tr>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td><input type="number" step="0" name="porcentaje[]" class="form-control"></td>

        <td>
            <select name="cuenta[]" id="cuenta" class="form-control" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM cuentas");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
                
            </select>
        </td>
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
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este medio de pago?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveMediosDePagoAddMore"){
    extract($_REQUEST);
    foreach($nombre as $key=>$un){
        $sqlCampos = 'nombre';
        $sqlValues = '"'.$nombre[$key].'"';

        if (!empty($porcentaje[$key])){
            $sqlCampos = $sqlCampos . ',cargo_porcentaje';
            $sqlValues = $sqlValues . ', '.$porcentaje[$key];
        }

        $sqlCampos = $sqlCampos . ',id_cuenta';
        $sqlValues = $sqlValues . ', '.$cuenta[$key];

        $sqlCampos = $sqlCampos . ',id_canal';
        $sqlValues = $sqlValues . ', '.$canal[$key];

        $sqlInsert = 'INSERT INTO medio_pago ('.$sqlCampos.') VALUES ('.$sqlValues.')';
        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}