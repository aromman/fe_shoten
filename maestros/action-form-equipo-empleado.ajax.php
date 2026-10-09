<?php

session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/equipoEmpleado.php";
require_once "../app/models/equipo.php";
require_once "../app/models/empleados.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowEquipoEmpleado"){
    ?>
    <tr>
        <td>
            <select name="id_equipo[]" class="form-control" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $equipoPDO = new Equipo();
                    $equipos = $equipoPDO->getAll("equipo");
                    foreach ($equipos as $val) {
                    ?>
                    <option value="<?php echo $val['id']?>"><?php echo mb_strtoupper($val['equipo'],'UTF-8')?></option>
                <?php }
                    unset($equipoPDO);
                ?>
            </select>
        </td>
        <td>
            <select name="id_empleado[]" class="form-control" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $empleadoPDO = new Empleado();
                    $empleados = $empleadoPDO->getAll("apellido");
                    foreach ($empleados as $val) {
                    ?>
                    <option value="<?php echo $val['id']?>"><?php echo mb_strtoupper($val['apellido'].', '.$val['nombre'],'UTF-8')?></option>
                <?php }
                    unset($empleadoPDO);
                ?>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este integrante?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}

//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveEquipoEmpleadoAddMore"){
    extract($_REQUEST);

    foreach($id_equipo as $key=>$un){

        $newEquipoEmpleadoPDO = new EquipoEmpleado();
        $newEquipoEmpleadoPDO->id_equipo = $id_equipo[$key];
        $newEquipoEmpleadoPDO->id_empleado = $id_empleado[$key];
        $newEquipoEmpleadoPDO->create();
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}
