-- si no existe
INSERT INTO users (username)
SELECT * FROM (SELECT 'pepe2') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'pepe2'
) LIMIT 1;

-- si existe update
update users set password = "xfdasfdsafds" where username = 'pepe2'