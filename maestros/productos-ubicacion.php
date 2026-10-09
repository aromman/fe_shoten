<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../server.php";

$sql = "select 
pu.*,
c.nombre canal,
put01.nombre nivel01,
put02.nombre nivel02
from 
productos_ubicacion pu, 
canal c, 
productos_ubicacion_tipo put01, 
productos_ubicacion_tipo put02
where 
pu.id_canal = c.id
and pu.id_nivel_tipo_01 = put01.id
and pu.id_nivel_tipo_02 = put02.id
order by pu.id_canal;"; 

$result = mysqli_query($link,$sql);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Productos Ubicacion</title>
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
                
                $("#addMoreProductosUbicacion").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos-ubicacion.ajax.php',
                        data:{'action':'addDataRowProductosUbicacion'},
                        success: function(data){
                            $('#tb').append(data);
                            $('#saveProductosUbicacion').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formProductosUbicacion").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos-ubicacion.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveProductosUbicacion').addClass("disabled");
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
                        <h1 class="mt-4">Productos Ubicacion</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Productos Ubicacion
                            </div>
                            <div class="card-body">
                                <form id="formProductosUbicacion" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveProductosUbicacionAddMore">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Tipo Nivel 1</th>
                                            <th class="text-center">Codigo Nivel 1</th>
                                            <th class="text-center">Tipo Nivel 2</th>
                                            <th class="text-center">Codigo Nivel 2</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($result) > 0) {

                                                while($row = mysqli_fetch_assoc($result)) {


                                                    // inicializo variables
                                                    $colCanal = $row['canal'];
                                                    $colTipoNivel01 = $row['nivel01'];
                                                    $colCodigoNivel01 = $row['nivel_codigo_01'];
                                                    $colTipoNivel02 = $row['nivel02'];
                                                    $colCodigoNivel02 = $row['nivel_codigo_02'];
                                        ?>

                                                <tr>
                                                    <td><?php echo $colCanal; ?></td>
                                                    <td><?php echo $colTipoNivel01; ?></td>
                                                    <td><?php echo $colCodigoNivel01; ?></td>
                                                    <td><?php echo $colTipoNivel02; ?></td>
                                                    <td><?php echo $colCodigoNivel02; ?></td>

                                                    <td align="center">
                                                        <a href="update-productos-ubicacion.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="../delete-entity.php?entityName=productos_ubicacion&forwardOk=maestros/productos-ubicacion.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta ubicacion?');"><i class="fa fa-fw fa-trash"></i></a>

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
                                            <td colspan="6">
                                                <a href="javascript:;" class="btn btn-danger" id="addMoreProductosUbicacion"><i class="fa fa-fw fa-plus-circle"></i> Nueva Ubicacion</a>
                                                <button type="submit" name="saveProductosUbicacion" id="saveProductosUbicacion" value="saveProductosUbicacion" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
