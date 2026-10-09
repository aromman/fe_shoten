<?php

session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

if(!isset($_GET["id"]) || trim($_GET["id"]) === ""){
    header("location: error.php");
    exit;
}

$id = (int) $_GET["id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Actualizar Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
</head>
<body class="sb-nav-fixed">
<?php include '../topBar.php';?>
<div id="layoutSidenav">
    <?php include '../sidebar.php';?>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                <h1 class="mt-4">Cuenta</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="cuentas.php">Cuentas</a></li>
                    <li class="breadcrumb-item active">Editar Cuenta</li>
                </ol>
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-table me-1"></i>Datos Cuenta</div>
                    <div class="card-body">
                        <form id="formCuenta" action="../bff/cuentas/actions.php" method="post">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <div class="mb-3">
                                <label>Nombre</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required="required">
                            </div>
                            <div class="mb-3" id="tipoWrapper">
                                <label>Tipo</label>
                                <select name="tipo" id="tipo" class="form-control" required="required">
                                    <option value="">Seleccione</option>
                                    <option value="A">Activo</option>
                                    <option value="P">Pasivo</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Tipo Saldo</label>
                                <select name="tipoSaldo" id="tipoSaldo" class="form-control" required="required">
                                    <option value="">Seleccione</option>
                                    <option value="E">Efectivo</option>
                                    <option value="B">Bancario</option>
                                    <option value="D">Deuda</option>
                                    <option value="P">Pozo</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Ambito de uso</label>
                                <select name="ambitoUso" id="ambitoUso" class="form-control" required="required">
                                    <option value="COMERCIAL">Comercial</option>
                                    <option value="PERSONAL">Personal</option>
                                    <option value="EMPLEADO">Empleado</option>
                                </select>
                            </div>
                            <div class="mb-3" id="empleadoWrapper" style="display:none;">
                                <label>Empleado</label>
                                <select name="idEmpleado" id="idEmpleado" class="form-control">
                                    <option value="">Seleccione</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Saldo Inicial</label>
                                <input type="number" step=".01" name="saldoInicial" id="saldoInicial" class="form-control" required="required">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">Grabar</button>
                                <a href="cuentas.php" class="btn btn-secondary ml-2">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        <?php include '../footer.php';?>
    </div>
</div>
<script>
$(function() {
    var $ambitoUso = $('#ambitoUso');
    var $empleadoWrapper = $('#empleadoWrapper');
    var $idEmpleado = $('#idEmpleado');
    var $tipoWrapper = $('#tipoWrapper');
    var $tipo = $('#tipo');
    var idEmpleadoPendiente = null;

    var toggleEmpleado = function() {
        var esEmpleado = $ambitoUso.val() === 'EMPLEADO';
        $empleadoWrapper.toggle(esEmpleado);
        $idEmpleado.prop('required', esEmpleado);
        if (!esEmpleado) {
            $idEmpleado.val('');
        }
    };

    var toggleTipo = function() {
        var esComercial = $ambitoUso.val() === 'COMERCIAL';
        $tipoWrapper.toggle(!esComercial);
        if (esComercial) {
            $tipo.val('A');
        }
    };

    $ambitoUso.on('change', function() {
        toggleEmpleado();
        toggleTipo();
    });

    $.getJSON('../bff/empleados/control.php')
        .done(function(response) {
            var empleados = response.data && response.data.empleados ? response.data.empleados : [];
            $.each(empleados, function(index, empleado) {
                $idEmpleado.append(
                    $('<option></option>').val(empleado.id).text(empleado.nombreCompleto)
                );
            });
            if (idEmpleadoPendiente !== null) {
                $idEmpleado.val(idEmpleadoPendiente);
            }
        });

    $.getJSON('../bff/cuentas/detalle.php?id=<?php echo $id; ?>')
        .done(function(response) {
            var cuenta = response.data.cuenta || {};
            $('#nombre').val(cuenta.nombre || '');
            $('#tipo').val(cuenta.tipo || '');
            $('#tipoSaldo').val(cuenta.tipo_saldo || cuenta.tipoSaldo || '');
            $('#saldoInicial').val(cuenta.saldo_inicial || cuenta.saldoInicial || 0);
            $ambitoUso.val(cuenta.ambito_uso || cuenta.ambitoUso || 'COMERCIAL');
            idEmpleadoPendiente = cuenta.id_empleado || cuenta.idEmpleado || '';
            $idEmpleado.val(idEmpleadoPendiente);
            toggleEmpleado();
            toggleTipo();
        })
        .fail(function() {
            alert('No se pudo cargar la cuenta.');
        });
});
</script>
</body>
</html>
