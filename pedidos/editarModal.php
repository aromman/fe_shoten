<div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Editar Pedido</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Cliente</label>
                        <input type="text" name="cliente" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Contacto</label>
                        <input type="text" name="contacto" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Producto</label>
                        <input type="text" name="producto" class="form-control">
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionEditar" value="editar"/>
                        <input type="hidden" name="id"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="pedidos.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>