<?php
define('TopList', TRUE);
require_once __DIR__ . '/settings.default.php';
require_once __DIR__ . '/settings.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'lib/html.php';

$api = new Toplist\Api();
$validationRules = array (
        'length'    => 'integer|min_numeric,3|max_numeric,50',
        'award'     => 'alpha_dash',
        'gender'    => 'alpha',
        'region'    => 'alpha_dash',
        'popularity'=> 'alpha_dash',
        'id'        => 'alpha_numeric',
    );
$filterRules = array(
        'length'    => 'trim|sanitize_numbers',
        'award'     => 'trim|sanitize_string',
        'gender'    => 'trim|sanitize_string',
        'region'    => 'trim',
        'popularity'=> 'trim|sanitize_string',
        'id'        => 'trim|sanitize_string',
    );
$parameters = $api->getParameters( $validationRules, $filterRules );
$id = @$parameters['id'] ?: 1;

global $baseUrl;
$json = file_get_contents( "$baseUrl/list-api.php?" . http_build_query($parameters) );
$response = json_decode($json, true);
$widget = new Toplist\TListWidget( $response, $id, false );
$html = $widget->getHTML();


$api->write_headers( 'text/html' );
$api->write_html( $html );
