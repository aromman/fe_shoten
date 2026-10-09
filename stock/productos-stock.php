<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/productoStock.php";
require_once "../app/models/producto.php";
require_once('../config.php');

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $idProducto = trim($_GET["id"]);

    $sql = 'select 
    c.id,
    c.fecha,
    p.titulo producto_nombre,
    c.cantidad,
    c.precio_lista,
    c.precio_costo,
    ep.estado estado_nombre
    from compras c, productos p, estado_pedido ep
    where c.id_producto = p.id
    and c.id_estado = ep.id
    and p.id = '.$idProducto .' order by c.fecha desc, p.titulo limit 5;';
    $rsCompras = $db->query($sql);
    error_log(PHP_EOL.$sql, 3, "my-errors.log");

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
        <title>Producto Stock</title>
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
                
                $("#addMoreProductoStock").on("click",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos-stock.ajax.php',
                        data:{'action':'addDataRowProductoStock'},
                        success: function(data){
                            $('#tb').append(data);
                            $('#saveProductoStock').removeAttr('hidden',true);
                        }
                    });
                });
                
                $("#formProductoStock").on("submit",function(){
                    $.ajax({
                        type:'POST',
                        url:'action-form-productos-stock.ajax.php',
                        data:$(this).serialize(),
                        success: function(data){
                            var a   =   data.split('|***|');
                            if(a[1]=="add"){
                                $('#mag').html(a[0]);
                                setTimeout(function(){location.reload();},1500);
                            }
                            $('#saveProductoStock').addClass("disabled");
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
                        <h1 class="mt-4">Producto Stock</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Producto
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tbProducto">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Sku</th>
                                            <th class="text-center">Titulo</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio</th>
                                            <th class="text-center">Costo</th>
                                            <th class="text-center">Editorial</th>
                                            <th class="text-center">Es Nuevo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                            $productokPDO = new Producto();
                                            $row = $productokPDO->getById($idProducto);
                                            if (!is_null($row)){
                                                    $colSku = $row['sku'];
                                                    $colTitulo = $row['titulo'];
                                                    $colStock = $row['stock'];
                                                    $colPrecio = $row['precio'];
                                                    $colCosto = $row['precio_costo'];
                                                    $colEditorial = $row['editorial'];
                                                    $colEsNuevo = ($row['nuevo']==TRUE ? 'Si' : 'Usado');
                                            
                                        ?>
                                                <tr>
                                                    <td><?php echo $colSku; ?></td>
                                                    <td><?php echo $colTitulo; ?></td>
                                                    <td><?php echo $colStock; ?></td>
                                                    <td><?php echo $colPrecio; ?></td>
                                                    <td><?php echo $colCosto; ?></td>
                                                    <td><?php echo $colEditorial; ?></td>
                                                    <td><?php echo $colEsNuevo; ?></td>
                                                </tr>
                                        <?php
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
                        </div>                        

                        <!-- Compras --> 
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Compras 
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="tbCompras">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Id</th>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Precio Lista</th>
                                            <th class="text-center">Precio Costo</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if (mysqli_num_rows($rsCompras) > 0) {
                                            //error_log(PHP_EOL."Hay datos", 3, "my-errors.log");
                                            while($row = mysqli_fetch_assoc($rsCompras)) 
                                            {
                                            
                                                $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                $colProducto = $row['producto_nombre'];
                                                $colCantidad = number_format($row['cantidad'], 0, ".", ""); 
                                                $colPrecioLista = "$ " . number_format($row['precio_lista'], 2, ".", "");
                                                $colPrecioCosto = "$ " . number_format($row['precio_costo'], 2, ".", "");
                                                $colEstado = $row['estado_nombre'];

                                                //error_log(PHP_EOL."Imprimo", 3, "my-errors.log");
            
                                        ?>                                            
                                            <tr>
                                                <td><?php echo $row['id']; ?></td>
                                                <td align="center"><?php echo $colFecha; ?></td>
                                                <td><?php echo $colProducto; ?></td>
                                                <td align="right"><?php echo $colCantidad; ?></td>
                                                <td align="right"><?php echo $colPrecioLista; ?></td>
                                                <td align="right"><?php echo $colPrecioCosto; ?></td>
                                                <td align="right"><?php echo $colEstado; ?></td>
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

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Stock
                            </div>
                            <div class="card-body">
                                <form id="formProductoStock" method="post" onSubmit="return false;">
                                <input type="hidden" name="action" value="saveProductoStockAddMore">
                                <input type="hidden" name="producto" value="<?php echo $idProducto;?>">
                                <table class="table table-striped table-bordered" id="tb">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Editorial</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-center">Ubicacion</th>
                                            <th class="text-center">Fecha</th>
                                            <th class="text-center">Costo</th>
                                            <th class="text-center">Nuevo</th>
                                            <th class="text-center">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 

                                            $productoStockPDO = new ProductoStock();
                                            $stocks = $productoStockPDO->getByProducto($idProducto);
                                            if (!is_null($stocks)){
                                                foreach ($stocks as $row) {
                                                    // inicializo variables
                                                    $colIdEditorial = $row['editorial'];
                                                    $colCantidad  = number_format($row['cantidad'], 0, ".", ""); 
                                                    $colIdUbicacion = $row['id_ubicacion'];
                                                    $colUbicacion = $row['ubicacion'];
                                                    $colFecha = date('d/m/Y', strtotime($row['fecha']));
                                                    $colCosto = number_format($row['costo'], 2, ".", ""); 
                                                    $colEsNuevo = $row['nuevo'];
                                        ?>

                                                <tr>
                                                    <td><?php echo $colIdEditorial; ?></td>
                                                    <td><?php echo $colCantidad; ?></td>
                                                    <td><?php echo $colUbicacion; ?></td>
                                                    <td><?php echo $colFecha; ?></td>
                                                    <td><?php echo $colCosto; ?></td>
                                                    <td align="center"><input class="form-check-input" type="checkbox" value="" id="esNuevo" <?php if ($colEsNuevo == 1) echo "checked"; ?>  disabled></td>
                                                    <td align="center">
                                                        <a href="update-productos-stock.php?id=<?php echo $idProducto;?>&ubicacion=<?php echo $colIdUbicacion;?>" class="text-primary"><i class="fa fa-fw fa-pencil"></i></a>
                                                        <a href="../delete-entity.php?entityName=productos_stock&forwardOk=productos-stock.php?id=<?php echo $idProducto;?>&delId=<?php echo $row['id'];?>" class="text-danger" onClick="return confirm('Esta seguro de querer borrar este Stock?');"><i class="fa fa-fw fa-trash"></i></a>
                                                    </td>                            
                                                </tr>

                                        <?php
                                                }
                                            }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7">
                                                <a href="javascript:;" class="btn btn-danger" id="addMoreProductoStock"><i class="fa fa-fw fa-plus-circle"></i> Nuevo Stock</a>
                                                <button type="submit" name="saveProductoStock" id="saveProductoStock" value="saveProductoStock" class="btn btn-primary" hidden><i class="fa fa-fw fa-save"></i> Grabar</button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                                </form>
                            </div>
                        </div>

                        <form id="formViewProductoStock" method="post" onSubmit="return false;">
                            <a href="existencias.php" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-plus-circle"></i>Volver</a>
                        </form>

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
