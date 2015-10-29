<?php
require __DIR__ . '/lib/list.php';

header('Content-Type: application/json; charset=utf-8');

$list = new Toplist($_GET);
echo json_encode($list->getData());
