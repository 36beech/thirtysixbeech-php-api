<?php
require_once __DIR__ . '/../vendor/autoload.php';
use ThirtysixBeechApi\Api\ThirtysixBeechApi;


['db' => $db, 'auth' => $auth] = require __DIR__ . '/../config/config.php';
$method = $_SERVER['REQUEST_METHOD'] ?? null;
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// $path   = '/' . trim($path, '/');
$api = new ThirtysixBeechApi( $method, $path, $db, $auth );
error_log(print_r($api, true));

function test_callback() {
  return array("message"=>"CHEESE!");
}

$api->new_endpoint('test_callback');

/*
    $this->db = Connection::getInstance($db_config);
    $this->tokenManager = new TokenManager($auth['token_secret'], $auth['token_ttl']);
    $this->authMiddleware = new AuthMiddleware($this->tokenManager);
    */