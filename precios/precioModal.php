<?php 
?>
<div class="modal fade" id="precioModal" tabindex="-1" aria-labelledby="precioModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="precioModalLabel">Calcular Precio</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" class="row g-3">

                    <div class="mb-3">
                        <label>Precio Compra</label>
                        <input type="number" step="0" id="precioCompra" name="precioCompra" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Costo Variable</label>
                        <input type="number" step="0" id="costoVariable" name="costoVariable" value="0" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Costo Fijo 60%</label>
                        <input type="number" step="0" id="costoFijo" name="costoFijo" value="0" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Costo Total</label>
                        <input type="number" step="0" id="costoTotal" name="costoTotal" value="0" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Minimo</label>
                        <input type="number" step="0" id="precioMinimo" name="precioMinimo" value="0" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Medio</label>
                        <input type="number" step="0" id="precioMedio" name="precioMedio" value="0" class="form-control" readOnly>
                    </div>
                    <div class="mb-3">
                        <label>Precio Ideal</label>
                        <input type="number" step="0" id="precioIdeal" name="precioIdeal" value="0" class="form-control" readOnly>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>