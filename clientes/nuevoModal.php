<?PHP 

if(isset($_POST["apellido"]) && !empty($_POST["apellido"])){

    //error_log(PHP_EOL."Insertando", 3, "my-errors.log");  

    $apellido = $_POST["apellido"];
    $nombre = $_POST["nombre"];
    $dni = $_POST["dni"];
    $email = $_POST["email"];
    $descuento = $_POST["descuento"];
    $soloContacto = $_POST["soloContacto"];

    $fields = "apellido, nombre";
    $values = '"'.$apellido.'", "'.$nombre.'"';
    if (!empty($dni)){
        $fields = $fields . ", dni";
        $values = $values. ", " .$dni;
    }
    if (!empty($email)){
        $fields = $fields . ", email";
        $values = $values . ', "' . $email. '"';
    }

    if (!empty($descuento)){
        $fields = $fields . ", id_descuento";
        $values = $values . ', ' . $descuento;
    }

    $fields = $fields . ", solo_contacto";
    $values = $values . ', ' . $soloContacto;

    $sqlInsert = 'INSERT INTO clientes ('.$fields.') VALUES ('.$values.')';

    //error_log(PHP_EOL.$sqlInsert, 3, "my-errors.log");  
    $db->query($sqlInsert);

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
                        <label>Apellido</label>
                        <input type="text" name="apellido" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required="required">
                    </div>
                    <div class="mb-3">
                        <label>Dni</label>
                        <input type="text" name="dni" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="text" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descuento</label>
                        <select name="descuento" id="descuento" class="form-control" data-live-search="true" data-size="10" required="required">
                        <option value="">Seleccione</option>
                        <?php
                            $result	=	$db->query("SELECT * FROM descuentos");
                            while($val  =   $result->fetch_assoc()){
                            ?>
                            <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)" ><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                        <?php }?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Es solo Contacto?</label>
                        <select name="soloContacto" id="soloContacto" class="form-control" data-live-search="true" data-size="10" required="required">
                            <option value="">Seleccione</option>
                            <option value="1" data-subtext="(SI)" <?php if(1==$soloContacto) echo 'selected="selected"'; ?>>SI</option>
                            <option value="0" data-subtext="(NO)" <?php if(0==$soloContacto) echo 'selected="selected"'; ?>>NO</option>
                        </select>
                    </div>                                

                    <div>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="clientes.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>                                
                </form>
            </div>
        </div>
    </div>
</div>