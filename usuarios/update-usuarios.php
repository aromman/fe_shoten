<?php
// Include config file
require_once "../app/models/users.php";
require_once "../app/models/roles.php";
 
// Define variables and initialize with empty values
$tipo     = $nombre     = $fechaInicio     = $fechaFin = $estado = "";
$tipo_err = $nombre_err = $fechaInicio_err = $fechaFin_err = $estado_err = "";

$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_usuario = trim($_POST["usuario"]);
    $input_email = trim($_POST["email"]);
    $input_rol = trim($_POST["rol"]);
    
    $updatePDO = new User();
    $updatePDO->id = $id;
    $updatePDO->usuario = $input_usuario;
    $updatePDO->email = $input_email;
    $updatePDO->idRol = $input_rol;
    $updatePDO->update();

    header('location: usuarios.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $usuariosPDO = new User();
        $usuarios = $usuariosPDO->getById($id);

        if (!is_null($usuarios)){
        
            $val = $usuarios;

            $usuario = $val["username"]; 
            $email = $val["email"]; 
            $apellido = $val["apellido"]; 
            $nombre = $val["nombre"]; 
            $idRol = $val["id_rol"]; 
        }

    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
        exit();
    }
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
        <title>Canales</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Usuario</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="usuarios.php">Usuarios</a></li>
                            <li class="breadcrumb-item active">Actualizar Usuario</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Apellido</label>
                                    <input type="text" name="apellido" value="<?php echo $apellido;?>" class="form-control" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Usuario</label>
                                    <input type="text" name="usuario" value="<?php echo $usuario;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" name="email" value="<?php echo $email;?>" class="form-control" required="required">
                                </div>
                                <div  class="form-group">
                                    <label>Rol</label>
                                    <select name="rol" id="rol" class="form-control" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $rolesPDO = new Rol();
                                            $roles = $rolesPDO->getAll('rol');
                                            if (!is_null($roles)){
                                                foreach ($roles as $val) {
                                            ?>
                                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"
                                                <?php if($val['id']==$idRol) echo 'selected="selected"'; ?>
                                            >
                                                <?php echo mb_strtoupper($val['rol'],'UTF-8')?></option>
                                        <?php 
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="usuarios.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

    </body>
</html>