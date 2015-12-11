<?php
define('TopList', TRUE);
include __DIR__ . "settings.php";
require $baseDir . 'lib/api.php';
require $baseDir . 'lib/regions.php';

$api = new Toplist\Api();
$regionFinder = new Toplist\RegionFinder();
$api->write_headers();
$api->write_json($regionFinder->getRegionList());
