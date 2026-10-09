<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "../server.php";
require_once "../app/models/comandas.php";

$id_canal = $_SESSION["user.canal"];
$rol = $_SESSION["user.rol"];

$classEstadoComanda = array(1=>"primary",2=>"success",3=>"warning",4=>"danger", 5=>"info", 6=>"primary");
$nombreEstadoComanda = array(1=>"Pendiente",2=>"Preparando",3=>"A Entregar",5=>"Entregado");
$nombreAccion = array(1=>"Preparar",2=>"Completar",3=>"Entregar");
$proximoEstado = array(1=>"2",2=>"3",3=>"5");


function timeago($hora1,$hora2){
    $separar[1]=explode(':',$hora1);
    $separar[2]=explode(':',$hora2);
    $total_minutos_trasncurridos[1] = ($separar[1][0]*60)+$separar[1][1];
    $total_minutos_trasncurridos[2] = ($separar[2][0]*60)+$separar[2][1];
    $total_minutos_trasncurridos = $total_minutos_trasncurridos[1]-$total_minutos_trasncurridos[2];
    return (int) $total_minutos_trasncurridos;
}

if(
    (isset($_REQUEST['id']) && $_REQUEST['id']!="" ) &&
    (isset($_REQUEST['accionEditar']) && $_REQUEST['accionEditar']=="preparar" )
){
    // recupero la venta
    
    $id = $_REQUEST['id'];
    $nombre = $_REQUEST['nombre'];
    $beeper = $_REQUEST['beeper'];

    $estado = 2; // En preparacion

    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");

    $sql = "update comandas set nombre= '".$nombre."',beeper= '".$beeper."',id_estado = ".$estado.", updateDate='".$updateTime."' where id_venta_header =" . $id;
    error_log(PHP_EOL."[".date('d.m.Y h:i:s'). "] ".$sql, 3, "my-errors.log");
    
    $db->query($sql);

}



$page = $_SERVER['PHP_SELF'];
$sec = "30";

?>

