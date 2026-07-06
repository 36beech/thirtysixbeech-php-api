<?php

namespace ThirtysixBeechApi\Api;

use ThirtysixBeechApi\Database\Connection;
use ThirtysixBeechApi\Auth\TokenManager;
use ThirtysixBeechApi\Controllers\AuthController;
use ThirtysixBeechApi\Controllers\ItemsController;
use ThirtysixBeechApi\Middleware\AuthMiddleware;
use ThirtysixBeechApi\Response\JsonResponse;

class ThirtysixBeechApi
{
  private readonly array $path;
  private readonly object $db;
  private readonly TokenManager $tokenManager;
  private readonly AuthMiddleware $authMiddleware;

  public function __construct(
    private readonly string $method,
    string $path,
    private readonly array $db_config,
    private readonly array $auth
  ) {
    if ($method === 'OPTIONS') {
      http_response_code(204);
      exit;
    }

    $this->path = explode("/", trim($path, '/'));

    $this->db = Connection::getInstance($db_config);
    if( ! empty( $auth ) ) :
      $this->tokenManager = new TokenManager($auth['token_secret'], $auth['token_ttl']);
      $this->authMiddleware = new AuthMiddleware($this->tokenManager);
    endif;

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
  }

  public function new_endpoint(callable $callback)
  {
    $result = $callback();
    echo json_encode($this->db);
  }
}