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
$fecha = date("Y-m-d");
$canal = isset($_SESSION["user.canal"]) ? (int) $_SESSION["user.canal"] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Transferencias entre Cuentas</title>
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
                <h1 class="mt-4">Transferencias entre Cuenta</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="cuentas.php">Cuentas</a></li>
                    <li class="breadcrumb-item active">Transferencias</li>
                </ol>
                <div class="card-body">
                    <form action="../bff/cuentas/actions.php" method="post" id="formTransfer">
                        <input type="hidden" name="action" value="transfer">
                        <input type="hidden" name="cuentaOrigen" value="<?php echo (int) $id; ?>"/>
                        <input type="hidden" name="canal" value="<?php echo (int) $canal; ?>"/>
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8');?>" class="form-control" required="required">
                        </div>
                        <div class="form-group">
                            <label>Cuenta Origen</label>
                            <input type="text" name="nombreOrigen" id="nombreOrigen" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Cuenta Destino</label>
                            <select name="cuentaDestino" id="cuentaDestino" class="form-control" required="required">
                                <option value="">Seleccione</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Importe</label>
                            <input type="number" step="0.01" name="importe" class="form-control" required="required">
                        </div>
                        <br>
                        <input type="submit" class="btn btn-primary" value="Grabar">
                        <a href="cuentas.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </form>
                </div>
            </div>
        </main>
        <?php include '../footer.php';?>
    </div>
</div>
<script>
$(function() {
    $.getJSON('../bff/cuentas/detalle.php?id=<?php echo $id; ?>')
        .done(function(response) {
            var cuenta = response.data.cuenta || {};
            $('#nombreOrigen').val(cuenta.nombre || '');


            $.getJSON('../bff/cuentas/control.php')
                .done(function(resp) {
                    var lista = (resp.data && resp.data.cuentas) ? resp.data.cuentas : [];
                    $.each(lista, function(_, item) {
                        if (String(item.id) !== String(<?php echo $id; ?>)) {
                            $('#cuentaDestino').append('<option value="' + item.id + '">' + item.nombre + '</option>');
                        }
                    });
                });
        })
        .fail(function() {
            alert('No se pudo cargar la transferencia.');
        });
});
</script>
</body>
</html>
