<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/users.php";
require_once "../app/models/roles.php";
require_once "../app/models/empleados.php";

if(
    (isset($_REQUEST['accionPassword']) and $_REQUEST['accionPassword']=="password" )
){
    // recupero el pedido
    
    $id = $_REQUEST['id'];
    $password = $_REQUEST['password'];

    $password = password_hash($password, PASSWORD_DEFAULT);

    $usuarioPDO = new User();
    $usuarioPDO->id = $id;
    $usuarioPDO->password = $password;
    $usuarioPDO->update();

    header('location: usuarios.php');
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
        <title>Usuarios</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
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
                
                $("#addmoreusuarios").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-usuarios.ajax.php',
                        data:{'action':'addDataRowUsuarios'},
                        success: function(data){
                            $('#tb').append(data);
                            $('.selectpicker').selectpicker('refresh');
                            $('#saveUsuarios').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formUsuarios").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-usuarios.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveUsuarios').addClass("disabled");
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
                        <h1 class="mt-4">Usuarios</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Usuarios
                            </div>
                            <div class="card-body">
                                <form id="formUsuarios" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveUsuariosAddMore">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Id</th>
                                            <th class="text-center">Usuario</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Apellido</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Rol</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 

                                            $usuariosPDO = new User();
                                            $usuarios = $usuariosPDO->getAll("apellido, nombre");
                                            if (!is_null($usuarios)) {

                                                foreach ($usuarios as $row) {    

                                                    $colId = $row['id'];
                                                    $colUsuario = $row['username'];
                                                    $colEmail = $row['email'];
                                                    $colApellido = $row['apellido'];
                                                    $colNombre = $row['nombre'];
                                                    $colRol = $row['rol'];
                                                    $colRolId = $row['id_rol'];
                                            
                                        ?>

                                                <tr>
                                                    <td><?php echo $colId; ?></td>
                                                    <td><?php echo $colUsuario; ?></td>
                                                    <td><?php echo $colEmail; ?></td>
                                                    <td><?php echo $colApellido; ?></td>
                                                    <td><?php echo $colNombre; ?></td>
                                                    <td><?php echo $colRol; ?></td>

                                                    <td align="center">
                                                        <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#passwordModal" data-id="<?php echo $colId;?> " >
                                                            <i class="fa fa-fw fa-key"></i></a>
                                                        </a>

                                                        <a href="update-usuarios.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="delete-entity.php?entityName=canal&forwardOk=usuarios.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar este canal?');"><i class="fa fa-fw fa-trash"></i></a>

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
                                                <a href="javascript:;" class="btn btn-danger" id="addmoreusuarios"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Usuario</a>
                                                <button type="submit" name="saveUsuarios" id="saveUsuarios" value="saveUsuarios" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
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

        <?php include 'passwordModal.php'; ?>
        
        <script>
        $('#passwordModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
        });
        </script> 


    </body>
</html>
