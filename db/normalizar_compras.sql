insert into orden_compra (fecha,id_proveedor,cantidad, monto, fecha_entrega, id_estado_pedido)
select
c.fecha,
e.id_proveedor,
sum(c.cantidad),
sum(c.precio_lista * c.cantidad),
c.fecha,
max(c.id_estado)
from compras c, productos p, editoriales e
where c.id_producto = p.id
and p.id_editorial = e.id
group by c.fecha, e.id_proveedor; 


update compras set id_orden_compra = (
select oc.id 
from orden_compra oc, editoriales ed, productos prd
where oc.id_proveedor = ed.id_proveedor
and prd.id_editorial = ed.id
and prd.id = compras.id_producto
and oc.fecha = compras.fecha
);
