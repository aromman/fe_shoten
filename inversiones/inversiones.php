<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file

require_once "../app/models/inversores.php";
require_once "../app/models/inversiones.php";

// obtener cantidad de libros invertidos
$inversionesPDO = new Inversiones();
$inversoresPDO = new Inversores();

$cantidadTotalLibros = $amountCotizacion = $valuacionTotal = 0;

$rs = $inversionesPDO->getTotal();
if (!is_null($rs)) {
    $cantidadTotalLibros = $rs['total'];
}

// obtener cotizacion
$rs = $inversionesPDO->getCotizacion();
if (!is_null($rs)) {
    $amountCotizacion = $rs['cotizacion'];
}

// valuar total
if ($cantidadTotalLibros >0 && $amountCotizacion>0){
    $valuacionTotal = $cantidadTotalLibros * $amountCotizacion;    
} 

$classInversor = array(1=>"success",2=>"warning",3=>"info");

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Inversiones</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
                
                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                $("#addmoreinversiones").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-inversiones.ajax.php',
                        data:{'action':'addDataRowInversiones'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveinversiones').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formInversiones").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-inversiones.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveinversiones').addClass("disabled");
                        }
                    });
                });
                
            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Inversiones</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <p class="fs-5 text-muted">A la fecha el total invertido es <b>$ <?php echo number_format($valuacionTotal, 2, ".", "");?></b> que equivalen a <b><?php echo number_format($cantidadTotalLibros, 0, ".", "");?></b> libros segun la cotizacion actual de <b>$<?php echo number_format($amountCotizacion, 2, ".", "");?></b> cada libro.</p>
                        
                        <div class="row row-cols-1 row-cols-md-3 mb-3 text-center">
                        <?php 
                            $inversores = $inversoresPDO->getAll("nombre");
                            $idxInversor = 1;
                            foreach ($inversores as $inversor) {

                                $idInversor = $inversor['id'];
                                
                                $rs = $inversionesPDO->getByInversor($idInversor);
                                if (!is_null($rs)) {
                                    
                                    $className = $classInversor[$idxInversor];
                                    $idxInversor+=1;
                                    if ($idxInversor>3){
                                        $idxInversor = 1;
                                    } 
                                    $nombreInversor = $inversor['nombre'];
                                    
                                    $cantidadLibros = $rs['total'];
                                    $porcentaje = $valuacion = 0;
                                    
                                    if ($cantidadLibros >0 && $cantidadTotalLibros>0){
                                        $porcentaje = $cantidadLibros * 100 / $cantidadTotalLibros ;
                                        $valuacion = $cantidadLibros * $amountCotizacion;
                                    }    

                            ?>
                            <div class="col">
                                <div class="card mb-4 rounded-3 shadow-sm border-<?php echo $className; ?>">
                                    <div class="card-header py-3 text-white bg-<?php echo $className; ?> border-<?php echo $className; ?>">
                                        <h4 class="my-0 fw-normal"><?php echo $nombreInversor;?></h4>
                                    </div>
                                    <div class="card-body">
                                        <h1 class="card-title pricing-card-title">$<?php echo number_format($valuacion, 2, ".", "");?></h1>
                                        <ul class="list-unstyled mt-3 mb-4">
                                        <li><?php echo number_format($cantidadLibros, 0, ".", "");?> libros</li>
                                        <li><?php echo number_format($porcentaje, 0, ".", "");?>% participacion</li>
                                        </ul>
                                        <a href="pagar-deuda.php?id=<?php echo $idInversor;?>&saldo=<?php echo $valuacion;?>" class="btn btn-primary" id="payInversiones"><i class="fa fa-fw fa-pencil"></i>Pagar Deuda</a>
                                    </div>
                                </div>
                            </div>
                          <?php
                                }
                            }
                          ?>  
                        </div>

                        <div class="card-body">
                            <form id="formInversiones" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveinversionesAddMore">
                                <table class="table table-striped table-bordered" id="tb">

                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Detalle</th>
                                            <th class="text-center">Monto Pesos</th>
                                            <th class="text-center">Cotizacion</th>
                                            <th class="text-center">Libros</th>
                                            <th class="text-center">Invertido Por</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $result = $inversionesPDO->getAll("fecha desc");
                                            foreach ($result as $row) {

                                                    $fechaPago = date('d/m/Y', strtotime($row['fecha']));

                                                    $libros = $row['libros'];
                                                    
                                                    $amount_Pesos = $row['monto'];

                                                    $amount_Cotiza = $row['cotizacion'];
                                                    $id_invertido_por = $row['id_invertido_por'];
                                                    $invertidoPor = $id_invertido_por;
                                                    $inversor = $inversoresPDO->getById($id_invertido_por);
                                                    if (!is_null($inversor)){
                                                        $invertidoPor = $inversor['nombre'];
                                                    } 
                                            
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $fechaPago; ?></td>
                                                    <td><?php echo $row['detalle']; ?></td>
                                                    <td align="right"><?php echo number_format($amount_Pesos, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo number_format($amount_Cotiza, 2, ".", ""); ?></td>
                                                    <td align="right"><?php echo number_format($libros, 2, ".", ""); ?></td>
                                                    <td><?php echo $invertidoPor; ?></td>
                                                    <td align="center">
                                                        <a href="../delete-entity.php?entityName=inversiones&forwardOk=inversiones/inversiones.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta inversion?');"><i class="fa fa-fw fa-trash"></i></a>
                                                    </td>                            
                                                </tr>
                                        <?php
                                                }
                                        ?>
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="7">
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreinversiones"><i class="fa fa-fw fa-plus-circle"></i> Nueva Inversion</a>
                                                <button type="submit" name="saveinversiones" id="saveinversiones" value="saveinversiones" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    
                                </table>
                            </form>
                        </div>
                        
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>

</html>
