<?php

namespace App\Integraciones\Seguridad;

final class AppSecretsKey
{
    private $path;

    public function __construct($path)
    {
        $this->path = (string) $path;
    }

    public function obtener()
    {
        if (is_file($this->path)) {
            $config = require $this->path;
            $localKey = is_array($config) && isset($config['appSecretsKey'])
                ? trim((string) $config['appSecretsKey'])
                : '';
            if ($localKey !== '') {
                return $localKey;
            }
        }

        return trim((string) getenv('APP_SECRETS_KEY'));
    }

    private function obtenerLocal()
    {
        if (!is_file($this->path)) {
            return '';
        }

        $config = require $this->path;

        return is_array($config) && isset($config['appSecretsKey'])
            ? trim((string) $config['appSecretsKey'])
            : '';
    }

    public function obtenerOCrear()
    {
        $key = $this->obtenerLocal();
        if ($key !== '') {
            return $key;
        }

        $key = bin2hex(random_bytes(32));
        $contenido = "<?php\n\nreturn array(\n"
            . "    'appSecretsKey' => " . var_export($key, true) . ",\n"
            . ");\n";
        $directorio = dirname($this->path);
        if (!is_dir($directorio) || !is_writable($directorio)) {
            throw new \RuntimeException('No se puede escribir la configuración local de secretos.');
        }
        if (file_put_contents($this->path, $contenido, LOCK_EX) === false) {
            throw new \RuntimeException('No se pudo crear la configuración local de secretos.');
        }

        return $key;
    }
}
