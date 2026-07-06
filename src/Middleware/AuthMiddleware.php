<?php

namespace ThirtysixBeechApi\Middleware;

use ThirtysixBeechApi\Auth\TokenManager;
use ThirtysixBeechApi\Response\JsonResponse;

/**
 * Extracts and validates the bearer token from the Authorization header.
 *
 * Usage: call AuthMiddleware::guard() at the top of any protected endpoint.
 * Responds with 401 and halts if the token is missing, malformed, or expired.
 */
class AuthMiddleware
{
  public function __construct(private readonly TokenManager $tokenManager) {}

  /**
   * Halt with 401 unless a valid bearer token is present.
   */
  public function guard(): void
  {
    $header = $_SERVER['HTTP_AUTHORIZATION'] 
       ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] 
       ?? '';
    error_log(print_r($_SERVER, true));

    if (!str_starts_with($header, 'Bearer ')) {
      JsonResponse::error('Missing or malformed Authorization header.', 401);
    }

    $token = substr($header, 7);

    if (!$this->tokenManager->validate($token)) {
      JsonResponse::error('Invalid or expired token.', 401);
    }
  }
}
