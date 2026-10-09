<?php

namespace App\Integraciones\Fudo;

final class FudoConfig
{
    private $apiKey;
    private $apiSecret;
    private $apiBaseUrl;
    private $authUrl;
    private $timeoutSeconds;
    private $caBundlePath;

    public function __construct($apiKey, $apiSecret, $apiBaseUrl, $authUrl, $timeoutSeconds, $caBundlePath = '')
    {
        $this->apiKey = trim((string) $apiKey);
        $this->apiSecret = trim((string) $apiSecret);
        $this->apiBaseUrl = rtrim((string) $apiBaseUrl, '/');
        $this->authUrl = (string) $authUrl;
        $this->timeoutSeconds = max(1, (int) $timeoutSeconds);
        $this->caBundlePath = trim((string) $caBundlePath);
    }

    public static function fromEnvironment()
    {
        return new self(
            getenv('FUDO_API_KEY'),
            getenv('FUDO_API_SECRET'),
            getenv('FUDO_API_BASE_URL') ?: 'https://api.fu.do/v1alpha1',
            getenv('FUDO_AUTH_URL') ?: 'https://auth.fu.do/api',
            getenv('FUDO_TIMEOUT_SECONDS') ?: 15,
            getenv('FUDO_CA_BUNDLE') ?: self::localCaBundlePath()
        );
    }

    public static function fromCredentials(array $credentials)
    {
        return new self(
            isset($credentials['apiKey']) ? $credentials['apiKey'] : '',
            isset($credentials['apiSecret']) ? $credentials['apiSecret'] : '',
            getenv('FUDO_API_BASE_URL') ?: 'https://api.fu.do/v1alpha1',
            getenv('FUDO_AUTH_URL') ?: 'https://auth.fu.do/api',
            getenv('FUDO_TIMEOUT_SECONDS') ?: 15,
            getenv('FUDO_CA_BUNDLE') ?: self::localCaBundlePath()
        );
    }

    private static function localCaBundlePath()
    {
        $path = __DIR__ . '/cacert.local.pem';

        return is_file($path) ? $path : '';
    }

    public function isConfigured()
    {
        return $this->apiKey !== '' && $this->apiSecret !== '';
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    public function getApiBaseUrl()
    {
        return $this->apiBaseUrl;
    }

    public function getAuthUrl()
    {
        return $this->authUrl;
    }

    public function getTimeoutSeconds()
    {
        return $this->timeoutSeconds;
    }

    public function getCaBundlePath()
    {
        return $this->caBundlePath;
    }
}
