<?php
define('TopList', TRUE);
require __DIR__ . '/lib/regions.php';

header('Content-Type: application/json; charset=utf-8');

$regionFinder = new Toplist\RegionFinder();
echo json_encode($regionFinder->getRegionList());
