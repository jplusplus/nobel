<?php
define('TopList', TRUE);
require __DIR__ . '/settings.default.php';
require __DIR__ . '/settings.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/db.php';
require $baseDir . 'lib/dbpedia.php';
require $baseDir . 'lib/wikidata.php';
require $baseDir . 'lib/popularity.php';

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

/* Get laureate list from nobelprize.org */
$query = new Toplist\SPARQLQuery($parameters);
$list = $query->get();
// Laureate id's, for looking up Wikipedia links
$lids = array_map(function ($l) {return $l['dbPedia'];}, $list);

// Add link and image, replace underscore in award names
// Award codes for gProfilePageUrl, as hardcoded by Hans
$awardAbbrs = array(
    'Physics' => 'phy',
    'Chemistry' => 'che',
    'Literature' => 'lit',
    'Peace' => 'pea',
    'Physiology_or_Medicine' => 'med',
    'Economic_Sciences' => 'eco',
);
foreach ($list as &$row) {
    $cat = '';
    if ( array_key_exists('award', $parameters) && array_key_exists( $parameters['award'], $awardAbbrs ) ){
        $cat = $awardAbbrs[$parameters['award']];
    }
    global $gProfilePageUrl;
    $row['url'] = sprintf($gProfilePageUrl, $row['id'], $cat);
    global $gImageAPI;
    $row['image'] = sprintf($gImageAPI, $row['id']);
    array_walk($row["awards"], function (&$v, $k){
        $v['award'] = str_replace("_", " ", $v['award']);
    });

}
unset($row); // PHP is weird, but see http://php.net/manual/en/control-structures.foreach.php

if ( array_key_exists('popularity', $parameters) && $parameters['popularity'] === 'wikipedia'){
    /* Get all WP ids from dbPedia */
    $dbPediaQuery = new Toplist\DbPediaQuery();
    $wpNames = $dbPediaQuery->getWikipediaNames( $lids );

    /* Get most viewed list for this subset of laureates */
    $popularityList = new Toplist\WikipediaPopularityList($wpNames);
    $orderedList = $popularityList->getOrdered();

    usort($list, function($a, $b) use ($orderedList){
        $ida = $a['dbPedia'];
        $idb = $b['dbPedia'];
        $posa = array_search($ida, $orderedList);
        $posb = array_search($idb, $orderedList);
        return $posa > $posb ? 1 : -1;
    });

    /* Truncate list to max length */
    global $maxListItems;
    $maxListLength = @$parameters['length'] ?: $maxListItems;
    $list = array_values (array_slice($list, 0, $maxListLength));

} else {
    $popularityList = new Toplist\OnsitePopularityList();
    $orderedList = $popularityList->getOrdered();
    usort($list, function($a, $b) use ($orderedList){
        $ida = $a['id'];
        $idb = $b['id'];
        $posa = array_search($ida, $orderedList);
        $posb = array_search($idb, $orderedList);
        return $posa < $posb ? 1 : -1;
    });
	/* Truncate list to max length */
	global $maxListItems;
	$maxListLength = @$parameters['length'] ?: $maxListItems;
	$list = array_values (array_slice($list, 0, $maxListLength));

}

$api->write_headers();
$api->write_json($list);
