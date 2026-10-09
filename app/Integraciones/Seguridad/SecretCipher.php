<?php

namespace App\Integraciones\Seguridad;

final class SecretCipher
{
    private const CIPHER = 'aes-256-gcm';
    private const AAD = 'saikono_roi:integraciones:v1';

    private $key;

    public function __construct($masterKey)
    {
        $masterKey = trim((string) $masterKey);
        if (strlen($masterKey) < 32) {
            throw new \RuntimeException('La clave maestra de secretos debe tener al menos 32 caracteres.');
        }

        $this->key = hash('sha256', $masterKey, true);
    }

    public function cifrar(array $datos)
    {
        $iv = random_bytes(12);
        $tag = '';
        $textoPlano = json_encode($datos, JSON_UNESCAPED_SLASHES);
        $cifrado = openssl_encrypt(
            $textoPlano,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::AAD,
            16
        );

        if ($cifrado === false) {
            throw new \RuntimeException('No se pudieron cifrar las credenciales.');
        }

        return json_encode(array(
            'version' => 1,
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'data' => base64_encode($cifrado),
        ), JSON_UNESCAPED_SLASHES);
    }

    public function descifrar($payload)
    {
        $contenido = json_decode((string) $payload, true);
        if (
            !is_array($contenido)
            || empty($contenido['iv'])
            || empty($contenido['tag'])
            || empty($contenido['data'])
        ) {
            throw new \RuntimeException('El secreto almacenado tiene un formato inválido.');
        }

        $data = base64_decode($contenido['data'], true);
        $iv = base64_decode($contenido['iv'], true);
        $tag = base64_decode($contenido['tag'], true);
        if ($data === false || $iv === false || $tag === false) {
            throw new \RuntimeException('El secreto almacenado tiene una codificación inválida.');
        }

        $textoPlano = openssl_decrypt(
            $data,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            self::AAD
        );

        if ($textoPlano === false) {
            throw new \RuntimeException('No se pudieron descifrar las credenciales.');
        }

        $datos = json_decode($textoPlano, true);
        if (!is_array($datos)) {
            throw new \RuntimeException('Las credenciales descifradas no son válidas.');
        }

        return $datos;
    }
}
