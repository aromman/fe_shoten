<?php 
if(isset($_POST["abrirAccion"]) && !empty($_POST["abrirAccion"])){

    $id_canal = $_SESSION["user.canal"];
    $monto = $_POST["montoApertura"];
    $fechaCaja = $_POST["fechaCaja"];
    $username = $_SESSION["user.username"];
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");

    $turnosPDO = new Turnos();
    $turnosPDO->montoApertura = $monto;
    $turnosPDO->montoCierre = 0;
    $turnosPDO->estado = 'A';
    $turnosPDO->userName = $username;
    $turnosPDO->updateDate = $updateTime;
    $turnosPDO->fecha = $fechaCaja;
    $turnosPDO->idCanal = $id_canal;
    $turnosPDO->create();

    echo "<meta http-equiv='refresh' content='0'>";
}
?>

<div class="modal fade" id="abrirModal" tabindex="-1" aria-labelledby="abrirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Abrir Caja</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fechaCaja" value="<?php echo $fechaCaja;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Efectivo</label>
                        <input type="text" name="montoApertura" value="<?php echo $montoApertura;?>" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="turno-old.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                    <input type="hidden" name="abrirAccion" value="open">
                </form>
            </div>
        </div>
    </div>
</div>
