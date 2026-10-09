<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once __DIR__ . '/../app/config/url.php';

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Empleados</title>
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

                $("#addmoreempleados").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'../bff/empleados/actions.php',
                        data:{'action':'addDataRowEmpleados'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveEmpleados').removeAttr('hidden',true);
                        }
                    });
                });

                $("#formEmpleados").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'../bff/empleados/actions.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveEmpleados').addClass("disabled");
                        }
                    });
                });

                var endpoint = <?php echo json_encode(app_url('/bff/empleados/listado.php')); ?>;
                var escapeHtml = function(value) {
                    return String(value == null ? '' : value).replace(/[&<>"']/g, function(c) {
                        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
                    });
                };

                fetch(endpoint, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function(response) { return response.json(); })
                    .then(function(body) {
                        var empleados = body.data.empleados;

                        $('#tbEmpleadosBody').html(empleados.length
                            ? empleados.map(function(e) {
                                return '<tr>'
                                    + '<td>' + escapeHtml(e.apellido) + '</td>'
                                    + '<td>' + escapeHtml(e.nombre) + '</td>'
                                    + '<td align="center">'
                                    + '<a href="update-empleados.php?id=' + e.id + '" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a> '
                                    + '<a href="../delete-entity.php?entityName=empleados&forwardOk=empleados/empleados.php&delId=' + e.id + '" class="text-danger" onClick="return confirm(\'Esta seguro de querer borrar este empleado?\');"><i class="fa fa-fw fa-trash"></i></a>'
                                    + '</td>'
                                    + '</tr>';
                            }).join('')
                            : '<tr><td colspan="3" class="text-center text-muted py-4">0 results</td></tr>');
                    })
                    .catch(function() {
                        $('#tbEmpleadosBody').html('<tr><td colspan="3" class="text-center text-danger py-4">No se pudo cargar el listado de empleados.</td></tr>');
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
                        <h1 class="mt-4">Empleados</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Empleados
                            </div>
                            <div class="card-body">
                                <form id="formEmpleados" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveEmpleadosAddMore">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Apellido</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbEmpleadosBody">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6">
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreempleados"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Empleado</a>
                                                <button type="submit" name="saveEmpleados" id="saveEmpleados" value="saveEmpleados" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
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
