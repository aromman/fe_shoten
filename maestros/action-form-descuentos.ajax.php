<?php 

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/descuento.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowDescuentos"){
    ?>
    <tr>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td>
            <select name="tipo[]" id="tipo" class="form-control" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <option value="F" data-subtext="(F)">Monto Fijo</option>
                <option value="P" data-subtext="(P)">Porcentaje</option>
            </select>
        </td>
        <td><input type="number" step="0" name="valor[]" class="form-control"></td>
        <td><input type="number" step="0" name="porcentaje[]" class="form-control"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este descuento?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveDescuentosAddMore"){
    extract($_REQUEST);
    
    foreach($nombre as $key=>$un){
        
        $newDescuentoPDO = new Descuento();
        $newDescuentoPDO->nombre = $nombre[$key];
        $newDescuentoPDO->tipo = $tipo[$key]; 
        $newDescuentoPDO->valor = $valor[$key];
        $newDescuentoPDO->porcentaje = $porcentaje[$key];
        $newDescuentoPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}
