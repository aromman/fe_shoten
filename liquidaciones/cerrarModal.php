<?php 
if(isset($_POST["fecha"]) && !empty($_POST["fecha"])){

    $fecha = $_POST["fecha"];

    $totalCaja = $_POST["totalCaja"];
    $totalMercado = $_POST["totalMercado"];
    $totalDni = $_POST["totalDni"];
    $totalStock = $_POST["totalStock"];
    
    $sql = "insert into liquidacion (fecha, total_caja, total_mercado, total_dni, total_stock) values ('".$fecha."',".$totalCaja.",".$totalMercado.",".$totalDni.",".$totalStock .")";
    error_log(PHP_EOL.$sql, 3, "my-errors.log");  
    $db->query($sql);
    $db->close();
    echo "<meta http-equiv='refresh' content='0'>";
}
?>

<div class="modal fade" id="cerrarModal" tabindex="-1" aria-labelledby="cerrarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="cerrarModalLabel">Cerrar Liquidacion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" value="<?php echo $fechaLiquidacion;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Total Caja</label>
                        <input type="text" name="totalCaja" value="<?php echo $totalCajaFinal;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Total Mercado</label>
                        <input type="text" name="totalMercado" value="<?php echo $totalMercadoFinal;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Total Dni</label>
                        <input type="text" name="totalDni" value="<?php echo $totalDniFinal;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Total Stock</label>
                        <input type="text" name="totalStock" value="<?php echo $totalStockFinal;?>" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="liquidacion.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>