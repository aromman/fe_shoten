<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoModalLabel">Agregar registro</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="guarda.php" method="post">

                    <div class="mb-3">
                        <label>Apellido</label>
                        <input type="text" name="sku" value="<?php echo $sku;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="titulo" value="<?php echo $titulo;?>" class="form-control" required="required">
                    </div>

                    <div class="mb-3">
                        <label>Dni</label>
                        <input type="number" step="0" name="stock" value="<?php echo $stock;?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="number" step=".01" name="precio" value="<?php echo $precio;?>" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="hidden" name="forwardOk" value="<?php echo $forwardOk; ?>"/>

                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="productos.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>