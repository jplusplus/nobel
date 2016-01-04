<?php   
define('TopList', TRUE);
require_once __DIR__ . "/settings.default.php";
require_once __DIR__ . "/settings.php";
require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'lib/regions.php';

$api = new Toplist\Api();
$validationRules = array (
        'name'    => 'alpha_dash',
    );
$filterRules = array(
        'name'    => 'trim',
    );
$parameters = $api->getParameters( $validationRules, $filterRules );

$regionFinder = new Toplist\RegionFinder();
$api->write_headers();
if ( in_array( 'name', $parameters ) ){
    $api->write_json( $regionFinder->getRegions( $parameters['name'] ) );
} else{
    $api->write_json($regionFinder->getRegionMapping());
}
