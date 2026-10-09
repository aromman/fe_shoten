<?PHP 

require_once "../app/models/producto.php";    

if(isset($_POST["sku"]) && !empty($_POST["sku"])){

    $sku = $_POST["sku"];
    $titulo = $_POST["titulo"];
    $editorial = $_POST["editorial"];
    $stock = empty($_POST["stock"]) ? 0 : $_POST["stock"];
    $precio = empty($_POST["precio"]) ? 0 : $_POST["precio"];
    $precioCosto = empty($_POST["precioCosto"]) ? 0 : $_POST["precioCosto"];
    $serie = empty($_POST["serie"]) ? 1 : $_POST["serie"];
    $tomo = empty($_POST["tomo"]) ? 0 : $_POST["tomo"];
    $formato = empty($_POST["formato"]) ? 1 : $_POST["formato"];
    $esNuevo = $_POST["esNuevo"];

    $itemNewPDO = new Producto();
    $itemNewPDO->setSku ($sku);
    $itemNewPDO->setTitulo ($titulo);
    $itemNewPDO->setIdEditorial($editorial);
    $itemNewPDO->setStock ($stock);
    $itemNewPDO->setPrecio($precio);
    $itemNewPDO->precioCosto = $precioCosto;
    $itemNewPDO->setIdSerie ($serie);
    $itemNewPDO->tomo = $tomo;
    $itemNewPDO->setIdFormato ($formato);
    $itemNewPDO->nuevo = $esNuevo;
    $itemNewPDO->create();

    unset($_SESSION["productosRows"]);

    echo "<meta http-equiv='refresh' content='0'>";
}


?>


<div class="modal fade" id="nuevoModal" tabindex="-1" aria-labelledby="nuevoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="nuevoModalLabel">Agregar registro</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">

                    <div class="mb-3">
                        <label>Sku</label>
                        <input type="text" name="sku" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Titulo</label>
                        <input type="text" name="titulo" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Stock</label>
                        <input type="number" step="0" name="stock" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Precio</label>
                        <input type="number" step=".01" name="precio" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Precio Costo</label>
                        <input type="number" step=".01" name="precioCosto" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Editorial</label>
                        <select name="editorial" id="editorial" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <?php

                                $editorialesPDO = new Editorial();
                                $editoriales = $editorialesPDO->getAll("nombre ASC");
                                foreach ($editoriales as $val) {
                                ?>
                                <option 
                                    value="<?php echo $val['id']?>" 
                                    data-subtext="(<?php echo $val['id']?>)"
                                    >
                                    <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                </option>
                                <?php 
                                    }
                                    unset($editorialesPDO);
                                ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Serie</label>
                        <select name="serie" id="serie" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <?php
                                $productoSeriePDO = new ProductoSerie();
                                $series = $productoSeriePDO->getAll("nombre ASC");
                                foreach ($series as $val) {
                                ?>
                                <option 
                                    value="<?php echo $val['id']?>" 
                                    data-subtext="(<?php echo $val['id']?>)"
                                    >
                                    <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                </option>
                                <?php 
                                    }
                                    unset($productoSeriePDO);
                                ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tomo</label>
                        <input type="number" step="0" name="tomo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Formato</label>
                        <select name="formato" id="formato" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <?php
                                $productoFormatoPDO = new ProductoFormato();
                                $formatos = $productoFormatoPDO->getAll("nombre ASC");
                                foreach ($formatos as $val) {
                                ?>
                                <option 
                                    value="<?php echo $val['id']?>" 
                                    data-subtext="(<?php echo $val['id']?>)"
                                    >
                                    <?php echo mb_strtoupper($val['nombre'],'UTF-8')?>
                                </option>
                                <?php }
                                    unset($productoFormatoPDO);
                                ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Es Nuevo</label>
                        <select name="esNuevo" id="esNuevo" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <option value="1" data-subtext="(TRUE)" <?php if(1==$esNuevo) echo 'selected="selected"'; ?>>Si</option>
                            <option value="0" data-subtext="(FALSE)" <?php if(0==$esNuevo) echo 'selected="selected"'; ?>>No</option>
                        </select>
                    </div>

                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="productos.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>