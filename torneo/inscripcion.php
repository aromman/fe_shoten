<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/inscripcion.php";
require_once "../app/models/medioPago.php";
require_once "../app/models/cuentasMovimientos.php";

if(
    (isset($_REQUEST['id']) and $_REQUEST['id']!="" ) and 
    (isset($_REQUEST['accionPagar']) and $_REQUEST['accionPagar']=="pagar" )
){
    // recupero la venta
    
    $id_inscripcion = $_REQUEST['id'];
    $id_medioPago = $_REQUEST['medioPago'];
    $monto = $_REQUEST['monto'];
    $nombre = $_REQUEST['nombre'];

    $inscripcionPDO = new Inscripcion();
    $inscripcionPDO->id = $id_inscripcion;
    $inscripcionPDO->idEstado = 2;
    $inscripcionPDO->update();

    $fecha = date("Y-m-d");

    // Incerto movimiento en cuenta
    $medioPagoPDO = new MedioPago();
    $mediosPago = $medioPagoPDO->getById($id_medioPago);
    $idCuenta = $mediosPago['id_cuenta'];
    // inserto movimiento
    $idCausal = 1; // VENTAS
    $cuentaMovimientoPDO = new CuentasMovimientos();
    $cuentaMovimientoPDO->idCuenta = $idCuenta;
    $cuentaMovimientoPDO->fecha = $fecha;
    $cuentaMovimientoPDO->tipoMovimiento = "C";
    $cuentaMovimientoPDO->descripcion = "Pago Torneo de ".$nombre;
    $cuentaMovimientoPDO->monto = $monto;
    $cuentaMovimientoPDO->consolidado = "FALSE";
    $cuentaMovimientoPDO->idCausal = $idCausal;
    $cuentaMovimientoPDO->create();

    header('location: inscripcion.php');
    exit;
}

$inscripcionPDO = new Inscripcion();
$inscripciones = $inscripcionPDO->getAllActive("nombre");

abstract class Estado
{
    const Registrado = 1;
    const Pagado = 2;
    const Presente = 3;
    const Ausente = 4;
}

$classEstado = array(1=>"table-light",2=>"table-info",3=>"table-success",4=>"table-warning");
$NombreEstado = array(1=>"Registrado",2=>"Pagado",3=>"Presente",4=>"Ausente");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Inscripcion Torneos</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function(e) {
                $('.selectpicker').selectpicker();
                
                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });
                
                $("#addmoreinscripciones").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-inscripcion.ajax.php',
                        data:{'action':'addDataRowInscripciones'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveInscripciones').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formInscripciones").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-inscripcion.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveInscripciones').addClass("disabled");
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
                        <h1 class="mt-4">Inscripcion Torneos</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Inscripcion a Torneo
                            </div>
                            <div class="card-body">
                                <form id="formInscripciones" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveInscripcionesAddMore">
                                <div class="table-responsive">
                                <table class="table" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Torneo</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Pagar</th>
                                            <th class="text-center">Presente</th>
                                            <th class="text-center">Ausente</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($inscripciones) and count($inscripciones) > 0){

                                                foreach ($inscripciones as $row)
                                                {
                                                    $colTorneo = $row['torneo'];
                                                    $colNombre = $row['nombre'];
                                                    $colIdEstado = $row['id_estado'];
                                                    $colEstado = $NombreEstado[$colIdEstado];
                                                    $className = $classEstado[$colIdEstado];
                                                    $idInscripcion = $row['id'];
                                        ?>

                                                <tr class="<?php echo $className; ?>">
                                                    <td><?php echo $colTorneo;?></td>
                                                    <td><?php echo $colNombre;?></td>
                                                    <td><?php echo $colEstado;?></td>
                                                    
                                                    <!-- Pagar-->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case Estado::Registrado:
                                                    ?>
                                                            <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#pagarModal" data-id="<?php echo $idInscripcion;?>" data-nombre="<?php echo $colNombre;?>"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    <?php
                                                            break;
                                                        }
                                                    ?>
                                                    </td>

                                                    <!-- Presente-->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case Estado::Pagado:
                                                    ?>
                                                            <a href="action-inscripcion.php?id_estado=<?php echo Estado::Presente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                    <?php
                                                            break;
                                                        case Estado::Ausente:
                                                    ?>
                                                            <a href="action-inscripcion.php?id_estado=<?php echo Estado::Presente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                           
                                                    <?php                                                            
                                                            break;
                                                        }
                                                    ?>    
                                                    </td>                            
                                                    
                                                    <!-- Ausente -->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case Estado::Pagado:
                                                    ?>
                                                            <a href="action-inscripcion.php?id_estado=<?php echo Estado::Ausente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-down"></i></a>
                                                    <?php
                                                            break;
                                                        }
                                                    ?>    
                                                    </td>                            

                                                </tr>

                                        <?php
                                                }
                                            }
                                            else
                                            {
                                            echo '0 results';
                                            }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="10">
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreinscripciones"><i class="fa fa-fw fa-plus-circle"></i> Nueva</a>
                                                <button type="submit" name="saveInscripciones" id="saveInscripciones" value="saveInscripciones" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include 'pagarModal.php'; ?>
        
        <script>
        $('#pagarModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var nombre = applicant.data('nombre');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="nombre"]').val(nombre);
        });
        </script> 


    </body>
</html>
