<?php
/**
 * Pone la base al día: esquema, migraciones pendientes y datos base.
 *
 * Solo corre por CLI. En producción lo ejecuta un cron job del hosting,
 * porque el hosting bloquea el tráfico entrante desde GitHub Actions
 * (mismo esquema que rad):
 *
 *   php /home/USUARIO/app.shoten.com.ar/db/migrate.php
 *
 * Pasos, en orden:
 * 1. db/schema.sql: crea las tablas que falten (CREATE TABLE IF NOT EXISTS).
 * 2. db/migrations/*.sql: aplica las que no estén en schema_migrations.
 *    Si la base estaba vacía, schema.sql ya trae esos cambios y se marcan
 *    todas como aplicadas sin correrlas.
 * 3. db/seed_base.sql y db/seed_demo.sql: datos base y de demostración.
 *    Cada seed corre cuando su contenido cambia (se registra su hash en
 *    schema_migrations), no en cada pasada: seed_demo pisa stock, precios
 *    y contraseñas de los registros demo, y el cron los resetearía.
 *
 * Es seguro correrlo muchas veces: si no hay nada pendiente no imprime nada,
 * así el cron no manda mails vacíos. Sale con código 1 si algo falla.
 *
 * Opciones:
 *   --config=RUTA  usa otro archivo de config (mismo formato que db.local.php).
 *   --baseline     en una base existente sin registro de migraciones, marca
 *                  todas como aplicadas en vez de correrlas.
 *
 * Los .sql no pueden usar DELIMITER (no hay procedures ni triggers).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$options = getopt('', array('config:', 'baseline'));

if (isset($options['config'])) {
    $dbConfig = require $options['config'];
} else {
    require_once __DIR__ . '/../app/config/database.php';
    $dbConfig = app_database_config();
}

/**
 * Separa un .sql en sentencias. Respeta los ; dentro de comillas y descarta
 * los comentarios de línea (-- ...).
 */
function splitSqlStatements($sql)
{
    $statements = array();
    $current = '';
    $quote = null;
    $length = strlen($sql);

    for ($i = 0; $i < $length; $i++) {
        $char = $sql[$i];

        if ($quote !== null) {
            $current .= $char;
            if ($char === '\\' && $i + 1 < $length) {
                $current .= $sql[++$i];
            } elseif ($char === $quote) {
                $quote = null;
            }
            continue;
        }

        if ($char === '-' && substr($sql, $i, 3) === '-- ') {
            $end = strpos($sql, "\n", $i);
            $i = $end === false ? $length : $end;
            $current .= "\n";
            continue;
        }

        if ($char === "'" || $char === '"' || $char === '`') {
            $quote = $char;
        } elseif ($char === ';') {
            $statements[] = trim($current);
            $current = '';
            continue;
        }

        $current .= $char;
    }
    $statements[] = trim($current);

    return array_values(array_filter($statements, function ($statement) {
        return $statement !== '';
    }));
}

function runSqlFile(PDO $pdo, $file)
{
    foreach (splitSqlStatements(file_get_contents($file)) as $statement) {
        try {
            $pdo->exec($statement);
        } catch (PDOException $e) {
            throw new RuntimeException(basename($file) . ': ' . $e->getMessage());
        }
    }
}

function countTables(PDO $pdo)
{
    return (int) $pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name <> 'schema_migrations'"
    )->fetchColumn();
}

function markApplied(PDO $pdo, $filename)
{
    $stm = $pdo->prepare('INSERT IGNORE INTO schema_migrations (filename, applied_at) VALUES (:filename, NOW())');
    $stm->execute(array(':filename' => $filename));
}

try {
    $pdo = new PDO(
        $dbConfig['driver'] . ':host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['name'] . ';charset=' . $dbConfig['charset'],
        $dbConfig['user'],
        $dbConfig['pass'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    // Evita que dos corridas del cron se pisen.
    if (!$pdo->query("SELECT GET_LOCK('shoten_migrate', 0)")->fetchColumn()) {
        exit(0);
    }

    $hadTracking = (bool) $pdo->query("SHOW TABLES LIKE 'schema_migrations'")->fetchColumn();
    $wasEmpty = countTables($pdo) === 0;
    $migrations = glob(__DIR__ . '/migrations/*.sql');
    sort($migrations);

    // Antes de tocar nada: si no, la próxima corrida ya encontraría
    // schema_migrations creada y aplicaría todo.
    if (!$hadTracking && !$wasEmpty && $migrations && !isset($options['baseline'])) {
        throw new RuntimeException(
            'La base ya tenía tablas pero no schema_migrations. Revisar qué migraciones '
            . 'tiene aplicadas y correr con --baseline (o aplicarlas a mano).'
        );
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS schema_migrations (
            filename VARCHAR(190) NOT NULL PRIMARY KEY,
            applied_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
    );

    $log = array();
    $tablesBefore = countTables($pdo);
    runSqlFile($pdo, __DIR__ . '/schema.sql');
    $created = countTables($pdo) - $tablesBefore;
    if ($created > 0) {
        $log[] = "schema.sql: $created tablas creadas";
    }

    $applied = $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $pending = array_values(array_filter($migrations, function ($file) use ($applied) {
        return !in_array(basename($file), $applied, true);
    }));

    if ($pending && ($wasEmpty || isset($options['baseline']))) {
        foreach ($pending as $file) {
            markApplied($pdo, basename($file));
        }
        $log[] = 'baseline: ' . count($pending) . ' migraciones marcadas como aplicadas';
    } else {
        foreach ($pending as $file) {
            runSqlFile($pdo, $file);
            markApplied($pdo, basename($file));
            $log[] = 'migración aplicada: ' . basename($file);
        }
    }

    foreach (array('seed_base.sql', 'seed_demo.sql') as $seed) {
        $file = __DIR__ . '/' . $seed;
        $version = $seed . '@' . substr(sha1_file($file), 0, 12);
        if (in_array($version, $applied, true)) {
            continue;
        }
        runSqlFile($pdo, $file);
        markApplied($pdo, $version);
        $log[] = "seed aplicado: $version";
    }

    foreach ($log as $line) {
        echo '[' . date('Y-m-d H:i:s') . "] $line" . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] migrate.php falló: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
