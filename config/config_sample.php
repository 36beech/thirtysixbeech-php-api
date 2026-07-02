<?php

return array(
  'db' => array(
    'host'    => 'database',
    'port'    => 3306,
    'dbname'  => 'lamp',
    'user'    => 'lamp',
    'pass'    => 'lamp',
    'charset' => 'utf8mb4',
  ),
  'auth' => array(
    'pin_hash'       => '$2y$12$placeholderHashReplaceThisNow00000000000000000000000',
    'token_secret'   => 'change-me-to-a-long-random-string',
    'token_ttl'      => 3600, // seconds
  ),
);
