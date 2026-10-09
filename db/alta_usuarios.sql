
INSERT INTO users (username)
SELECT * FROM (SELECT 'alejandro') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'alejandro'
) LIMIT 1;
update users set password = '$2y$10$J2Is8vEjN2H35WbZ7dS9mePmdxJUb5yij6c3P8Kh4SWYuZpgsZv9C' where username = 'alejandro'

INSERT INTO users (username)
SELECT * FROM (SELECT 'lorena') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'lorena'
) LIMIT 1;
update users set password = '$2y$10$Fh84k4XO8o4UPQBM9I2jH.TgQCaFQeTjJbwiDJ8gCIaswV.yGjiwG' where username = 'lorena'

INSERT INTO users (username)
SELECT * FROM (SELECT 'ezequiel') AS tmp
WHERE NOT EXISTS (
    SELECT username FROM users WHERE username = 'ezequiel'
) LIMIT 1;
update users set password = '$2y$10$SanDwKDKGcjUeSvvXQfHReSY8GgenH.0rNJjETKY4ooAx1u4iv6fu' where username = 'ezequiel'
