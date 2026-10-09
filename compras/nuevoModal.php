<?php
$admiteDescuento = isset($admiteDescuento) ? $admiteDescuento : '';
$esNuevo = isset($esNuevo) ? $esNuevo : '';
?>

<div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoModalLabel">Nuevo Producto</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../bff/compras/actions.php" method="post">
                    <input type="hidden" name="buyNewAction" value="new">
                    <div class="mb-3">
                        <label>Sku</label>
                        <input type="text" name="sku" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="titulo" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="number" step="0" name="cantidad" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Precio de Venta</label>
                        <input type="number" step=".01" name="precioVenta" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Precio de Compra</label>
                        <input type="number" step=".01" name="precioCompra" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Editorial</label>
                        <select name="editorial" id="editorial" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Serie</label>
                        <select name="serie" id="serie" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tomo</label>
                        <input type="number" step="0" name="tomo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Formato</label>
                        <select name="formato" id="formato" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Admite Descuento</label>
                        <select name="admiteDescuento" id="admiteDescuento" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <option value="1" data-subtext="(TRUE)" <?php if(1==$admiteDescuento) echo 'selected="selected"'; ?>>Si</option>
                            <option value="0" data-subtext="(FALSE)" <?php if(0==$admiteDescuento) echo 'selected="selected"'; ?>>No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Es Nuevo</label>
                        <select name="esNuevo" id="esNuevo" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <option value="1" data-subtext="(TRUE)" <?php if(1==$esNuevo) echo 'selected="selected"'; ?>>Si</option>
                            <option value="0" data-subtext="(FALSE)" <?php if(0==$esNuevo) echo 'selected="selected"'; ?>>No</option>
                        </select>
                    </div>

                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="buy.php" data-dismiss="modal" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
