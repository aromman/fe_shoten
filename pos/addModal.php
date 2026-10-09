<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="abrirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Agregar Item Venta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="saleAction" value="add">
                    <input type="hidden" name="precioActual">
                    <input type="hidden" name="precioOfertaAplicada">
                    <input type="hidden" name="precioOferta">
                    <input type="hidden" name="costo">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label>Sku</label>
                        <input type="text" name="sku" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="producto" class="form-control" readOnly>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Precio de Lista</label>
                            <input type="number" step=".01" name="precioLista" class="form-control" readOnly>
                        </div>
                        <div class="col-md-6" id="precioOfertaContainer" style="display: none;">
                            <label>Precio Oferta</label>
                            <input type="number" step=".01" name="precioOfertaDisplay" class="form-control" readOnly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="number" step="0" name="cantidad" class="form-control" required="required" value="1">
                    </div>
                    <div class="mb-3">
                        <label>Precio de Venta</label>
                        <input type="number" step=".01" name="precioVenta" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="pos.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
