<div class="modal fade" id="consignacionModal" tabindex="-1" aria-labelledby="consignacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Pasaje a Consignacion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../bff/ofertas/actions.php">
                    <input type="hidden" name="consignacionProductoId">
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="consignacionProductoNombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="text" name="consignacionCantidad" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Lista</label>
                        <input type="number" step=".01" name="consignacionPrecioLista" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Reposicion</label>
                        <input type="number" step=".01" name="consignacionPrecioReposicion" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Consignado</label>
                        <input type="number" step="0" name="consignacionPrecio" class="form-control" required="required">
                    </div>
                    <div class="form-group">
                        <label>Editorial</label>
                        <select name="consignacionEditorial" id="consignacionEditorial" class="form-control" data-live-search="true" data-size="10" required="required">
                        <option value="">Seleccione</option>
                        <?php foreach ($editorialesConsignacion as $val) { ?>
                            <option value="<?php echo h($val['id']); ?>" data-subtext="(<?php echo h($val['id']); ?>)"><?php echo h(mb_strtoupper($val['nombre'], 'UTF-8')); ?></option>
                        <?php } ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Medio Pago</label>
                        <select name="consignacionMedioPago" id="consignacionMedioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                        <option value="">Seleccione</option>
                        <?php foreach ($mediosPagoProveedor as $val) { ?>
                            <option value="<?php echo h($val['id']); ?>" data-subtext="(<?php echo h($val['id']); ?>)"><?php echo h(mb_strtoupper($val['nombre'], 'UTF-8')); ?></option>
                        <?php } ?>
                        </select>
                    </div>

                    <br>
                    <div>
                        <input type="hidden" name="accionOferta" value="consignacion"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="ofertas.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
