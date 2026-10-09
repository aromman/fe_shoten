<?php 
if(isset($_POST["montoCierre"]) && !empty($_POST["montoCierre"])){

    $id_canal = $_SESSION["user.canal"];
    $montoTotal = $_POST["montoTotal"];
    $montoCierre = $_POST["montoCierre"];
    $fechaCaja = $_POST["fechaCaja"];
    $username = $_SESSION["user.username"];
    $montoApertura = $_POST["montoApertura"];

    $monto10 = $_POST["monto10"];
    $monto20 = $_POST["monto20"];
    $monto50 = $_POST["monto50"];
    $monto100 = $_POST["monto100"];
    $monto200 = $_POST["monto200"];
    $monto500 = $_POST["monto500"];
    $monto1000 = $_POST["monto1000"];
    $monto2000 = $_POST["monto2000"];
    $monto10000 = $_POST["monto10000"];
    $monto20000 = $_POST["monto20000"];
    
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $updateTime=date("Y/m/d H:i:sa");

    if ($rol==0){
        $turnosPDO = new Turnos();
        $turnosPDO->montoCierre = $montoCierre;
        $turnosPDO->userName = $username;
        $turnosPDO->updateDate = $updateTime;
        $turnosPDO->fecha = $fechaCaja;
        $turnosPDO->idCanal = $id_canal;
        $turnosPDO->cerrar();
    }
    
    $turnoParcialPDO = new TurnoParcial();
    $turnoParcialPDO->fecha = $fechaCaja;
    $turnoParcialPDO->montoApertura = $montoApertura;
    $turnoParcialPDO->montoCierre = $montoCierre;
    $turnoParcialPDO->idCanal = $id_canal;
    $turnoParcialPDO->userName = $username;
    $turnoParcialPDO->updateDate = $updateTime;
    $turnoParcialPDO->monto10 = $monto10;
    $turnoParcialPDO->monto20 = $monto20;
    $turnoParcialPDO->monto50 = $monto50;
    $turnoParcialPDO->monto100 = $monto100;
    $turnoParcialPDO->monto200 = $monto200;
    $turnoParcialPDO->monto500 = $monto500;
    $turnoParcialPDO->monto1000 = $monto1000;
    $turnoParcialPDO->monto2000 = $monto2000;
    $turnoParcialPDO->monto10000 = $monto10000;
    $turnoParcialPDO->monto20000 = $monto20000;
    $turnoParcialPDO->create();
    
    echo "<meta http-equiv='refresh' content='0'>";
}
?>

<div class="modal fade" id="cerrarModal" tabindex="-1" aria-labelledby="cerrarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="cerrarModalLabel"><?php echo ($rol==0) ? "Cerrar Caja" : "Cierre Parcial";?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" class="row g-3">

                    <input type="hidden" name="tipo">

                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fechaCaja" value="<?php echo $fechaCaja;?>" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Monto Apertura</label>
                        <input type="number" step="0" id="montoApertura" name="montoApertura" value="<?php echo $montoApertura;?>" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Total Registrado</label>
                        <input type="number" step="0" id="montoTotal" name="montoTotal" value="<?php echo $montoTotal;?>" class="form-control" readOnly>
                    </div>
                    <div class="col-md-6">
                        <label>10</label>
                        <input type="number" step="0" id="monto10" name="monto10" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>20</label>
                        <input type="number" step="0" id="monto20" name="monto20" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>50</label>
                        <input type="number" step="0" id="monto50" name="monto50" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>100</label>
                        <input type="number" step="0" id="monto100" name="monto100" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>200</label>
                        <input type="number" step="0" id="monto200" name="monto200" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>500</label>
                        <input type="number" step="0" id="monto500" name="monto500" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>1000</label>
                        <input type="number" step="0" id="monto1000" name="monto1000" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>2000</label>
                        <input type="number" step="0" id="monto2000" name="monto2000" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>10000</label>
                        <input type="number" step="0" id="monto10000" name="monto10000" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>20000</label>
                        <input type="number" step="0" id="monto20000" name="monto20000" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Efectivo En Caja</label>
                        <input type="text" name="montoCierre" id="montoCierre" value="<?php echo $montoCierre;?>" class="form-control" readOnly>
                    </div>
                    <div class="col-md-6">
                        <label>Falta</label>
                        <input type="text" name="montoResto" id="montoResto" value="0" class="form-control" readOnly>
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
