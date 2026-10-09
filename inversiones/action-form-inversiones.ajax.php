<?php 

require_once "../app/models/inversores.php";
require_once "../app/models/inversiones.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowInversiones"){

    $inversoresPDO = new Inversores();

    ?>
    <tr>
        
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>">        </td>
        <td><input type="text" name="detalle[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="monto[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="cotiza[]" class="form-control" required="required"></td>
        <td></td>
        <td>
        <select name="pagado_por[]" id="pagadoPor" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
            <option value="">Seleccione</option>
			<?php
                $inversores = $inversoresPDO->getAll("nombre");
                foreach ($inversores as $inversor) {
			?>
				<option value="<?php echo $inversor['id']?>" data-subtext="(<?php echo $inversor['nombre']?>)"><?php echo mb_strtoupper($inversor['nombre'],'UTF-8')?></option>
			<?php }?>
        </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer insertar esta inversion?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveinversionesAddMore"){
    extract($_REQUEST);

    foreach($detalle as $key=>$un){
        
        $inversionesPDO = new Inversiones();
        $inversionesPDO->setFecha($fecha[$key]);
        $inversionesPDO->setDetalle($un);
        $inversionesPDO->setMonto($monto[$key]);
        $inversionesPDO->setLibros($monto[$key] / $cotiza[$key]);
        $inversionesPDO->setCotizacion($cotiza[$key]);
        $inversionesPDO->setInvertidoPor($pagado_por[$key]);
        $inversionesPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}