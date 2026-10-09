<div class="modal fade" id="pagarModal" tabindex="-1" aria-labelledby="abrirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Pagar Reserva</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="fecha" value="<?php echo date("Y-m-d");?>" readOnly>
                    </div>
                    <div class="mb-3">
                        <label htmlFor="hora">Hora</label>
                        <input type="time" name="hora" value="<?php echo date("h:M");?>" readOnly>
                    </div>
                    <div class="mb-3">
                        <label htmlFor="horas">Cantidad Horas</label>
                        <input type="text" name="horas" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label htmlFor="mesas">Cantidad Mesas</label>
                        <input type="text" name="mesas" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label htmlFor="nombre">Nombre</label>
                        <input type="text" name="nombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label htmlFor="monto">Monto</label>
                        <input type="number" step=".01" name="monto" class="form-control">
                    </div>
                    <div class="form-group">
                        <label htmlFor="medioPago">Medio Pago</label>
                        <select name="medioPago" id="medioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                        <option value="">Seleccione</option>
                        <?php
                            $result	=	$db->query("SELECT * FROM medio_pago");
                            while($val  =   $result->fetch_assoc()){
                            ?>
                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" <?php if($val['id']==$medioPago) echo 'selected="selected"'; ?>><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                        <?php }?>
                        </select>
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionPagar" value="pagar"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="reserva.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>