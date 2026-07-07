<?php

namespace ThirtysixBeechApi\Response;

/**
 * Sends a JSON response and terminates execution.
 */
class JsonResponse
{
    /**
     * Emit a JSON response.
     *
     * @param mixed $data    Payload to encode.
     * @param int   $status  HTTP status code.
     */
    public static function send(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Shorthand for successful responses. */
    public static function success(mixed $data, int $status = 200): never
    {
        self::send(['success' => true, 'data' => $data], $status);
    }

    /** Shorthand for error responses. */
    public static function error(string $message, int $status = 400): never
    {
        self::send(['success' => false, 'error' => $message], $status);
    }
}
