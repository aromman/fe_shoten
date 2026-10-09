<?PHP
if(isset($_POST["cobrar01"]) && !empty($_POST["cobrar01"])){

    $submittedToken = $_POST['cafeteria_token'] ?? '';
    $sessionToken = $_SESSION['cafeteria_token'] ?? '';
    unset($_SESSION['cafeteria_token']); // se consume siempre, sea válido o no

    $montoPago = $_POST["cobrar01"];
    $pagado = isset($_POST["pagado02"]) ? $_POST["pagado02"] : 0;
    $pagoValido = (float) $pagado >= (float) $montoPago;

    if ($submittedToken !== '' && $sessionToken !== '' && hash_equals($sessionToken, $submittedToken) && $pagoValido) {
        date_default_timezone_set('America/Argentina/Buenos_Aires');

        $id_canal = $_SESSION["user.canal"];
        $vuelto = isset($_POST["vuelto"]) ? $_POST["vuelto"] : 0;
        $detallePago = "Cobro Cafeteria $" . $montoPago . " Pagado $" . $pagado . " Vuelto $" . $vuelto;
        $tipoPago = "C";
        $username = $_SESSION["user.username"];

        $updateTime=date("Y/m/d H:i:sa");
        $fechaPago = date("Y-m-d H:i:s");

        $newPDO = new CuentasMovimientos();
        $newPDO->idCuenta = 1; // efectivo
        $newPDO->fecha = $fechaPago;
        $newPDO->tipoMovimiento = $tipoPago;
        $newPDO->descripcion = $detallePago;
        $newPDO->monto = $montoPago;
        $newPDO->consolidado = false;
        $newPDO->idCausal = 5; // Cafeteria
        $newPDO->idCanal = $id_canal;
        $newPDO->create();
    }

    echo "<meta http-equiv='refresh' content='0'>";
}

$cafeteriaToken = bin2hex(random_bytes(16));
$_SESSION['cafeteria_token'] = $cafeteriaToken;
?>


<div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoModalLabel">Agregar registro</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoMovimiento" action="" method="post">
                    <input type="hidden" name="cafeteria_token" value="<?php echo htmlspecialchars($cafeteriaToken); ?>">

                    <div class="mb-3">
                        <label>Cobrar</label>
                        <input type="number" step=".01" name="cobrar01" id="cobrar01" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Pagado</label>
                        <input type="number" step=".01" name="pagado02" id="pagado02" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Vuelto</label>
                        <input type="number" step=".01" name="vuelto" id="vuelto" class="form-control" readonly>
                    </div>

                    <div id="nuevoMovimientoError" class="alert alert-danger d-none"></div>

                    <div>
                        <input type="submit" class="btn btn-primary" value="Cobrar">
                        <a href="cafeteria.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#formNuevoMovimiento').on('submit', function(e) {
            var cobrar = Number.parseFloat($('#cobrar01').val()) || 0;
            var pagado = Number.parseFloat($('#pagado02').val()) || 0;
            var errorEl = $('#nuevoMovimientoError');

            if (pagado < cobrar) {
                e.preventDefault();
                errorEl.text('El importe pagado no puede ser menor al importe a cobrar.').removeClass('d-none');
                return false;
            }

            errorEl.addClass('d-none');
            $(this).find('input[type="submit"]').prop('disabled', true).val('Procesando...');
        });
    });
</script>