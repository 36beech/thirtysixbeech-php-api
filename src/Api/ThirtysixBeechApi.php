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

  public function __construct(
    private readonly array $config
  ) {}

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

  public function new_endpoint(string $endpoint, string $endpoint_method, callable $callback, bool $db_required = true, bool $auth_required = false)
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? null;
    $path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    if ($method !== strtoupper($endpoint_method)) {
        return;
    }

    $params = $this->matchPath($endpoint, $path);
    if ($params === false) {
        return;
    }

    if( $db_required ) :
      $db = $this->getDb();
    endif;

    $data = $callback($params, $db ?? null);
    JsonResponse::success($data);
  }

  private function matchPath(string $pattern, string $path): array|false
  {
    $pattern = trim( $pattern, "/" );
    $path = trim( $path, "/" );
    error_log( print_r( ["pattern" => $pattern, "path" => $path], true ) );

    $regex = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (!preg_match($regex, $path, $matches)) {
        return false;
    }

    return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
  }
}