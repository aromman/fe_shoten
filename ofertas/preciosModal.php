<div class="modal fade" id="preciosModal" tabindex="-1" aria-labelledby="preciosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="preciosModalLabel">Actualizar Precios y Oferta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../bff/ofertas/actions.php">
                    <input type="hidden" name="preciosProductoId">
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="preciosProductoNombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Compra</label>
                        <input type="number" step=".01" min="0" name="preciosCompra" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Precio Lista</label>
                        <input type="number" step=".01" min="0" name="preciosLista" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Precio Oferta Sugerido</label>
                        <input type="number" step=".01" min="0" name="preciosOfertaSugerido" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Oferta</label>
                        <input type="number" step=".01" min="0" name="preciosOferta" class="form-control" required="required">
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionOferta" value="precios"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="ofertas.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
