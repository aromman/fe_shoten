<?php

session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/equipo.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowEquipos"){
    ?>
    <tr>
        <td><input type="text" name="equipo[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este equipo?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}

//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveEquiposAddMore"){
    extract($_REQUEST);

    foreach($equipo as $key=>$un){

        $newEquipoPDO = new Equipo();
        $newEquipoPDO->equipo = $equipo[$key];
        $newEquipoPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}
