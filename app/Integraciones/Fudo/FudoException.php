<?php

namespace App\Integraciones\Fudo;

final class FudoException extends \RuntimeException
{
    private $errorCode;

    public function __construct($message, $errorCode, $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = (string) $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
