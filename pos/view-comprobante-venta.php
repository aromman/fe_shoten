<?php

session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../server.php";
include_once ('../neofactura/wsfev1.php');
include_once ('../neofactura/wsaa.php');


if(isset($_GET["id"]) && !empty(trim($_GET["id"]))
    and isset($_REQUEST['forwardOk']) and $_REQUEST['forwardOk']!=""){

		$numero_de_factura = $_GET["id"];

		/**
		 * Numero del punto de venta
		 **/
		$punto_de_venta = $_GET["puntoVenta"];

		/**
		 * Tipo de factura
		 **/
		$tipo_de_comprobante = 11; // 11 = Factura C



		// Tu CUIT
		$CUIT = "27440427109"; // CUIT del emisor
		//$MODO = Wsaa::MODO_HOMOLOGACION;
		$MODO = Wsaa::MODO_PRODUCCION;
		

		$afip = new Wsfev1($CUIT,$MODO);

		// obtener comprobante
		$voucher_info = $afip->consultarComprobante($punto_de_venta, $tipo_de_comprobante,$numero_de_factura);

		if($voucher_info === NULL){
			$voucher_info = 'El comprobante no existe';
		} else {
				$concepto =  $voucher_info->Concepto;
				$tipoDocumento = $voucher_info->DocTipo;
				$fecha_factura = $voucher_info->CbteFch;
				$total_factura = $voucher_info->ImpTotal;
				$cae_factura = $voucher_info->CodAutorizacion;
				$fecha_vto_factura = $voucher_info->FchVto;
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
        <title>Ventas</title>
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

		<style type="text/css">
			*{
				box-sizing: border-box;
				-webkit-user-select: none; /* Chrome, Opera, Safari */
				-moz-user-select: none; /* Firefox 2+ */
				-ms-user-select: none; /* IE 10+ */
				user-select: none; /* Standard syntax */
			}

			.bill-container{
				border-collapse: collapse;
				max-width: 8cm;
				position: absolute;
				left:0;
				right: 0;
				margin: auto;
				border-collapse: collapse;
				font-family: monospace;
				font-size: 12px;
			}

			.text-lg{
				font-size: 20px;
			}

			.text-center{
				text-align: center;
			}
		

			#qrcode {
				width: 75%
			}

			p {
				margin: 2px 0;
			}

			table table {
				width: 100%;
			}

			
			table table tr td:last-child{
				text-align: right;
			}

			.border-top {
				border-top: 1px dashed;
			}

			.padding-b-3 {
				padding-bottom: 3px;
			}

			.padding-t-3 {
				padding-top: 3px;
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
                        <h1 class="mt-4">Ticket Venta</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Tablero</a></li>
                            <li class="breadcrumb-item active">Periodo Actual</li>
                        </ol>
						<div class="table-responsive">
							<table class="bill-container">
								<tr>
									<td class="padding-b-3">
										<p>Razón social: Saiko Manga & Comics</p>
										<p>Direccion: Monteagudo 2927</p>
										<p>C.U.I.T.: <?php echo $tax_id;?></p>
										<p>RESPONSABLE MONOTRIBUTO</p>
										<p>IIBB: <?php echo $tax_id;?></p>
										<p>Inicio de actividad: 01/08/2023</p>
									</td>
								</tr>
								<tr>
									<td class="border-top padding-t-3 padding-b-3">
										<p class="text-center text-lg">FACTURA C</p>
										<p class="text-center">Codigo <?php echo $tipo_de_comprobante;?></p>
										<p>P.V: 0000<?php echo $punto_de_venta;?></p>
										<p>Nro: 0000000<?php echo $numero_de_factura;?></p>
										<p>Fecha: <?php echo $fecha_factura;?></p>
										<p>Concepto: <?php echo $concepto;?></p>
									</td>
								</tr>
								<tr>
									<td class="border-top padding-t-3 padding-b-3">
										<p>A CONSUMIDOR FINAL</p>
									</td>
								</tr>
								<tr>
									<td class="border-top padding-t-3 padding-b-3">
										<div>
											<table>
												<tr>
													<td>TOTAL</td>
													<td><?php echo $total_factura?></td>
												</tr>
											</table>
										</div>
									</td>
								</tr>
								<tr>
									<td class="border-top padding-t-3">
										<p>CAE: <?php echo $cae_factura?></p>
										<p>Vto: <?php echo $fecha_vto_factura?></p>
									</td>
								</tr>
								<tr class="text-center">
									<td>
										<p>
										<?php print_r($voucher_info);?>
										</p>
									</td>
								</tr>
							</table>
                    	</div>
					</div>
                </main>

                <?php include '../footer.php';?>

            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" crossorigin="anonymous"></script>


    </body>
</html>
