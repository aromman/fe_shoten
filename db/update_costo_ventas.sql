update ventas set costo = 0;

-- actualizo costo segun monto fijo
update ventas set costo = precio_unitario - (
select IFNULL(e.monto_fijo, 0) from editoriales e, productos p where e.id = p.id_editorial and ventas.id_producto = p.id );

-- aplico porcentaje
update ventas set costo = costo * ((100 - (select IFNULL(e.porcentaje, 0) from editoriales e, productos p where e.id = p.id_editorial and ventas.id_producto = p.id)) / 100);

select v.precio_unitario, v.costo, e.monto_fijo, e.porcentaje from ventas v, productos p, editoriales e
where v.id_producto = p.id
and p.id_editorial = e.id;
