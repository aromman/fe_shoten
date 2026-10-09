<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="transferModalLabel">Transferencia entre Cuentas</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTransferModal" action="../bff/cuentas/actions.php" method="post">
                    <input type="hidden" name="action" value="transfer">
                    <input type="hidden" name="cuentaOrigen" id="transferCuentaOrigen" value="">
                    <input type="hidden" name="canal" value="<?php echo (int) (isset($_SESSION['user.canal']) ? $_SESSION['user.canal'] : 0); ?>">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" id="transferFecha" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Cuenta Origen</label>
                        <input type="text" id="transferNombreOrigen" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Cuenta Destino</label>
                        <select name="cuentaDestino" id="transferCuentaDestino" class="form-control" required="required">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Importe</label>
                        <input type="number" step="0.01" name="importe" id="transferImporte" class="form-control" required="required">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Grabar</button>
                        <button type="button" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
