<?php include_once('config.php');
if(isset($_REQUEST['action']) and $_REQUEST['action']=="addDataRowCompras"){
?>
    <tr>
        <td align="center"><input type="date" name="fecha[]" value="<?php echo date("Y-m-d");?>"></td>
        <td>
            <select name="producto[]" id="producto" class="form-control selectpicker" data-live-search="true" required="required">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("SELECT * FROM productos order by titulo");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['titulo'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td><input type="number" step="0" name="cantidad[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="precioLista[]" class="form-control" required="required"></td>
        <td><input type="number" step=".01" name="precioCompra[]" class="form-control"></td>
        <td>
            <select name="orden[]" id="orden" class="form-control selectpicker" data-live-search="true">
                <option value="">Seleccione</option>
                <?php
                    $result	=	$db->query("select 
                    oc.id,
                    concat(oc.fecha,' ', p.nombre )  nombre
                    from orden_compra oc, proveedores p
                    where oc.id_proveedor = p.id
                    order by oc.fecha, p.nombre;
                    ");
                    while($val  =   $result->fetch_assoc()){
                    ?>
                    <option value="<?php echo $val['id']?>" data-subtext="(<?php echo $val['id']?>)"><?php echo mb_strtoupper($val['nombre'],'UTF-8')?></option>
                <?php }?>
            </select>
        </td>
        <td><input disabled type="text" name="estado[]" class="form-control"></td> 
        <td align="center">
            <a class="text-danger" onClick="if(confirm('Esta seguro de querer borrar esta compra?')){$(this).closest('tr').remove();}"><i class="fa fa-fw fa-trash"></i></a>
        </td>
    </tr>
    <?php
    echo '|***|addmore';
}
 
//Submit data or extra rows
if(isset($_REQUEST['action']) and $_REQUEST['action']=="saveComprasAddMore"){
    foreach($fecha as $key=>$un){
        $fields = "fecha, id_producto, cantidad, precio_lista, precio_costo";
        $values = '"'.$fecha[$key].'",'.$producto[$key].','.$cantidad[$key].','.$precioLista[$key].','.$precioCompra[$key];
        if (!empty($orden[$key])){
            $fields = $fields . ", id_orden_compra";
            $values = $values . ','.$orden[$key];
        }
        $sqlInsert = 'INSERT INTO compras ('.$fields.') values ('.$values.')';
        $db->query($sqlInsert);
    }
    echo '<div class="alert alert-success"><i class="fa fa-fw fa-thumbs-up"></i> Record added successfully!</div>|***|add';
} 