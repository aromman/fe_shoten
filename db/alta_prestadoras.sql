
INSERT INTO prestadoras (cuit, razon_social)
    SELECT * FROM (SELECT '1234564654'  ) AS tmp
    WHERE NOT EXISTS (
        SELECT cuit FROM prestadoras WHERE cuit = '1234564654'
    ) LIMIT 1;
    update prestadoras set razon_social = 'Array' where razon_social = '1234564654'
    
INSERT INTO prestadoras (cuit, razon_social)
    SELECT * FROM (SELECT '1321321546'  ) AS tmp
    WHERE NOT EXISTS (
        SELECT cuit FROM prestadoras WHERE cuit = '1321321546'
    ) LIMIT 1;
    update prestadoras set razon_social = 'Array' where razon_social = '1321321546'
    