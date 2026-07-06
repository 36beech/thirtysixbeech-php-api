<?php

namespace ThirtysixBeechApi\Auth;

/**
 * Lightweight bearer-token manager using HMAC-SHA256.
 *
 * Tokens are not JWTs but follow a similar signed-payload pattern:
 *   base64url(payload) . '.' . base64url(signature)
 *
 * The payload contains an issued-at timestamp; the signature covers
 * the payload plus the secret so they cannot be forged or replayed
 * after the TTL has elapsed.
 */
class TokenManager
{
  public function __construct(
    private readonly string $secret,
    private readonly int $ttl = 3600
  ) {}

  /**
   * Issue a new signed token valid for $this->ttl seconds.
   *
   * @return string Opaque bearer token.
   */
  public function issue(): string
  {
    $payload   = $this->encodeBase64Url(json_encode(['iat' => time()]));
    $signature = $this->sign($payload);

    return $payload . '.' . $signature;
  }

  /**
   * Validate a token and return true when it is authentic and unexpired.
   *
   * @param string $token Raw value from the Authorization header.
   */
  public function validate(string $token): bool
  {
    $parts = explode('.', $token, 2);

    if (count($parts) !== 2) {
      return false;
    }

    [$payload, $signature] = $parts;

    // Constant-time comparison to prevent timing attacks.
    if (!hash_equals($this->sign($payload), $signature)):
      return false;
    endif;

    $data = json_decode($this->decodeBase64Url($payload), true);

    if (!isset($data['iat']) || !is_int($data['iat'])):
      return false;
    endif;

    return (time() - $data['iat']) <= $this->ttl;
  }


  private function sign(string $payload): string
  {
    return $this->encodeBase64Url(
      hash_hmac('sha256', $payload, $this->secret, binary: true)
    );
  }

  private function encodeBase64Url(string $data): string
  {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
  }

  private function decodeBase64Url(string $data): string
  {
    return base64_decode(strtr($data, '-_', '+/'));
  }
}
