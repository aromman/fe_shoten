<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$userRole = isset($_SESSION['user.rol']) ? (int) $_SESSION['user.rol'] : -1;

// Include config file
require_once "../server.php";
require_once "../app/models/medioPago.php";
require_once "../app/models/reservas.php";

if(
    (isset($_REQUEST['id']) && $_REQUEST['id']!="" ) &&
    (isset($_REQUEST['accionPagar']) && $_REQUEST['accionPagar']=="pagar" )
){
    // recupero la venta
    
    $id_reserva = $_REQUEST['id'];
    $id_medioPago = $_REQUEST['medioPago'];
    $monto = $_REQUEST['monto'];
    $nombre = $_REQUEST['nombre'];

    $sql = "update reservas set id_estado = 2, monto = ".$monto." WHERE id = ".$id_reserva;
    error_log(PHP_EOL.$sql, 3, "my-errors.log");
    $db->query($sql);

    $fecha = date("Y-m-d");

    // Incerto movimiento en cuenta
    $medioPagoPDO = new MedioPago();
    $mediosPago = $medioPagoPDO->getById($id_medioPago);
    $idCuenta = $mediosPago['id_cuenta'];
    // inserto movimiento
    $idCausal = 1; // VENTAS
    $sqlInsert = 'INSERT INTO cuentas_movimientos (id_cuenta, fecha, tipo_movimiento, descripcion, monto, consolidado, id_causal) VALUES ('.$idCuenta.',"'.$fecha.'","C", "Pago Reserva de '.$nombre.'", '.$monto.', "FALSE", '.$idCausal.') ';
    error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
    $db->query($sqlInsert);

    header('location: reserva.php');
    exit;
}
abstract class Estado
{
    const Reservado = 1;
    const Pagado = 2;
    const Presente = 3;
    const Ausente = 4;
}

$classEstado = array(1=>"table-light",2=>"table-info",3=>"table-success",4=>"table-warning");
$NombreEstado = array(1=>"Reservado",2=>"Pagado",3=>"Presente",4=>"Ausente");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Reservas</title>
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
                
                $("#addmorereservas").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-reserva.ajax.php',
                        data:{'action':'addDataRowReservas'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveReservas').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formReservas").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-reserva.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveReservas').addClass("disabled");
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
                        <h1 class="mt-4">Historial Reservas</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Ultimos 7 Dias</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Historico de Reservas
                            </div>
                            <div class="card-body">
                                <form id="formReservas" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveReservasAddMore">
                                <div class="table-responsive">
                                <table class="table" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Hora</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Clave</th>
                                            <th class="text-center">Horas</th>
                                            <th class="text-center">Mesas</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Pagar</th>
                                            <th class="text-center">Presente</th>
                                            <th class="text-center">Ausente</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php

                                            $reservaPDO = new Reserva();
                                            $reservas = $reservaPDO->getAllLastWeek("fecha, hora, cliente ASC");
                                            if (!is_null($reservas)){
                                                foreach ($reservas as $row) {
                                                    $colFecha = $row['fecha'];
                                                    $colHora = $row['hora'];
                                                    $colNombre = $row['cliente'];
                                                    $colClave = $row['clave'];
                                                    $colHoras = $row['horas'];
                                                    $colMesas = $row['mesas'];
                                                    $colIdEstado = $row['id_estado'];
                                                    $colEstado = $NombreEstado[$colIdEstado];
                                                    $className = $classEstado[$colIdEstado];
                                                    $idReserva = $row['id'];
                                        ?>

                                                <tr class="<?php echo $className; ?>">
                                                    <td><?php echo $colFecha;?></td>
                                                    <td><?php echo $colHora;?></td>
                                                    <td><?php echo $colNombre;?></td>
                                                    <td><?php echo $colClave;?></td>
                                                    <td><?php echo $colHoras;?></td>
                                                    <td><?php echo $colMesas;?></td>
                                                    <td><?php echo $colEstado;?></td>
                                                    
                                                    <!-- Pagar-->
                                                    <td align="center">
                                                    <?php
                                                        if ($userRole === 0) {
                                                        switch ($colIdEstado) {
                                                        case Estado::Reservado:
                                                    ?>
                                                            <a href="#"
                                                            class="text-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#pagarModal"
                                                            data-id="<?php echo $idReserva;?>"
                                                            data-fecha="<?php echo $colFecha;?>"
                                                            data-hora="<?php echo $colHora;?>"
                                                            data-horas="<?php echo $colHoras;?>"
                                                            data-mesas="<?php echo $colMesas;?>"
                                                            data-nombre="<?php echo $colNombre;?>">
                                                                <i class="fa fa-fw fa-credit-card"></i>
                                                            </a>
                                                    <?php
                                                            break;
                                                        }
                                                        }
                                                    ?>
                                                    </td>

                                                    <!-- Presente-->
                                                    <td align="center">
                                                    <?php
                                                        if ($userRole === 0) {
                                                        switch ($colIdEstado) {
                                                        case Estado::Pagado:
                                                    ?>
                                                            <a href="action-reserva.php?id_estado=<?php echo Estado::Presente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                    <?php
                                                            break;
                                                        case Estado::Ausente:
                                                    ?>
                                                            <a href="action-reserva.php?id_estado=<?php echo Estado::Presente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                           
                                                    <?php                                                            
                                                            break;
                                                        }
                                                        }
                                                    ?>    
                                                    </td>                            
                                                    
                                                    <!-- Ausente -->
                                                    <td align="center">
                                                    <?php
                                                        if ($userRole === 0) {
                                                        switch ($colIdEstado) {
                                                        case Estado::Pagado:
                                                    ?>
                                                            <a href="action-reserva.php?id_estado=<?php echo Estado::Ausente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-down"></i></a>
                                                    <?php
                                                            break;
                                                        }
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
                                                <a href="javascript:;" class="btn btn-danger" id="addmorereservas"><i class="fa fa-fw fa-plus-circle"></i> Nueva</a>
                                                <button type="submit"
                                                        name="saveReservas"
                                                        id="saveReservas"
                                                        value="saveReservas"
                                                        class="btn btn-primary"
                                                        hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
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

                <?php include_once '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        <?php include_once 'pagarModal.php'; ?>
        
        <script>
        $('#pagarModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var fecha = applicant.data('fecha');
            var hora = applicant.data('hora');
            var nombre = applicant.data('nombre');
            var horas = applicant.data('horas');
            var mesas = applicant.data('mesas');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="fecha"]').val(fecha);
            modal.find('input[name="hora"]').val(hora);
            modal.find('input[name="nombre"]').val(nombre);
            modal.find('input[name="horas"]').val(horas);
            modal.find('input[name="mesas"]').val(mesas);
        });
        </script>

    </body>
</html>
