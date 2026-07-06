<?php

namespace ThirtysixBeechApi\Controllers;

use ThirtysixBeechApi\Auth\TokenManager;
use ThirtysixBeechApi\Response\JsonResponse;

/**
 * Handles PIN-based authentication.
 *
 * POST /auth/login
 * Body (JSON): { "pin": "1234" }
 *
 * Returns a bearer token on success.
 */
class AuthController
{
  public function __construct(
    private readonly TokenManager $tokenManager,
    private readonly string       $pinHash
  ) {}

  /**
   * Verify the supplied PIN and issue a token.
   */
  public function login(): never
  {
    $body = $this->parseJsonBody();
    $pin = $body['pin'] ?? '';

    if (!is_string($pin) || !preg_match('/^\d{6}$/', $pin)) {
      JsonResponse::error('PIN must be exactly 6 digits.', 422);
    }

    if (!password_verify($pin, $this->pinHash)) {
      JsonResponse::error('Invalid credentials.', 401);
    }

    $token = $this->tokenManager->issue();

    JsonResponse::success([
      'token'      => $token,
      'token_type' => 'Bearer',
      'expires_in' => 3600,
    ]);
  }

      // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

  /**
   * Decode and return the JSON request body.
   *
   * @return array<string, mixed>
   */
  private function parseJsonBody(): array
  {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
      JsonResponse::error('Request body must be valid JSON.', 400);
    }

    return $data;
  }
}
