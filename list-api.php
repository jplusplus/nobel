<?php
define('TopList', TRUE);
require __DIR__ . '/settings.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'lib/list.php';

$api = new Toplist\Api();
$validationRules = array (
        'length'    => 'integer|min_numeric,3|max_numeric,50',
        'award'     => 'alpha_dash',
        'gender'    => 'alpha',
        'region'    => 'alpha_dash',
        'popularity'=> 'alpha_dash',
    );
$filterRules = array(
        'length'    => 'trim|sanitize_numbers',
        'award'     => 'trim|sanitize_string',
        'gender'    => 'trim|sanitize_string',
        'region'    => 'trim',
        'popularity'=> 'trim|sanitize_string'
    );
$parameters = $api->getParameters( $validationRules, $filterRules );
$list = new Toplist\TList($_GET);
$data = $list->getData();

$api->write_headers();
$api->write_json($data);
