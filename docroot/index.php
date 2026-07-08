<?php
require_once __DIR__ . '/../vendor/autoload.php';
use ThirtysixBeechApi\Api\ThirtysixBeechApi;

        
        header('Access-Control-Allow-Origin: http://localhost:5173');
        header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/../config/config.php';
$api = new ThirtysixBeechApi( $config );

function test_callback($args) {
  return array("message"=>"CHEESE!", "params" => $params);
}

function another_callback() {
  return array("message"=>"This is the default");
}

function get_birds(array $args): array
{
  $sql = "SELECT * FROM `species` ORDER BY `species`.`common_name` ASC LIMIT 50";
  $stmt = $args['db']->query($sql);
  return $stmt->fetchAll();
}

function get_families(array $args): array
{
  $sql = "SELECT * FROM `families` ORDER BY `common_name`";
  $stmt = $args['db']->query($sql);
  return $stmt->fetchAll();
}

function get_bird(array $args): array
{
  $params = $args['params'];
  $db = $args['db'];

  if( empty( $params['species'] ) ):
    return array('No species specified');
  endif;

  $sql = "
    SELECT 
    `species`.`id`,
    `species`.`family_id`,
    `species`.`common_name`,
    `species`.`scientific_name`,
    `species`.`conservation_status`,
    `species`.`avg_wingspan_cm`,
    `species`.`avg_weight_g`,
    `species`.`migratory`,
    `species`.`habitat`,
    `families`.`common_name` as `family_common_name`,
    `families`.`scientific_name` as `family_scientific_name`,
    `families`.`order_name`
  FROM `species`, `families` 
  WHERE `species`.`id` = :id AND `species`.`family_id` = `families`.`id`
  ";
  $stmt = $db->prepare( $sql );
  $stmt->execute( array(
    ':id' => $params['species']
  ) );

  return $stmt->fetchAll();
}

function add_birds(array $args): array
{
  $raw  = file_get_contents('php://input');
  $body = json_decode($raw, true);

  return $body;
}

$api->new_endpoint('/', 'GET', 'another_callback', false);
$api->new_endpoint('/birds', 'GET', 'get_birds');
$api->new_endpoint('/birds', 'POST', 'add_birds', $auth_required = true);
$api->new_endpoint('/families', 'GET', 'get_families');
$api->new_endpoint('/birds/:species', 'GET', 'get_bird');
$api->new_endpoint('/this/is/the/path', 'GET', 'test_callback', false);

