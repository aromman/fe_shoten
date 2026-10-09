<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["user.loggedin"]) || $_SESSION["user.loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../app/models/medioPago.php";

$medioPago = new MedioPago();

?>
<div class="modal fade" id="pagarModal" tabindex="-1" aria-labelledby="abrirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="abrirModalLabel">Pagar Consignacion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="buyAction" value="add">
                    <input type="hidden" name="id">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Monto</label>
                        <input type="number" step=".01" name="monto" class="form-control" readOnly>
                    </div>
                    <div class="form-group">
                        <label>Medio Pago</label>
                        <select name="medioPago" id="medioPago" class="form-control" data-live-search="true" data-size="10" required="required">
                        <option value="">Seleccione</option>
                        <?php
                            $result	=	$medioPago->getAll("nombre ASC");
                            foreach($result as $val){?>
                            ?>
                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" <?php if($val['id']==$medioPago) echo 'selected="selected"'; ?>><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                        <?php }?>
                        </select>
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="accionPagar" value="pagar"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="consignaciones.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>