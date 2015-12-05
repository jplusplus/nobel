<?php
define('TopList', TRUE);
require __DIR__ . '/settings.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'lib/list.php';

$list = new Toplist\TList($_GET);
$data = $list->getData();

$api = new Toplist\Api();
$api->write_headers();
$api->write_json($data);
