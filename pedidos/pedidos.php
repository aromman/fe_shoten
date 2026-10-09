<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/pedidos.php";

$pedidosPDO = new Pedidos();

$result = $pedidosPDO->getAllVigentes();
$oneMonth = 1;
$rsFinalizados = $pedidosPDO->getFinalizadosByMonths($oneMonth);

abstract class EstadoPedido{
    const Pendiente = 1;
    const Solicitado = 2;
    const Disponible = 3;
    const Contactado = 4;
    const Entregado = 5;
    const Agotado = 6;
    const Cancelado = 7;
}

$accionEstado= array(1=>"Solicitar a Proveedor",2=>"Esperar Entrega",3=>"Contactar Cliente",4=>"Esperar Retiro", 5=>"Retirado por Cliente",6=>"Esperar reedicion",7=>"Cancelado por Cliente");

$classEstado = array(1=>"table-light",2=>"table-info",3=>"table-success",4=>"table-warning", 5=>"table-primary",6=>"table-dark",7=>"table-danger");


if(
    (isset($_REQUEST['accionContacto']) and $_REQUEST['accionContacto']=="contacto" )
){
    // recupero el pedido
    
    $id_pedido = $_REQUEST['id'];
    $observacion = $_REQUEST['observacion'];
    $fecha = $_REQUEST['fecha'];

    $pedidosPDO->id = $id_pedido;
    $pedidosPDO->observacion = $observacion;
    $pedidosPDO->fechaActualizacion = $fecha;
    $pedidosPDO->update();

    header('location: pedidos.php');
    exit;
}

