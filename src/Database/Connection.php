<?php

namespace ThirtysixBeechApi\Database;

use PDO;
use PDOException;

/**
 * Database connection singleton.
 *
 * Provides a single shared PDO instance for the lifetime of the request.
 */
class Connection
{
  private static ?PDO $instance = null;
  private function __construct() {}

  public static function getInstance(array $config)
  {
    // Return the saved instance if the connection has already been made
    if (self::$instance !== null) {
      return self::$instance;
    }

    $dsn = sprintf(
      'mysql:host=%s;port=%d;dbname=%s;charset=%s',
      $config['host'],
      $config['port'],
      $config['dbname'],
      $config['charset']
    );

    try {
      self::$instance = new PDO($dsn, $config['user'], $config['pass'], array(
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ));
    } catch (PDOException $e) {
      throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
    }

    return self::$instance;
  }
}
