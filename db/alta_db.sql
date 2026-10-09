
INSERT INTO users (username)
SELECT * FROM (SELECT 'aromman') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'aromman'
) LIMIT 1;
update users set password = '$2y$10$hltuciRWNUGSa7YwJviXtuFMAksGnHKy8PQk8x0wFseaxV/NbHsne' where username = 'aromman'
;
INSERT INTO users (username)
SELECT * FROM (SELECT 'lorena') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'lorena'
) LIMIT 1;
update users set password = '$2y$10$WkMZIftoDvdx./L7VepgW.X4jDyZ4RgDj8MZhTekOwiuf.CJON2IC' where username = 'lorena'
;
INSERT INTO users (username)
SELECT * FROM (SELECT 'ezequiel') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'ezequiel'
) LIMIT 1;
update users set password = '$2y$10$N.Ig88lIW.yfRV7OCAbmzOyJ9LvM/aCUd6x0.4I9DdMd840y19JO2' where username = 'ezequiel'
;
INSERT INTO prestadoras (cuit)
    SELECT * FROM (SELECT '1234564654'  ) AS tmp
    WHERE NOT EXISTS (
        SELECT cuit FROM prestadoras WHERE cuit = '1234564654'
    ) LIMIT 1;
    update prestadoras set razon_social = 'Banco' where cuit = '1234564654'
    ;
INSERT INTO prestadoras (cuit)
    SELECT * FROM (SELECT '1321321546'  ) AS tmp
    WHERE NOT EXISTS (
        SELECT cuit FROM prestadoras WHERE cuit = '1321321546'
    ) LIMIT 1;
    update prestadoras set razon_social = 'Obra Social' where cuit = '1321321546'
    ;
INSERT INTO profesionales (dni)
    SELECT * FROM (SELECT '41785485') AS tmp
    WHERE NOT EXISTS (
        SELECT dni FROM profesionales WHERE dni = '41785485'
    ) LIMIT 1;
    update profesionales set nombre = 'Ezequiel',apellido = 'Romero Mansilla' where dni = '41785485'
    ;
INSERT INTO profesionales (dni)
    SELECT * FROM (SELECT '47865989') AS tmp
    WHERE NOT EXISTS (
        SELECT dni FROM profesionales WHERE dni = '47865989'
    ) LIMIT 1;
    update profesionales set nombre = 'Jon',apellido = 'Doe' where dni = '47865989'
    ;