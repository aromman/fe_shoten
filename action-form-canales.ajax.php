<?php 

require_once "app/models/canal.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowCanales"){
    ?>
    <tr>
        
        <td>
            <select name="tipo[]" id="tipo" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <option value="FISICO" data-subtext="(Fisico)">FISICO</option>
                <option value="VIRTUAL" data-subtext="(Virtual)">VIRTUAL</option>
                <option value="EVENTO" data-subtext="(Evento)">EVENTO</option>
                <option value="TORNEO" data-subtext="(Torneo)">TORNEO</option>
                <option value="CAFETERIA" data-subtext="(Cafeteria)">CAFETERIA</option>
            </select>
        </td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td align="center"><input type="date" name="fechaInicio[]" value="<?php echo date("Y-m-d");?>"></td>
        <td align="center"><input type="date" name="fechaFin[]" value="<?php echo date("Y-m-d");?>"></td>
        <td><input disabled type="text" name="estado[]" class="form-control" required="required"></td>
        <td></td>
        <td>
            <select name="vende[]" id="vende" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <option value="TRUE">SI</option>
                <option value="FALSE">NO</option>
            </select>
        </td>
        <td><input type="number" step=".01" name="puntoVenta[]" class="form-control"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar esta canal?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveCanalesAddMore"){
    extract($_REQUEST);
    
    foreach($tipo as $key=>$un){
        $canalPDO = new Canal();    
        $canalPDO->nombre = $nombre[$key];
        $canalPDO->tipo = $tipo[$key];  
        $canalPDO->fechaInicio = $fechaInicio[$key];
        $canalPDO->fechaFin = $fechaFin[$key];
        $canalPDO->activo = 'TRUE';
        $canalPDO->vende = $vende[$key];
        $canalPDO->puntoVenta = $puntoVenta[$key];
        $canalPDO->create();

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}