<div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Ajustar Stock</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../bff/ofertas/actions.php">
                    <input type="hidden" name="stockProductoId">
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="stockProductoNombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Stock</label>
                        <input type="number" step=".01" name="stockCantidadTeorico" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Cantidad</label>
                        <input type="number" step="0" name="stockCantidadReal" class="form-control" required="required">
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionOferta" value="stock"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="ofertas.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
