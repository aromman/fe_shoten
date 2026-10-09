<?php
// Include config file
include_once('../config.php');
 
//error_log(PHP_EOL."Comenzando", 3, "my-errors.log");  

// Check existence of id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
    
        // Get URL parameter
    $id =  trim($_GET["id"]);

    // datos cliente
    $sql = "select * from clientes where id = " . $id; 
    $rsCliente = $db->query($sql);    
    //error_log(PHP_EOL.$sql, 3, "my-errors.log");

    // ventas
    $sql = "select 
    v.id id,
    v.fecha fecha,
    c.nombre canal,
    p.titulo producto,
    v.unidades unidades,
    v.precio_unitario precioUnitario,
    v.descuento descuento,
    v.motivo_descuento motivoDescuento,
    v.total total,
    mp.nombre medioPago,
    CONCAT (cli.apellido,' ',cli.nombre)  cliente
    from ventas v, canal c, productos p, clientes cli, medio_pago mp
    where v.id_canal = c.id
    and p.id = v.id_producto
    and cli.id = v.id_cliente 
    and mp.id = v.id_medio_pago
    and cli.id = " . $id ."
    order by 2,3,4"; 
    $rsVentas = $db->query($sql);    
    //error_log(PHP_EOL.$sql, 3, "my-errors.log");
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
        <title>Cliente</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">

        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <!--Producto -->    
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Clientes</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="clientes.php">Clientes</a></li>
                            <li class="breadcrumb-item active">Ver Cliente</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Datos Cliente
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Apellido</th>
                                            <th class="text-center">Nombre</th>
                                            <th class="text-center">Dni</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Descuento</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($rsCliente) > 0) {
                                                while($row = mysqli_fetch_assoc($rsCliente))
                                                {

                                                    $colApellido = $row['apellido'];
                                                    $colNombre = $row['nombre'];
                                                    $colDni = $row['dni'];
                                                    $colEmail = $row['email'];
                                                    $colDescuento = $row['descuento'];
                                            
                                        ?>

                                                <tr>
                                                <td><?php echo $colApellido; ?></td>
                                                    <td><?php echo $colNombre; ?></td>
                                                    <td><?php echo $colDni; ?></td>
                                                    <td><?php echo $colEmail; ?></td>
                                                    <td><?php echo $colDescuento; ?></td>

                                                    <td align="center">
                                                        <a href="update-clientes.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="../delete-entity.php?entityName=clientes&forwardOk=clientes/clientes.php&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar esta cliente?');"><i class="fa fa-fw fa-trash"></i></a>
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
                        </div>
                    
                        <!-- Ventas -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Ventas
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Canal</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Unidades</th>
                                            <th class="text-center">P.U</th>
                                            <th class="text-center">Descuento</th>
                                            <th class="text-center">Motivo Descuento</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Medio Pago</th>
                                            <th class="text-center">Cliente</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            if (mysqli_num_rows($rsVentas) > 0) {

                                                while($row = mysqli_fetch_assoc($rsVentas))
                                                {
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colCanal = $row['canal'];
                                                    $colProducto = $row['producto'];
                                                    $colUnidades = number_format($row['unidades'], 0, ".", ""); 
                                                    $colPrecioUnitario = number_format($row['precioUnitario'], 2, ".", ""); 
                                                    $colDescuento = number_format($row['descuento'], 2, ".", ""); 
                                                    $colMotivoDescuento = $row['motivoDescuento'];
                                                    $colTotal = number_format($row['total'], 2, ".", ""); 
                                                    $colMedioPago = $row['medioPago'];
                                                    $colCliente = $row['cliente'];
                                            
                                        ?>

                                                <tr>
                                                    <td align="center"><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colCanal;?></td>
                                                    <td><?php echo $colProducto;?></td>
                                                    <td align="right"><?php echo $colUnidades; ?></td>
                                                    <td align="right"><?php echo $colPrecioUnitario; ?></td>
                                                    <td align="right"><?php echo $colDescuento; ?></td>
                                                    <td><?php echo $colMotivoDescuento; ?></td>
                                                    <td align="right"><?php echo $colTotal; ?></td>
                                                    <td><?php echo $colMedioPago; ?></td>
                                                    <td><?php echo $colCliente; ?></td>
                                                    <td align="center">
                                                        <a href="../update-ventas.php?id=<?php echo $row['id'];?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
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
                        </div>

                        <form id="formViewClientes" method="post" onSubmit="return false;">
                            <a href="<?php echo $_REQUEST['forwardOk'];?>" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
                        </form>

                    </div>



                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="assets/demo/chart-area-demo.js"></script>
        <script src="assets/demo/chart-bar-demo.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>
        <script src="js/datatables-simple-demo.js"></script>

    </body>
</html>
