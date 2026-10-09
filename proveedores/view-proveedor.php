<?php

session_start();

if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../app/models/proveedores.php";

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function mostrarValor($value)
{
    if ($value === null || $value === '') {
        return '';
    }

    return h($value);
}

function claseStockProducto($stock, $stockMinimo)
{
    $sinStock = $stock === null || $stock === '';
    $sinStockMinimo = $stockMinimo === null || $stockMinimo === '';

    if ($sinStock || $sinStockMinimo) {
        return 'table-warning';
    }

    if (is_numeric($stock) && is_numeric($stockMinimo) && (float) $stock < (float) $stockMinimo) {
        return 'table-danger';
    }

    return '';
}

$proveedorData = null;
$rsProductos = array();
$productosFudo = array('items' => array(), 'error' => '');

if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){
    $id = trim($_GET["id"]);
    $proveedorPDO = new Proveedor();
    $proveedorData = $proveedorPDO->getByIdParaCalendario($id);

    if (is_array($proveedorData)) {
        $origen = isset($proveedorData['origen']) ? $proveedorData['origen'] : 'local';
        if ($origen === 'fudo') {
            // Sin integracion con Fudo: no hay fuente para listar productos/ingredientes de
            // proveedores origen='fudo'. $productosFudo queda vacio (ver render mas abajo).
        } else {
            $rsProductos = $proveedorPDO->getAllProducts($id);
            if (!is_array($rsProductos)) {
                $rsProductos = array();
            }
        }
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
        <title>Proveedores</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">
        <script>
            $(document).ready(function(e) {
                $('#tbProductos').dataTable({
                    "order": [[0, 'asc']],
                    "lengthMenu": [[10, 20, 50, 100, 150, -1], [10, 20, 50, 100, 150, "All"]]
                });
            });
        </script>
        <style>
            .badge-origen-local {
                color: #0f4c81;
                background-color: #dceeff;
                border: 1px solid #9cc7ed;
            }

            .badge-origen-fudo {
                color: #7a4b00;
                background-color: #ffe7b3;
                border: 1px solid #f0bd58;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <?php include '../topBar.php';?>

        <div id="layoutSidenav">
            <?php include '../sidebar.php';?>

            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Proveedor</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="proveedores.php">Proveedores</a></li>
                            <li class="breadcrumb-item active">Ver Proveedor</li>
                        </ol>

                        <?php if (!is_array($proveedorData)) { ?>
                            <div class="alert alert-warning">No se encontro el proveedor solicitado.</div>
                        <?php } else {
                            $origenProveedor = isset($proveedorData['origen']) ? $proveedorData['origen'] : 'local';
                        ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Datos Proveedor
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="tb">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Nombre</th>
                                                    <th class="text-center">Origen</th>
                                                    <th class="text-center">Email</th>
                                                    <th class="text-center">Telefono</th>
                                                    <th class="text-center">Fiscal</th>
                                                    <th class="text-center">Cantidad Minima</th>
                                                    <th class="text-center">Monto Minimo</th>
                                                    <th class="text-center">Activo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo h($proveedorData['nombre']); ?></td>
                                                    <td align="center"><span class="badge badge-origen-<?php echo h($origenProveedor); ?>"><?php echo h($origenProveedor); ?></span></td>
                                                    <td><?php echo h(isset($proveedorData['email']) ? $proveedorData['email'] : ''); ?></td>
                                                    <td><?php echo h(isset($proveedorData['telefono']) ? $proveedorData['telefono'] : ''); ?></td>
                                                    <td><?php echo h(isset($proveedorData['fiscal_number']) ? $proveedorData['fiscal_number'] : ''); ?></td>
                                                    <td><?php echo number_format(isset($proveedorData['cantidad_minima']) ? (float) $proveedorData['cantidad_minima'] : 0, 0, ',', '.'); ?></td>
                                                    <td><?php echo number_format(isset($proveedorData['monto_minimo']) ? (float) $proveedorData['monto_minimo'] : 0, 2, ',', '.'); ?></td>
                                                    <td align="center"><input class="form-check-input" type="checkbox" <?php if ((int) $proveedorData['activo'] === 1) echo "checked"; ?> disabled></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-table me-1"></i>
                                    Productos e ingredientes
                                </div>
                                <div class="card-body">
                                    <?php if ($origenProveedor === 'fudo' && $productosFudo['error'] !== '') { ?>
                                        <div class="alert alert-warning"><?php echo h($productosFudo['error']); ?></div>
                                    <?php } ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered" id="tbProductos">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Nombre</th>
                                                    <th class="text-center">Tipo</th>
                                                    <th class="text-center">Unidad</th>
                                                    <th class="text-center">Precio</th>
                                                    <th class="text-center">Precio Costo</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Stock Minimo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($origenProveedor === 'fudo') { ?>
                                                    <?php foreach ($productosFudo['items'] as $item) { ?>
                                                        <tr class="<?php echo claseStockProducto($item['stock'], $item['stockMinimo']); ?>">
                                                            <td><?php echo h($item['nombre']); ?></td>
                                                            <td><?php echo h($item['entidad']); ?></td>
                                                            <td><?php echo mostrarValor($item['unidad']); ?></td>
                                                            <td><?php echo mostrarValor($item['precio']); ?></td>
                                                            <td><?php echo mostrarValor($item['precioCosto']); ?></td>
                                                            <td><?php echo mostrarValor($item['stock']); ?></td>
                                                            <td><?php echo mostrarValor($item['stockMinimo']); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <?php foreach ($rsProductos as $row) { ?>
                                                        <tr class="<?php echo claseStockProducto(isset($row['stock']) ? $row['stock'] : '', ''); ?>">
                                                            <td><?php echo h($row['titulo']); ?></td>
                                                            <td>Producto</td>
                                                            <td>unit</td>
                                                            <td><?php echo mostrarValor($row['precio']); ?></td>
                                                            <td><?php echo mostrarValor($row['precio_costo']); ?></td>
                                                            <td><?php echo mostrarValor($row['stock']); ?></td>
                                                            <td></td>
                                                        </tr>
                                                    <?php } ?>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <form id="formViewClientes" method="post" onSubmit="return false;">
                            <a href="<?php echo h($_REQUEST['forwardOk']);?>" class="btn btn-secondary ml-2"><i class="fa fa-fw fa-arrow-left"></i> Volver</a>
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
