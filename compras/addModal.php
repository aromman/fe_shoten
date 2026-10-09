<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="abrirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Agregar Item Compra</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../bff/compras/actions.php" method="post">
                    <input type="hidden" name="buyAction" value="add">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label>Sku</label>
                        <input type="text" name="sku" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="producto" class="form-control" readOnly>
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
                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="buy.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
