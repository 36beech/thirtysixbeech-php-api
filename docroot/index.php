<?php
require_once __DIR__ . '/../vendor/autoload.php';
use ThirtysixBeechApi\Api\ThirtysixBeechApi;

$config = require __DIR__ . '/../config/config.php';
$api = new ThirtysixBeechApi( $config );

function test_callback($params) {
  return array("message"=>"CHEESE!", "params" => $params);
}

function another_callback() {
  return array("message"=>"This is the default");
}

function get_birds(array $params, ?PDO $db): array
{
  $sql = "SELECT * FROM `species` ORDER BY `species`.`common_name` ASC LIMIT 5";
  $stmt = $db->query($sql);
  return $stmt->fetchAll();
}

$api->new_endpoint('/', 'GET', 'another_callback', false);
$api->new_endpoint('/birds', 'GET', 'get_birds');
$api->new_endpoint('/this/is/the/path', 'GET', 'test_callback', false);