if(
    (isset($_REQUEST['accionEditar']) and $_REQUEST['accionEditar']=="editar" )
){
    // recupero el pedido
    
    $id_pedido = $_REQUEST['id'];
    $cliente = $_REQUEST['cliente'];
    $contacto = $_REQUEST['contacto'];
    $producto = $_REQUEST['producto'];

    $pedidosPDO->id = $id_pedido;
    $pedidosPDO->cliente = $cliente;
    $pedidosPDO->contacto = $contacto;
    $pedidosPDO->producto = $producto;
    $pedidosPDO->update();
    
    header('location: pedidos.php');
    exit;
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
        <title>Pedidos</title>
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
                
                $("#addmorepedidos").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-pedidos.ajax.php',
                        data:{'action':'addDataRowPedidos'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#savePedidos').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formPedidos").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-pedidos.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#savePedidos').addClass("disabled");
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
                        <h1 class="mt-4">Pedidos</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Pedidos en Curso
                            </div>
                            <div class="card-body">
                                <form id="formPedidos" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="savePedidosAddMore">
                                <div class="table-responsive">
                                <table class="table" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Cliente</th>
                                            <th class="text-center">Contacto</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Accion</th>
                                            <th class="text-center">Fecha Actualizacion</th>
                                            <th class="text-center">Observacion</th>
                                            <th class="text-center">Contactar</th>
                                            <th class="text-center">Avanzar</th>
                                            <th class="text-center">Cancelar</th>
                                            <th class="text-center">Reversar</th>
                                            <th class="text-center">Editar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($result) ) {
                                                foreach($result as $row){
                                                    $colId = $row['id'];
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colCliente = $row['cliente'];
                                                    $colProducto = $row['producto'];
                                                    $colContacto = $row['contacto'];
                                                    $colEstado = $row['estado'];
                                                    $colIdEstado = $row['id_estado'];
                                                    $colAccionEstado = $accionEstado[$colIdEstado];
                                                    $colFechaActualizacion = date('d/m/Y', strtotime($row['fecha_actualizacion']));
                                                    $colObservacion = $row['observacion'];
                                                    $colDiasActualizacion = $row['dias_actualizacion'];

                                                    $className = $classEstado[$colIdEstado];
                                        ?>

                                                <tr class="<?php echo $className; ?>">
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colCliente;?></td>
                                                    <td><?php echo $colContacto;?></td>
                                                    <td><?php echo $colProducto;?></td>
                                                    <td><?php echo $colEstado;?></td>
                                                    <td><?php echo $colAccionEstado;?></td>
                                                    <td align="center"><?php echo $colFechaActualizacion;?></td>
                                                    <td><?php echo $colObservacion;?></td>
                                                    <!-- Contactar -->        
                                                     <td>
                                                        <?php
                                                            if (($colIdEstado==EstadoPedido::Contactado or $colIdEstado==EstadoPedido::Agotado) and $colDiasActualizacion > 7){
                                                        ?>
                                                        
                                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#contactoModal" data-id="<?php echo $colId;?> " >
                                                            <i class="fa fa-fw fa-headset"></i></a>
                                                        </a>
                                                        <?php
                                                            }
                                                        ?>
                                                    </td>
                                                     
                                                    <!-- Avanzar -->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case EstadoPedido::Pendiente:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Solicitado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-shopping-cart"></i></a>
                                                    <?php
                                                            break;
                                                        case EstadoPedido::Agotado:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Solicitado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-shopping-cart"></i></a>
                                                    <?php
                                                            break;
           
                                                        case EstadoPedido::Solicitado:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Disponible; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-thumbs-up"></i></a>
                                                    <?php
                                                            break;
                                                        case EstadoPedido::Disponible:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Contactado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-credit-card"></i></a>
                                                    <?php
                                                            break; 
                                                        case EstadoPedido::Contactado:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Entregado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-truck"></i></a>
                                                    <?php
                                                            break;
                                                        }
                                                    ?>    
                                                    </td>                            

                                                    <!-- Cancelar -->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case EstadoPedido::Pendiente:
                                                    ?>
                                                        <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Agotado; ?>&id=<?php echo $row['id'];?>" class="text-warning"><i class="fa fa-fw fa-cart-arrow-down"></i></a>
                                                    <?php
                                                            break;
                                                        }
                                                    ?>  
                                                        <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Cancelado; ?>&id=<?php echo $row['id'];?>" class="text-danger"><i class="fa fa-fw fa-trash"></i></a>
                                                    </td>                            

                                                    <!-- Reversar -->
                                                    <td align="center">
                                                    <?php
                                                        switch ($colIdEstado) {
                                                        case EstadoPedido::Solicitado:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Pendiente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
                                                    <?php
                                                            break;
                                                        case EstadoPedido::Agotado:
                                                            ?>
                                                                    <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Pendiente; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
                                                    <?php
                                                            break;
                                                        case EstadoPedido::Disponible:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Solicitado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
                                                    <?php
                                                            break; 
                                                        case EstadoPedido::Contactado:
                                                    ?>
                                                            <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Disponible; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
                                                    <?php
                                                            break;
                                                        }
                                                    ?>    
                                                    </td>                            

                                                    <td align="center">
                                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#editarModal" 
                                                                    data-id="<?php echo $colId;?>" 
                                                                    data-cliente="<?php echo $colCliente;?>" 
                                                                    data-contacto="<?php echo $colContacto;?>" 
                                                                    data-producto="<?php echo $colProducto;?>" 
                                                                    >
                                                            <i class="fa fa-fw fa-pencil"></i></a>
                                                        </a>
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
                                                <a href="javascript:;" class="btn btn-danger" id="addmorepedidos"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Pedido</a>
                                                <button type="submit" name="savePedidos" id="savePedidos" value="savePedidos" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </div>
                                </form>
                            </div>
                        </div>

                        <!-- FINALIZADOS -->

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Pedidos Finalizados
                            </div>
                            <div class="card-body">
                                <form id="formPedidosFinalizados" method="post" onSubmit="return false;">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Cliente</th>
                                            <th class="text-center">Contacto</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Observacion</th>
                                            <th class="text-center">Fecha Actualizacion</th>
                                            <th class="text-center">Reversar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($rsFinalizados)) {
                                                foreach ($rsFinalizados as $row){
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colCliente = $row['cliente'];
                                                    $colProducto = $row['producto'];
                                                    $colContacto = $row['contacto'];
                                                    $colEstado = $row['estado'];
                                                    $colIdEstado = $row['id_estado'];
                                                    $colAccionEstado = $accionEstado[$colIdEstado];
                                                    $colFechaActualizacion = date('d/m/Y', strtotime($row['fecha_actualizacion']));
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colCliente;?></td>
                                                    <td><?php echo $colContacto;?></td>
                                                    <td><?php echo $colProducto;?></td>
                                                    <td><?php echo $colEstado;?></td>
                                                    <td><?php echo $colAccionEstado;?></td>
                                                    <td align="center"><?php echo $colFechaActualizacion;?></td>
                                                    <td align="center">
                                                        <a href="action-pedidos.php?id_estado=<?php echo EstadoPedido::Contactado; ?>&id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-rotate-left"></i></a>
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
                                </table>
                                </div>
                                </form>
                            </div>
                        </div>
                        <!-- END FINALIZADOS -->                     


                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>


        <?php include 'contactoModal.php'; ?>
        <?php include 'editarModal.php'; ?>
        
        <script>
        $('#contactoModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
        });
        </script> 

        <script>
        $('#editarModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var cliente = applicant.data('cliente');
            var contacto = applicant.data('contacto');
            var producto = applicant.data('producto');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="cliente"]').val(cliente);
            modal.find('input[name="contacto"]').val(contacto);
            modal.find('input[name="producto"]').val(producto);
        });
        </script> 

    </body>
</html>
