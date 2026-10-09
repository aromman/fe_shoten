-- aplico monto fijo
update productos set precio_costo = precio - (select IFNULL(e.monto_fijo,0) from editoriales e where e.id = id_editorial);

-- aplico porcentajes sobre precio de costo
update productos set precio_costo = precio_costo * ((100 - (select IFNULL(e.porcentaje,0) from editoriales e where e.id = id_editorial)) / 100);
