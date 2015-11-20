<?php
define('TopList', TRUE);
require __DIR__ . '/settings.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode(array("hello" => "world"));
