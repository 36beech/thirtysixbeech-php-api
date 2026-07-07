<?php

namespace ThirtysixBeechApi\Api;

use ThirtysixBeechApi\Database\Connection;
use ThirtysixBeechApi\Auth\TokenManager;
use ThirtysixBeechApi\Controllers\AuthController;
use ThirtysixBeechApi\Controllers\ItemsController;
use ThirtysixBeechApi\Middleware\AuthMiddleware;
use ThirtysixBeechApi\Response\JsonResponse;

use PDO;

class ThirtysixBeechApi
{
  private ?PDO $db = null;
  private ?TokenManager $auth = null;
  private ?AuthMiddleware $middleware = null;

  /**
   * Bootstraps the API with the provided configuration.
   *
   * If auth is configured, the POST /auth/login endpoint is registered
   * automatically — no manual new_endpoint() call required.
   *
   * @param array $config Associative array with optional 'db' and 'auth' keys.
   */
  public function __construct(
    private readonly array $config
  ) {
    if (isset($this->config['auth'])) {
      error_log("AUTH");
      $this->new_endpoint('/auth/login', 'POST', [$this, 'handleLogin'], db_required: false);
      error_log("new_endpoint should have fired");
    }
  }

  /**
   * Returns the shared PDO database connection, creating it on first call.
   *
   * Lazy — the connection is only opened when an endpoint that requires
   * the database is actually matched and invoked.
   *
   * @throws \RuntimeException If the connection cannot be established.
   */
  public function getDb(): PDO
  {
    if ($this->db === null) :
      if (!isset($this->config['db'])) :
        JsonResponse::error('Database not configured.', 503);
      endif;
      $this->db = Connection::getInstance($this->config['db']);
    endif;

    return $this->db;
  }

  /**
   * Returns the shared TokenManager instance, creating it on first call.
   *
   * Lazy — the TokenManager is only instantiated when auth is first needed,
   * either during login or when guarding a protected endpoint.
   */
  public function getAuth(): TokenManager
  {
    if ($this->auth === null) :
      if (!isset($this->config['auth'])) :
        JsonResponse::error('Auth not configured.', 503);
      endif;
      $this->auth = new TokenManager(
        $this->config['auth']['token_secret'],
        $this->config['auth']['token_ttl']
      );
    endif;

    return $this->auth;
  }

  /**
   * Handles POST /auth/login requests.
   *
   * Reads a PIN from the JSON request body, verifies it against the bcrypt
   * hash in config, and returns a signed bearer token on success.
   *
   * Responds with 422 if the PIN format is invalid, or 401 if it does not
   * match. On success, returns the token, type, and TTL.
   *
   * @return array Token payload: token, token_type, expires_in.
   */
  public function handleLogin(): array
  {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true);
    error_log(print_r($body,true));

    $pin = $body['pin'] ?? '';

    if (!is_string($pin) || !preg_match('/^\d+$/', $pin)) {
      JsonResponse::error('Invalid PIN format.', 422);
    }

    if (!password_verify($pin, $this->config['auth']['pin_hash'])) {
      JsonResponse::error('Invalid credentials.', 401);
    }

    $token = $this->getAuth()->issue();

    return [
      'token'      => $token,
      'token_type' => 'Bearer',
      'expires_in' => $this->config['auth']['token_ttl'],
    ];
  }

  /**
   * Returns the shared AuthMiddleware instance, creating it on first call.
   *
   * AuthMiddleware is the single source of truth for bearer token validation.
   * Lazy — only instantiated when a protected endpoint is matched.
   */
  public function getMiddleware(): AuthMiddleware
  {
    if ($this->middleware === null) :
      $this->middleware = new AuthMiddleware($this->getAuth());
    endif;

    return $this->middleware;
  }

  /**
   * Registers and immediately attempts to dispatch a route.
   *
   * Compares the current HTTP method and URL path against the given endpoint
   * pattern. If both match, optional auth and DB dependencies are resolved,
   * then the callback is invoked and its return value sent as a JSON response.
   *
   * If the method or path do not match, the function returns silently,
   * allowing subsequent new_endpoint() calls to continue matching.
   *
   * @param string   $endpoint        Route pattern, e.g. '/birds/:species'.
   * @param string   $endpoint_method HTTP method to match, e.g. 'GET', 'POST'.
   * @param callable $callback        Handler invoked on match: fn(array $params, ?PDO $db): array.
   * @param bool     $db_required     Whether to open a DB connection before invoking the callback.
   * @param bool     $auth_required   Whether to enforce bearer token auth before invoking the callback.
   */
  public function new_endpoint(string $endpoint, string $endpoint_method, callable $callback, bool $db_required = true, bool $auth_required = false)
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $args = array();

    if ($method !== strtoupper($endpoint_method)) {
      return;
    }

    $params = $this->matchPath($endpoint, $path);
    if ($params === false) {
      return;
    }

    $args['params'] = $params;

    if ($db_required) :
      $db = $this->getDb();
    endif;

    $args['db'] = $db ?? null;

    // if ($auth_required) {
    //   $this->getMiddleware()->guard();
    // }

    $data = $callback($args);
    JsonResponse::success($data);
  }

  /**
   * Matches a URL path against a route pattern, extracting named parameters.
   *
   * Converts Express-style ':param' tokens into named regex capture groups,
   * then tests the path against the resulting pattern.
   *
   * Returns an associative array of named captures on match (empty array if
   * the route has no parameters), or false if the path does not match.
   *
   * @param string $pattern Route pattern, e.g. 'birds/:species'.
   * @param string $path    Incoming URL path, e.g. 'birds/42'.
   *
   * @return array|false Named parameter map on match, false otherwise.
   */
  private function matchPath(string $pattern, string $path): array|false
  {
    $pattern = trim($pattern, "/");
    $path = trim($path, "/");
    error_log(print_r(["pattern" => $pattern, "path" => $path], true));

    $regex = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (!preg_match($regex, $path, $matches)) {
      return false;
    }

    return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
  }
}
