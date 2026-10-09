<?php 

include_once('config.php');
require_once "app/models/medioPago.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowCostos"){
    ?>
    <tr>
        
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>">        </td>
        <td><input type="text" name="detalle[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td>
            <select name="medioPago[]" id="medioPago" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $medioPagoPDO = new MedioPago();
                    $mediosPago = $medioPagoPDO->getAll("nombre ASC");
                    foreach ($mediosPago as $val) {
                        ?>
                            <option 
                                value="<?php echo $val['id']?>" 
                                data-subtext="(<?php echo $val['id']?>)"
                            >
                                <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                            </option>
                        <?php 
                            }
                            unset($medioPagoPDO);
                        ?>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este costo?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveCostosAddMore"){
       
    extract($_REQUEST);
     
    foreach($detalle as $key=>$un){
        $sqlInsert = 'INSERT INTO gastos (fecha, detalle, monto, tipo, id_medio_pago) VALUES ("'.$fecha[$key].'", "'.$un.'", '.$monto[$key].', "V",'.$medioPago[$key].') ';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

        $medioPagoPDO = new MedioPago();
        $mediosPago = $medioPagoPDO->getById($medioPago[$key]);
        $idCuenta = $mediosPago['id_cuenta'];
        $idCausal = 4 ; // pagos
        // inserto movimiento
        $sqlInsert = 'INSERT INTO cuentas_movimientos (id_cuenta, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal) VALUES ('.$idCuenta.',"'.$fecha[$key].'","D", "'.$un.'", '.$monto[$key].', "FALSE", '.$idCausal.') ';
        //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
        $db->query($sqlInsert);

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';


}