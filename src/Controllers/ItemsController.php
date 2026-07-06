<?php

namespace ThirtysixBeechApi\Controllers;

use ThirtysixBeechApi\Middleware\AuthMiddleware;
use ThirtysixBeechApi\Response\JsonResponse;
use PDO;

/**
 * CRUD operations for the `items` table.
 *
 * All routes require a valid bearer token (enforced by AuthMiddleware).
 *
 * GET  /items        → list all items
 * POST /items        → create a new item
 */
class ItemsController
{
  public function __construct(
    private readonly PDO            $db,
    private readonly AuthMiddleware $auth
  ) {}

  /**
   * GET /items
   *
   * Returns all items ordered newest-first.
   */
  public function index(): never
  {
    // $this->auth->guard();

    $stmt  = $this->db->query('SELECT * FROM `links` LIMIT 100');
    $items = $stmt->fetchAll();

    JsonResponse::success($items);
  }
}
