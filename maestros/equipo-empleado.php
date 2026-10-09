<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/equipoEmpleado.php";

$equipoEmpleadoPDO = new EquipoEmpleado();
$equipoEmpleados = $equipoEmpleadoPDO->getAllWithNames();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Integrantes de Equipo</title>
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

                $('body').on('mousemove',function(){
                    $('[data-toggle="tooltip"]').tooltip();
                });

                $("#addMoreEquipoEmpleado").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-equipo-empleado.ajax.php',
                        data:{'action':'addDataRowEquipoEmpleado'},
                        success: function(data){
                            $('#tb').append(data);
                            $('#saveEquipoEmpleado').removeAttr('hidden',true);
                        }
                    });
                });

                $("#formEquipoEmpleado").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-equipo-empleado.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveEquipoEmpleado').addClass("disabled");
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
                        <h1 class="mt-4">Integrantes de Equipo</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item"><a href="equipos.php">Equipos</a></li>
                            <li class="breadcrumb-item active">Integrantes de Equipo</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Integrantes de Equipo
                            </div>
                            <div class="card-body">
                                <div id="mag"></div>
                                <form id="formEquipoEmpleado" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveEquipoEmpleadoAddMore">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Equipo</th>
                                            <th class="text-center">Empleado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            if (!is_null($equipoEmpleados) && count($equipoEmpleados) > 0) {

                                                foreach ($equipoEmpleados as $row) {

                                                    // inicializo variables
                                                    $colEquipo = $row['equipo'];
                                                    $colEmpleado = $row['apellido'] . ', ' . $row['nombre'];

                                        ?>

                                                <tr>
                                                    <td><?php echo $colEquipo; ?></td>
                                                    <td><?php echo $colEmpleado; ?></td>

                                                    <td align="center">

                                                        <a href="update-equipo-empleado.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="../delete-entity.php?entityName=equipo_empleado&forwardOk=maestros/equipo-empleado.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar este integrante?');"><i class="fa fa-fw fa-trash"></i></a>

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
                                            <td colspan="3">
                                                <a href="javascript:;" class="btn btn-danger" id="addMoreEquipoEmpleado"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Integrante</a>
                                                <button type="submit" name="saveEquipoEmpleado" id="saveEquipoEmpleado" value="saveEquipoEmpleado" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
