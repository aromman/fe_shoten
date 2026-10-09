<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "app/models/canal.php";
$canalPDO = new Canal();
$canales = $canalPDO->getAll("activo desc, fechaInicio desc");

$classEstado = array("ACTIVO"=>"table-success","AGENDADO"=>"table-warning","FINALIZADO"=>"table-light");

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Canales</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                
                $("#addmorecanales").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-canales.ajax.php',
                        data:{'action':'addDataRowCanales'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveCanales').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formCanales").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-canales.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveCanales').addClass("disabled");
                        }
                    });
                });
                
            });
        </script>

    </head>
    <body class="sb-nav-fixed">

        <?php include 'topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Canales</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Canales
                            </div>
                            <div class="card-body">
                                <form id="formCanales" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveCanalesAddMore">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Tipo</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Fecha Inicio</th>
                                            <th class="text-center">Fecha Fin</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Activo</th>
                                            <th class="text-center">Es canal de Venta</th>
                                            <th class="text-center">Punto de Venta</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (!is_null($canales)) {
                                                foreach ($canales as $row) {
                                                    // inicializo variables
                                                    $colTipo = $colNombre = $colFechaInicio = $colFechaFin = $colEstado = $colActivo = "";

                                                    $colTipo = $row['tipo'];
                                                    $colNombre = $row['nombre'];

                                                    $colEstado = "ACTIVO";
                                                    $now = new DateTime("now");

                                                    if (!empty($row['fechaInicio'])){
                                                        $colFechaInicio = date('d/m/Y', strtotime($row['fechaInicio']));
                                                        $past = new DateTime($row['fechaInicio']);
                                                        if($past > $now) {
                                                            $colEstado = "AGENDADO";        
                                                        } 
                                                    }
                                                    if (!empty($row['fechaFin'])){
                                                        $colFechaFin = date('d/m/Y', strtotime($row['fechaFin']));
                                                        $past = new DateTime($row['fechaFin']);
                                                        if($past < $now) {
                                                            $colEstado = "FINALIZADO";             
                                                        }
                                                    }
                                                    $colActivo = $row['activo'];
                                                    $colVende = $row['vende'];

                                                    $colPuntoVenta = $row['punto_venta'];

                                                    $className = $classEstado[$colEstado];

                                            
                                        ?>

                                                <tr class="<?php echo $className; ?>">
                                                    <td><?php echo $colTipo; ?></td>
                                                    <td><?php echo $colNombre; ?></td>
                                                    <td align="center"><?php echo $colFechaInicio; ?></td>
                                                    <td align="center"><?php echo $colFechaFin; ?></td>
                                                    <td><?php echo $colEstado; ?></td>
                                                    <td align="center"><input class="form-check-input" type="checkbox" value="" id="activo" <?php if ($colActivo == 1) echo "checked"; ?>  disabled></td>
                                                    <td align="center"><input class="form-check-input" type="checkbox" value="" id="vende" <?php if ($colVende == 1) echo "checked"; ?>  disabled></td>
                                                    <td><?php echo $colPuntoVenta; ?></td>

                                                    <td align="center">
                                                        
                                                        <a href="update-canales.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="delete-entity.php?entityName=canal&forwardOk=canales.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar este canal?');"><i class="fa fa-fw fa-trash"></i></a>

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
                                                <a href="javascript:;" class="btn btn-danger" id="addmorecanales"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Canal</a>
                                                <button type="submit" name="saveCanales" id="saveCanales" value="saveCanales" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
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

                <?php include 'footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

    </body>
</html>
