<div class="modal fade" id="reprogramarModal" tabindex="-1" aria-labelledby="reprogramarModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="reprogramarModalLabel">Reprogramar Reserva</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" value="<?php echo date("Y-m-d");?>">
                    </div>
                    <div class="mb-3">
                        <label htmlFor="hora">Hora</label>
                        <input type="time" name="hora" value="<?php echo date("h:M");?>">
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionEditar" value="reprogramar"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="reserva.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>