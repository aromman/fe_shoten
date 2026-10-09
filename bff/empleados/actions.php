<?php

session_start();

require_once dirname(__DIR__, 2) . '/app/models/empleados.php';

if (!isset($_SESSION['user.loggedin']) || $_SESSION['user.loggedin'] !== true) {
    header('location: ../../login.php');
    exit;
}

function empleados_bff_redirigir($url)
{
    header('location: ' . $url);
    exit;
}

function empleados_bff_render_row()
{
    ?>
    <tr>
        <td><input type="text" name="apellido[]" class="form-control" required="required"></td>
        <td><input type="text" name="nombre[]" class="form-control" required="required"></td>
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar este empleado?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
}

$action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

try {
    if ($action === 'addDataRowEmpleados') {
        empleados_bff_render_row();
        echo '|***|addmore';
        exit;
    }

    if ($action === 'saveEmpleadosAddMore') {
        $apellidos = isset($_POST['apellido']) && is_array($_POST['apellido']) ? $_POST['apellido'] : array();
        $nombres = isset($_POST['nombre']) && is_array($_POST['nombre']) ? $_POST['nombre'] : array();

        foreach ($apellidos as $key => $apellido) {
            $empleadoPDO = new Empleado();
            $empleadoPDO->apellido = trim((string) $apellido);
            $empleadoPDO->nombre = trim((string) ($nombres[$key] ?? ''));
            $empleadoPDO->create();
        }

        echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
        exit;
    }

    if ($action === 'update') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            throw new RuntimeException('EMPLEADO_INVALIDO');
        }

        $empleadoPDO = new Empleado();
        $empleadoPDO->id = $id;
        $empleadoPDO->apellido = trim((string) ($_POST['apellido'] ?? ''));
        $empleadoPDO->nombre = trim((string) ($_POST['nombre'] ?? ''));
        $empleadoPDO->update();

        empleados_bff_redirigir('../../empleados/empleados.php');
    }

    empleados_bff_redirigir('../../empleados/empleados.php');
} catch (\Throwable $error) {
    error_log($error->getMessage());
    empleados_bff_redirigir('../../errors.php');
}
