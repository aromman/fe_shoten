<?php
// Include config file
include_once('config.php');
 
// Define variables and initialize with empty values
$apellido     = $nombre     = $dni = $email     = "";
$apellido_err = $nombre_err = $dni_err = $email_err = "";

$mensaje = "";

//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    
    $mensaje = "Actualizando";
    //error_log(PHP_EOL.$mensaje, 3, "my-errors.log");  

    // Get hidden input value
    $id = $_POST["id"];
    
    $input_apellido = trim($_POST["apellido"]);
    $input_nombre = trim($_POST["nombre"]);
    $input_dni = trim($_POST["dni"]);
    $input_email = trim($_POST["email"]);
    $input_descuento = trim($_POST["descuento"]);

    $sqlUpdate = 'update clientes set apellido ="'.$input_apellido.'" , nombre = "'.$input_nombre.'"';
    if (!empty($input_dni)){
        $sqlUpdate = $sqlUpdate . ', dni = '.$input_dni;
    }
    if (!empty($input_email)){
        $sqlUpdate = $sqlUpdate . ', email = "'.$input_email.'"';
    }
    if (!empty($input_descuento)){
        $sqlUpdate = $sqlUpdate . ', id_descuento = '.$input_descuento;
    }
    $sqlUpdate = $sqlUpdate . ' WHERE id = '.$id;

    //error_log(PHP_EOL.$sqlUpdate, 3, "my-errors.log");  

    $db->query($sqlUpdate);

    header('location: clientes.php');
    exit();
    
} else{

    $mensaje = "Editando";

    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){

        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        
        $rs = $db->query("select * from clientes where id = " . $id);
        $val  =  $rs->fetch_assoc();

        $apellido = $val["apellido"]; 
        $nombre = $val["nombre"]; 
        $dni = $val["dni"]; 
        $email = $val["email"]; 
        $descuento = $val["id_descuento"]; 
        

    }  else{
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
        <title>Tables - SB Admin</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

    </head>
    <body class="sb-nav-fixed">

        <?php include 'topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include 'sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Actualizar Cliente</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                            <li class="breadcrumb-item active">Actualizar Cliente</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">

                                <div class="form-group">
                                    <label>Apellido</label>
                                    <input type="text" name="apellido" value="<?php echo $apellido;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input type="text" name="nombre" value="<?php echo $nombre;?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Dni</label>
                                    <input type="text" name="dni" value="<?php echo $dni;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" name="email" value="<?php echo $email;?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Descuento</label>
                                    <select name="descuento" id="descuento" class="form-control" data-live-search="true" data-size="10" required="required">
                                    <option value="">Seleccione</option>
                                    <?php
                                        $result	=	$db->query("SELECT * FROM descuentos");
                                        while($val  =   $result->fetch_assoc()){
                                        ?>
                                        <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" <?php if($val['id']==$descuento) echo 'selected="selected"'; ?>><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                                    <?php }?>
                                    </select>
                                </div>
                            
                                <br> 
                                <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                                <input type="submit" class="btn btn-primary" value="Submit">
                                <a href="ventas.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>