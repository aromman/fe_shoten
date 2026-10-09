<div class="modal fade" id="ofertaModal" tabindex="-1" aria-labelledby="ofertaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Establecer Oferta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="../bff/ofertas/actions.php">
                    <input type="hidden" name="ofertaProductoId">
                    <input type="hidden" name="returnTo" value="ofertas.php">
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="ofertaProductoNombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Lista</label>
                        <input type="number" step=".01" name="ofertaPrecioLista" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Reposicion</label>
                        <input type="number" step=".01" name="ofertaPrecioReposicion" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Oferta Sugerido</label>
                        <input type="number" step=".01" name="ofertaPrecioSugerido" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Oferta</label>
                        <input type="number" step="0" name="ofertaPrecio" class="form-control" required="required">
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionOferta" value="oferta"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="ofertas.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>
