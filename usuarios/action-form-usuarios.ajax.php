<?php 
require_once "../app/models/users.php";
require_once "../app/models/roles.php";
require_once "../app/models/empleados.php";

if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowUsuarios"){
    ?>
    <tr>
        <td></td>
        <td><input type="text" name="usuario[]" class="form-control" required="required"></td>
        <td><input type="text" name="email[]" class="form-control" required="required"></td>
        <td><input type="text" name="apellido[]" class="form-control" required="required"></td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td>
            <select name="rol[]" id="rol" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                <option value="">Seleccione</option>
                <?php
                    $rolesPDO = new Rol();
                    $roles = $rolesPDO->getAll('rol');
                    if (!is_null($roles)){
                        foreach ($roles as $val) {
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"
                        <?php if($val['id']==$idRol) echo 'selected="selected"'; ?>
                    >
                        <?php echo mb_strtoupper($val['rol'],'UTF-8')?></option>
                <?php 
                        }
                    }
                ?>
            </select>
        </td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este usuario?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveUsuariosAddMore"){
    extract($_REQUEST);
    
    $empleadosPDO = new Empleado();

    foreach($usuario as $key=>$un){
 
        $idEmpleado = 0;
        $rsEmpleado = $empleadosPDO->getByName($apellido[$key],$nombre[$key]);
        if (!is_null($rsEmpleado)){
            $rsEmpleado = $rsEmpleado['id'];
        } else {
            $empleadosPDO->apellido = $apellido[$key];
            $empleadosPDO->nombre = $nombre[$key];
            $idEmpleado = $empleadosPDO->create();
        }

        $chars = 8; // 
        $data = $apellido[$key] . '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz' . $nombre[$key];
        $password = substr(str_shuffle($data), 0, $chars);
        $password = password_hash($password, PASSWORD_DEFAULT);

        $usuarioPDO = new User();
        $usuarioPDO->usuario = $usuario[$key];
        $usuarioPDO->email = $email[$key];
        $usuarioPDO->idRol = $rol[$key];
        $usuarioPDO->idEmpleado = $idEmpleado;
        $usuarioPDO->password = $password;
        $usuarioPDO->create();
        

    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
}