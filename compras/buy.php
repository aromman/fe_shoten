<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}
// avanza venta
if(isset($_POST["fecha"]) && !empty($_POST["fecha"])){
    
    $itemArray = array('fecha'=>$_POST["fecha"], 'proveedor'=>$_POST["proveedor"], 'estado'=>$_POST["estado"], 'medioPago'=>$_POST["medioPago"]);
    $_SESSION["oc_header"] = $itemArray;

    header('location: oc.php');
    exit();
    
} 

$fecha = date("Y-m-d");
if (!isset($proveedor)) {
    $proveedor = "";
}
if (!isset($estado)) {
    $estado = "";
}
if (!isset($medioPago)) {
    $medioPago = "";
}
// chequeo si la session esta inicializada.
if(!empty($_SESSION["oc_header"])) {
    if(isset($_SESSION["oc_header"]['fecha']) and $_SESSION["oc_header"]['fecha']!=""){
        $fecha = $_SESSION["oc_header"]['fecha'];
    } else {
        $fecha = date("Y-m-d");
    }
    if(isset($_SESSION["oc_header"]['proveedor']) and $_SESSION["oc_header"]['proveedor']!=""){
        $proveedor = $_SESSION["oc_header"]['proveedor'];
    } else {
        $proveedor = "";
    }
    if(isset($_SESSION["oc_header"]['estado']) and $_SESSION["oc_header"]['estado']!=""){
        $estado = $_SESSION["oc_header"]['estado'];
    } else {
        $estado = "";
    }
    if(isset($_SESSION["oc_header"]['medioPago']) and $_SESSION["oc_header"]['medioPago']!=""){
        $medioPago = $_SESSION["oc_header"]['medioPago'];
    } else {
        $medioPago = "";
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
        <title>Nueva Compra</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" />
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css">

        <script>
            $(document).ready(function() {
                if ($.fn.modal && $.fn.modal.Constructor) {
                    $.fn.modal.Constructor.prototype._enforceFocus = function() {};
                    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
                }
                if ($.fn.selectpicker && $.fn.selectpicker.Constructor) {
                    $.fn.selectpicker.Constructor.DEFAULTS.liveSearchNormalize = true;
                    $.fn.selectpicker.Constructor.DEFAULTS.liveSearchStyle = 'contains';
                    $.fn.selectpicker.Constructor.DEFAULTS.noneResultsText = 'Sin resultados para {0}';
                }

                var escapeHtml = function(value) {
                    return $('<div>').text(value === null || value === undefined ? '' : value).html();
                };

                var cargarOpciones = function(selector, opciones, labelVacio) {
                    var $select = $(selector);
                    $select.find('option').remove();
                    $select.append('<option value="">' + escapeHtml(labelVacio) + '</option>');

                    $.each(opciones || [], function(index, opcion) {
                        $select.append(
                            '<option value="' + escapeHtml(opcion.id) + '" data-subtext="(' + escapeHtml(opcion.id) + ')">' +
                                escapeHtml(opcion.nombre) +
                            '</option>'
                        );
                    });

                    if ($select.hasClass('selectpicker') && $select.selectpicker) {
                        $select.selectpicker('refresh');
                    }
                };

                $(document).on('shown.bs.modal', '.modal', function() {
                    $(this).find('.selectpicker').selectpicker('refresh');
                });

                $(document).on('shown.bs.select', '.selectpicker', function() {
                    var $search = $('.bs-searchbox input:visible').last();
                    setTimeout(function() {
                        $search.trigger('focus');
                    }, 0);
                });

                $(document).on('click keydown keyup keypress input', '.bootstrap-select .bs-searchbox input', function(event) {
                    event.stopPropagation();
                });

                var renderCarrito = function(carrito) {
                    var $tbody = $('#carritoBody');
                    var grandTotal = 0;

                    $tbody.empty();

                    if (!carrito || carrito.length === 0) {
                        $tbody.append('<tr><td colspan="7" class="text-center text-muted">No hay productos agregados</td></tr>');
                        $('#grandTotal').text('0,00');
                        return;
                    }

                    $.each(carrito, function(index, item) {
                        var itemTotal = (parseFloat(item.cantidad || 0) * parseFloat(item.precioCosto || 0));
                        grandTotal += itemTotal;

                        $tbody.append(
                            '<tr>' +
                                '<td>' + escapeHtml(item.sku) + '</td>' +
                                '<td>' + escapeHtml(item.titulo) + '</td>' +
                                '<td>' + escapeHtml(Number(item.precioLista || 0).toFixed(2)) + '</td>' +
                                '<td>' + escapeHtml(item.cantidad) + '</td>' +
                                '<td>' + escapeHtml(Number(item.precioCosto || 0).toFixed(2)) + '</td>' +
                                '<td>' + escapeHtml(itemTotal.toFixed(2)) + '</td>' +
                                '<td>' +
                                    '<a href="action-cart.php?action=remove&id=' + escapeHtml(item.id) + '&forwardOk=buy.php" class="text-danger lead" onclick="return confirm(\'Are you sure want to remove this item?\');"><i class="fas fa-trash-alt"></i></a>' +
                                '</td>' +
                            '</tr>'
                        );
                    });

                    $('#grandTotal').text(grandTotal.toFixed(2));
                };

                var productosIndex = {};
                var productosTable = null;

                var renderProductos = function(productos) {
                    var $tbody = $('#productosBody');
                    productos = productos || [];
                    productosIndex = {};

                    if (productosTable) {
                        productosTable.destroy();
                        productosTable = null;
                    }

                    $tbody.empty();

                    $.each(productos, function(index, item) {
                        productosIndex[String(item.id)] = item;
                    });

                    productosTable = $('#tbProductos').DataTable({
                        data: productos,
                        deferRender: true,
                        autoWidth: false,
                        pageLength: 25,
                        order: [[0, 'asc']],
                        lengthMenu: [[25, 50, 75, -1], [25, 50, 75, "Todos"]],
                        language: {
                            emptyTable: 'No se encontraron productos'
                        },
                        columns: [
                            {
                                data: 'sku',
                                render: function(data, type) {
                                    return type === 'display' ? escapeHtml(data) : (data || '');
                                }
                            },
                            {
                                data: null,
                                render: function(data, type, item) {
                                    var tituloCompleto = item.titulo + ' (' + item.editorial + ')' + (item.nuevo ? '' : ' - Usado');
                                    return type === 'display' ? escapeHtml(tituloCompleto) : tituloCompleto;
                                }
                            },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, item) {
                                    if (type !== 'display') {
                                        return '';
                                    }

                                    return '<a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#addModal" ' +
                                        'data-id="' + escapeHtml(item.id) + '" ' +
                                        'data-sku="' + escapeHtml(item.sku) + '" ' +
                                        'data-producto="' + escapeHtml(item.titulo) + '" ' +
                                        'data-precio="' + escapeHtml(item.precioLista) + '" ' +
                                        'data-costo="' + escapeHtml(item.precioCosto) + '">' +
                                            '<i class="fa fa-fw fa-cart-plus"></i>' +
                                        '</a>';
                                }
                            },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                className: 'text-center',
                                render: function(data, type, item) {
                                    if (type !== 'display') {
                                        return '';
                                    }

                                    return '<a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#addClone" data-id="' + escapeHtml(item.id) + '">' +
                                            '<i class="fa fa-fw fa-copy"></i>' +
                                        '</a>';
                                }
                            }
                        ]
                    });
                };

                var cargarCatalogo = function() {
                    if (productosTable) {
                        productosTable.destroy();
                        productosTable = null;
                    }

                    $('#productosBody').html('<tr><td colspan="4" class="text-center text-muted">Cargando productos...</td></tr>');
                    $.getJSON('../bff/compras/catalogo.php', { all: 1 })
                        .done(function(response) {
                            renderProductos(response.data.productos || []);
                        })
                        .fail(function(xhr, status) {
                            if (status === 'abort') {
                                return;
                            }

                            $('#productosBody').html('<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el catalogo de compras.</td></tr>');
                        });
                };

                $.getJSON('../bff/compras/control.php')
                    .done(function(response) {
                        cargarOpciones('#proveedor', response.data.proveedores, 'Seleccione');
                        cargarOpciones('#medioPago', response.data.mediosPago, 'Seleccione');
                        cargarOpciones('#editorial', response.data.editoriales, 'Seleccione');
                        cargarOpciones('#serie', response.data.series, 'Seleccione');
                        cargarOpciones('#formato', response.data.formatos, 'Seleccione');

                        renderCarrito(response.data.carrito);
                        cargarCatalogo();

                        $('#addClone').on('show.bs.modal', function (event) {
                            var applicant = $(event.relatedTarget);
                            var id = String(applicant.data('id'));
                            var item = productosIndex[id];

                            if (!item) {
                                return;
                            }

                            $('#proId').val(item.id);
                            $('#proSku').val(item.sku + ' (CLONE)');
                            $('#proProducto').val(item.titulo + ' (CLONE)');
                            $('#proTomo').val('');
                        });
                    })
                    .fail(function() {
                        $('#productosBody').html('<tr><td colspan="4" class="text-center text-danger">No se pudo cargar el catálogo de compras.</td></tr>');
                        $('#carritoBody').html('<tr><td colspan="7" class="text-center text-danger">No se pudo cargar el carrito.</td></tr>');
                    });

                $('.selectpicker').selectpicker({
                    liveSearchNormalize: true,
                    liveSearchStyle: 'contains',
                    noneResultsText: 'Sin resultados para {0}'
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

                    <!-- Begin Page Content -->
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Nueva Compra</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="/index.php">Nueva Compra</a></li>
                        </ol>
                        <!-- Content Row -->
                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow mb-4">
                                    <div class="card-body">
                                    <form id="formFactura" method="post" action="../bff/compras/confirmar.php">
                                        <!-- Proveedor --> 
                                        <div id="container-proveedor">
                                            <div class="form-group">
                                                <label>Fecha</label>
                                                <input type="date" id="fecha" name="fecha" value="<?php echo $fecha;?>" class="form-control" required="required">
                                            </div>
                                            <div  class="form-group">
                                                <label>Proveedor</label>
                                                <select name="proveedor" id="proveedor" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                            <div  class="form-group">
                                                <label>Estado</label>
                                                <select name="estado" id="estado" class="form-control" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                    <option value="1" <?php if(1==$estado) echo 'selected="selected"';?> >Pendiente</option>
                                                    <option value="5" <?php if(5==$estado) echo 'selected="selected"';?> >Entregado</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Medio de Pago</label>
                                                <select name="medioPago" id="medioPago" class="form-control selectpicker" data-live-search="true" data-size="10" required="required">
                                                    <option value="">Seleccione</option>
                                                </select>
                                            </div>
                                        </div>
                                        <br>                    
                                        <!-- PRODUCTOS --> 
                                        <div class="table-responsive">
                                        <table class="table table-bordered table-striped text-center">
                                            <thead>
                                                <tr>
                                                    <th>Sku</th>
                                                    <th>Titulo</th>
                                                    <th>Precio Venta</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Compra</th>
                                                    <th>Total</th>
                                                    <th>
                                                        <a href="action-cart.php?action=empty&forwardOk=buy.php" 
                                                           onclick="return confirm('Seguro que quiere limpiar el carrito?');">
                                                           <i class="fas fa-broom"></i>&nbsp;&nbsp;Limpiar Carrito</a>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="carritoBody"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5"><b>Total Compra</b></td>
                                                    <td><b><i class="fas fa-dollar-sign"></i>&nbsp;&nbsp;<span id="grandTotal">0,00</span></b></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        </div>
                                        <BR>        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary"><i class="far fa-credit-card"></i>&nbsp;&nbsp;Comprar</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>                        

                            <div class="col-lg-6 mb-4">

                                <div class="card shadow mb-4">
                                    <div class="card-body">

                                        <form id="formProductos" method="post" onSubmit="return false;">
                                            <div class="table-responsive">    
                                            <table class="table table-bordered table-striped text-center" id="tbProductos">
                                                <thead>
                                                    <tr>
                                                        <th colspan=4>
                                                            <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center justify-content-between">
                                                                <a href="#" 
                                                                   class="btn btn-primary" 
                                                                   data-bs-toggle="modal" 
                                                                   data-bs-target="#nuevoModal" 
                                                                   ><i class="fa fa-fw fa-plus"></i>Nuevo Producto</a>
                                                            </div>
                                                        </th>
                                                    </tr>    
                                                    <tr>
                                                        <th class="text-center">Sku</th>
                                                        <th class="text-center">Titulo</th>
                                                        <th class="text-center">Comprar</th>
                                                        <th class="text-center">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="productosBody">
                                                </tbody>
                                            </table>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>                        
                        </div>
                    </div>        
                </main>
                <?php include '../footer.php';?>
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>

        
        <?php include 'addModal.php'; ?>
        <?php include 'nuevoModal.php'; ?>
        <?php include 'addClone.php'; ?>

    </body>

    <script>
        $('#addModal').on('show.bs.modal', function (event) {
            var applicant = $(event.relatedTarget);
            var id = applicant.data('id');
            var sku = applicant.data('sku');
            var producto = applicant.data('producto');
            var precio = applicant.data('precio');
            var costo = applicant.data('costo');
            var cantidad = 1;
            var modal = $(this);
            modal.find('input[name="id"]').val(id);
            modal.find('input[name="sku"]').val(sku);
            modal.find('input[name="producto"]').val(producto);
            modal.find('input[name="precioVenta"]').val(precio);
            modal.find('input[name="cantidad"]').val(cantidad);
            modal.find('input[name="precioCompra"]').val(costo);
        });


            </script> 
</html>
