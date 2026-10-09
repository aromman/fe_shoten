<?php
// Include config file
include_once('config.php');
 
$mensaje = "";

// Processing form data when form is submitted
if (isset($_POST['submit'])) {
    
    $mensaje = "Insertando nueva Compra";

    // Get hidden input value
    $input_fecha = trim($_POST["fecha"]);
    $input_producto = trim($_POST["producto"]);
    $input_precio_lista = trim($_POST["precioLista"]);
    $input_precio_compra = trim($_POST["precioCompra"]);
    $input_cantidad = trim($_POST["cantidad"]);

    $sqlInsert = 'INSERT INTO compras (fecha, id_producto, cantidad, precio_lista, precio_costo) values ("'.$input_fecha.'",'.$input_producto.','.$input_cantidad.','.$input_precio_lista.','.$input_precio_compra.')';
    //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
    $db->query($sqlInsert);

    header('location: compras.php');
    exit();
    
} else {
    echo "No veo el submit";
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
        <script defer src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

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
                        <h1 class="mt-4">Nueva Compra</h1>
                        <h2 class="mt-4"><?php echo $mensaje?></h2>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="compras.php">Compras</a></li>
                            <li class="breadcrumb-item active">Actualizar Gasto</li>
                        </ol>
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <?php 
                                        $result	= $db->query("select max(fecha) fecha from compras group by fecha;");
                                        $fechaCompra = date("Y-m-d");
                                        while($val  =   $result->fetch_assoc()){
                                            $fechaCompra = $val['fecha'];
                                        }
                                    ?>
                                    <input type="date" name="fecha" class="form-control" value="<?php echo $fechaCompra;?>" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Producto</label>
                                    <select name="producto" id="producto" class="form-control" data-live-search="true" data-size="10" required="required">
                                        <option value="">Seleccione</option>
                                        <?php
                                            $result	=	$db->query("SELECT * FROM productos ORDER BY titulo ASC");
                                            while($val  =   $result->fetch_assoc()){
                                            ?>
                                            <option 
                                                value="<?php echo $val['id']?>" 
                                                data-subtext="(<?php echo $val['sku']?>)"
                                                <?php if($val['id']==$producto) echo 'selected="selected"'; ?>
                                                >
                                                <?php echo mb_strtoupper($val['titulo'],'UTF-8')?>
                                            </option>
                                            <?php }?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Cantidad</label>
                                    <input type="number" step="0" name="cantidad" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label>Precio Lista</label>
                                    <input type="number" step=".01" name="precioLista" class="form-control" required="required">
                                </div>    
                                <div class="form-group">
                                    <label>Precio Compra</label>
                                    <input type="number" step=".01" name="precioCompra" class="form-control" required="required">
                                </div>    
                                <br> 
                                <input type="submit" name="submit"  class="btn btn-primary" value="Grabar">
                                <a href="compras.php" class="btn btn-secondary ml-2">Cancel</a>
                            </form>
                        </div>
                    </div>
                </main>

                <?php include 'footer.php';?>

            </div>
        </div>

    </body>
</html>