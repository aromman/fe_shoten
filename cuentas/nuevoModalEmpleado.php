<div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoModalLabel">Agregar registro</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../bff/cuentas/actions.php" method="post">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="ambitoUso" value="EMPLEADO">
                    <input type="hidden" name="tipo" value="A">
                    <input type="hidden" name="tipoSaldo" value="D">
                    <div class="mb-3">
                        <label>Empleado</label>
                        <select name="idEmpleado" id="idEmpleado" class="form-control" required="required">
                            <option value="">Seleccione</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fecha Inicial</label>
                        <input type="date" name="fecha" value="<?php echo date("Y-m-d");?>" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Saldo Inicial</label>
                        <input type="number" step=".01" name="saldo" class="form-control" required="required">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-primary" value="Grabar">
                        <a href="cuentas-empleados.php" class="btn btn-secondary ml-2">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
