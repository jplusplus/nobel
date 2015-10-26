<?php
require __DIR__ . '/lib/list.php';

header('Content-Type: application/json');

$list = new Toplist($_GET);
echo json_encode($list->getData());
