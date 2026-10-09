<?php

namespace App\Http;

final class JsonResponse
{
    public static function enviar($payload, $statusCode)
    {
        http_response_code((int) $statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
