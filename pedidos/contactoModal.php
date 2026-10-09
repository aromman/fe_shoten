<?php 
    date_default_timezone_set('America/Argentina/Buenos_Aires');
?>
<div class="modal fade" id="contactoModal" tabindex="-1" aria-labelledby="contactoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Contactar Cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" class="form-control" value="<?php echo date("Y-m-d");?>">
                        
                    </div>
                    <div class="mb-3">
                        <label>Observacion</label>
                        <input type="text" name="observacion" class="form-control">
                    </div>

                    <br>
                    <div>
                        <input type="hidden" name="accionContacto" value="contacto"/>
                        <input type="hidden" name="id"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="pedidos.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>