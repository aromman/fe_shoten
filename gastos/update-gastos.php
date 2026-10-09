<?php
require_once dirname(__DIR__) . '/bff/gastos/control.php';
require_once "../app/models/gastos.php";

$fecha = $detalle = $monto = "";
$mensaje = "";
$listados = gastos_bff_cargar_listados();

if (isset($_POST["id"]) && !empty($_POST["id"])) {
    $mensaje = "Actualizando";
    header('location: ../bff/gastos/actions.php');
    exit();
} else {
    $mensaje = "Editando";

    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id = (int) $_GET["id"];
        $gastosPDO = new Gastos();
        $valGasto = $gastosPDO->getById($id);
        if (!is_array($valGasto) || empty($valGasto)) {
            header("location: error.php");
            exit();
        }

        $fecha = $valGasto["fecha"];
        $detalle = $valGasto["detalle"];
        $monto = $valGasto["monto"];
        $clase = $valGasto["id_clase"];
        $medioPago = $valGasto["id_medio_pago"];
        $canal = $valGasto["id_canal"];
    } else {
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Actualizar Gastos</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Gasto</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="gastos.php">Gastos</a></li>
                            <li class="breadcrumb-item active">Actualizar Gasto</li>
                        </ol>
                        <div class="card-body">
                            <form action="../bff/gastos/actions.php" method="post">
                                <input type="hidden" name="action" value="update">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Canal</label>
                                    <select name="canal" id="canal" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php foreach ($listados['canales'] as $val) { ?>
                                        <option 
                                            value="<?php echo $val['id']?>" 
                                            data-subtext="(<?php echo $val['id']?>)"
                                            <?php if($val['id']==$canal) echo 'selected="selected"'; ?>
                                        >
                                            <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                        </option>
                                    <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Detalle</label>
                                    <input type="text" name="detalle" value="<?php echo $detalle;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Monto</label>
                                    <input type="number" step=".01" name="monto" value="<?php echo $monto;?>" class="form-control" required="required">
                                </div>

                                <div class="form-group">
                                    <label>Clase</label>
                                    <select name="clase" id="clase" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php foreach ($listados['clases'] as $val) { ?>
                                        <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" <?php if($val['id']==$clase) echo 'selected="selected"'; ?>><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                    <?php } ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Medio Pago</label>
                                    <select name="medioPago" id="medioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php foreach ($listados['mediosPago'] as $val) { ?>
                                        <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" <?php if($val['id']==$medioPago) echo 'selected="selected"'; ?>><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                    <?php } ?>
                                    </select>
                                </div>

                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="gastos.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>
