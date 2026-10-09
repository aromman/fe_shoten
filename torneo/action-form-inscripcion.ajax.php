<?php 
require_once "../app/models/canal.php";
require_once "../app/models/inscripcion.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowInscripciones"){
    ?>
    <tr>
        <td>
            <select name="canal[]" id="canal" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $canalPDO = new Canal();
                    $result = $canalPDO->getByType("TORNEO","nombre");
                    foreach ($result as $val){  
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td></td>
        <td></td>
        <td></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este pedido?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
        <td></td>              
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) && $_REQUEST['action']=="saveInscripcionesAddMore"){
    extract($_REQUEST);
    foreach($nombre as $key=>$un){
        $inscripcionPDO = new Inscripcion();
        $inscripcionPDO->idCanal = $canal[$key];
        $inscripcionPDO->nombre = $nombre[$key];
        $inscripcionPDO->idEstado = 1;
        $inscripcionPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}