<html lang="en">
    <head>
        <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Monitor de Cocina</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">
        <?php include_once '../topBar.php';?>
        
        <div id="layoutSidenav">
            
            <?php include_once '../sidebar.php';?>
            
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h4 class="mt-4">Cocina</h4>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Monitoreo</li>
                        </ol>
                        <div class="row row-cols-1 row-cols-md-4 mb-4 text-center">
                        <?php
                            $hayRegistros = false;
                            $comandaPDO = new Comanda();
                            $comandas = $comandaPDO->getAllInCurse();
                            if (!is_null($comandas)){
                                $colIdVentaHeaderAnt = '';
                                $estadoAnt = '';
                                $cantidadTotal = 0;

                                $fecha1 = new DateTime();
                                $fecha1->format("Y-m-d H:i:s");

                                foreach ($comandas as $row) {
                                    $colIdVentaHeader = $row['id_venta_header'];
                                    $colUnidades = $row['unidades'];
                                    $colProducto = $row['producto'];
                                    $updateDate = $row['updateDate'];
                                    
                                    date_default_timezone_set('America/Argentina/Buenos_Aires');
                                    
                                    $fecha2 = new DateTime($updateDate);
                                    $fecha2->format("Y-m-d H:i:s");

                                    $inicio = $fecha2->format("H:i");
                                    
                                    $fecha1 = new DateTime();
                                    $fin = $fecha1->format("H:i");

                                    $minPasados = timeago($fin, $inicio);
                                    $estado = $row['id_estado'];

                                    if ($estado == 1){
                                        $idxEstado = 1;
                                    } elseif ($estado == 2){
                                        if ($minPasados<5){
                                            $idxEstado = 2;
                                        } elseif ($minPasados<10){
                                            $idxEstado = 3;
                                        } else {
                                            $idxEstado = 4;
                                        }
                                    } elseif ($estado == 3){
                                        $idxEstado = 5;
                                    } elseif ($estado == 5){
                                        $idxEstado = 6;
                                    }

                                    if ($minPasados < 59) {
                                        $leyendaMin = "Pasaron ".$minPasados ." Minutos";
                                    } else {
                                        $leyendaMin = "Pasaron Horas";
                                    }

                                    $className = $classEstadoComanda[$idxEstado];
                                    $tituloOrigen = "MOSTRADOR : ". $nombreEstadoComanda[$estado];

                                    // primer registro
                                    if (empty($colIdVentaHeaderAnt)){
                        ?>
                                    <div class="col">
                                        <div class="card mb-4 rounded-3 shadow-sm border-<?php echo $className; ?>">
                                            <div class="card-header py-3 text-white bg-<?php echo $className; ?> border-<?php echo $className; ?>">
                                                <h4 class="my-0 fw-normal"><?php echo $tituloOrigen?></h4>
                                                <h4 class="my-0 fw-normal">Solicitado <?php echo $inicio;?> hrs</h4>
                                                <h4 class="my-0 fw-normal"><?php echo $leyendaMin;?></h4>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mt-3 mb-4">

                        <?php
                                        
                                    } else if(!empty($colIdVentaHeaderAnt) && $colIdVentaHeaderAnt!=$colIdVentaHeader){
                                        // corte de contro
                        ?>
                                                </ul>
                                                <a href="actualizar.php?forwardOk=monitor.php&id=<?php echo $colIdVentaHeaderAnt;?>&estado=<?php echo $proximoEstado[$estadoAnt];?>" class="btn btn-primary"><i class="fa fa-fw fa-circle-plus"></i><?php echo $nombreAccion[$estadoAnt];?></a>
                                                <a target="_blank" rel=“noopener” style=“outline: currentcolor none medium;” href="view-comanda.php?forwardOk=monitor.php&id=<?php echo $colIdVentaHeaderAnt;?>" class="btn btn-primary"><i class="fa fa-fw fa-eye"></i>Imprimir</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="card mb-4 rounded-3 shadow-sm border-<?php echo $className; ?>">
                                            <div class="card-header py-3 text-white bg-<?php echo $className; ?> border-<?php echo $className; ?>">
                                                <h4 class="my-0 fw-normal"><?php echo $tituloOrigen?></h4>
                                                <h4 class="my-0 fw-normal">Solicitado <?php echo $inicio;?> hrs</h4>
                                                <h4 class="my-0 fw-normal"><?php echo $leyendaMin;?></h4>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-unstyled mt-3 mb-4">
                                
                          <?php
                                    $cantidadTotal = 0;
                                
                                    }
                          ?>
                                     <li><?php echo number_format($colUnidades, 0, ".", "").' '.$colProducto ;?></li>

                        <?php

                                    // guardo valores
                                    $hayRegistros = true;
                                    $colIdVentaHeaderAnt = $colIdVentaHeader;
                                    $estadoAnt = $estado;
                                    $cantidadTotal += $colUnidades;

                                                }
                                            }
                        

                        if ($hayRegistros) {
                        ?>
                                                </ul>
                                                <a href="actualizar.php?forwardOk=monitor.php&id=<?php echo $colIdVentaHeaderAnt;?>&estado=<?php echo $proximoEstado[$estadoAnt];?>" class="btn btn-primary"><i class="fa fa-fw fa-circle-plus"></i><?php echo $nombreAccion[$estadoAnt];?></a>
                                                <a target="_blank" rel=“noopener” style=“outline: currentcolor none medium;” href="view-comanda.php?forwardOk=monitor.php&id=<?php echo $colIdVentaHeaderAnt;?>" class="btn btn-primary"><i class="fa fa-fw fa-eye"></i>Imprimir</a>
                                            </div>
                                        </div>
                                    </div>
                        <?php

                        }
                        ?>
                        </div>
                    </div>
                </main>
                
                <?php include_once '../footer.php';?>

            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
