<?php 
if(isset($_POST["montoPago"]) && !empty($_POST["montoPago"])){

    $id_canal = $_SESSION["user.canal"];
    $montoPago = $_POST["montoPago"];
    $fechaPago = $_POST["fechaPago"];
    $detallePago = $_POST["detallePago"];
    $tipoPago = $_POST["tipoPago"];
    $username = $_SESSION["user.username"];
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");
    $newPDO = new CuentasMovimientos();
    $newPDO->idCuenta = 1; // efectivo
    $newPDO->fecha = $fechaPago;
    $newPDO->tipoMovimiento = $tipoPago;
    $newPDO->descripcion = $detallePago;
    $newPDO->monto = $montoPago;
    $newPDO->consolidado = false;
    $newPDO->idCausal = 3;
    $newPDO->idCanal = $id_canal;
    $newPDO->create();


    echo "<meta http-equiv='refresh' content='0'>";
}
?>

<div class="modal fade" id="pagarModal" tabindex="-1" aria-labelledby="pagarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="pagarModalLabel">Nuevo Movimiento de Caja</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" class="row g-3">

                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fechaPago" value="<?php echo $fechaHoy;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Tipo</label>
                        <select name="tipoPago" id="tipoPago" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <option value="C" data-subtext="(C)">Deposito</option>
                            <option value="D" data-subtext="(D)">Pago</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Detalle</label>
                        <input type="text" name="detallePago" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="number" step=".01" name="montoPago" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Confirmar">
                        <a href="turno-old.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
