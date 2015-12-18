<?php
define('TopList', TRUE);
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
    if ( array_key_exists('award', $parameters) ){
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


    global $gStatsInterval;
    global $gStatsStart;
    if (preg_match('/^\d{8}/', $gStatsStart)){
        /* A date */
    } else {
        /* Assume an offset */
        global $gTimezone;
        $date = new \DateTime( 'now', new DateTimeZone($gTimezone) );
        $date->add(\DateInterval::createFromDateString('-'.$gStatsStart));
        $gStatsStart = $date->format('Ymd');
    }
    /* Get sparkline data */
    foreach ($list as &$laureate){

        if ( !array_key_exists( 'dbPedia', $laureate ) ){
            /* No dbPedia link */
            // FIXME normalize
            $laureate["popularity"] = null;
            continue;
        }

        if ( !array_key_exists( $laureate["dbPedia"], $wpNames ) ){
            /* No such WP article.
               Most likely a dead link from nobelprize.org to dbPedia */
            $laureate["popularity"] = null;
            continue;
        }

        $enWpName = $wpNames[$laureate['dbPedia']];

        /* get iw links */
        $wikiDataQuery = new Toplist\WikiDataQuery();
        $iwLinks = $wikiDataQuery->getSitelinks($enWpName);

        /* get Article stats for each WP */
        global $gStatsWPEditions;
        $totalWeight = 0; // Keep track of weights, in case not all languages have an article
        $totalStats = array();
        foreach ($gStatsWPEditions as $code => $weight ){
            if ( array_key_exists( $code . 'wiki', $iwLinks )){
                $wiki = $iwLinks[$code . 'wiki'];
                $article = new Toplist\ArticleStats( $wiki, "$code.wikipedia" );
                $stat = $article->getPoints($gStatsInterval, $gStatsStart);
                if ( $stat !== null ){
                    foreach ($stat as $k=>$v) {
                        $stat[$k] = $v * $weight;
                    }
                    $totalStats[] = $stat;
                    $totalWeight += $weight;
                }
            }
        }
        /* summarize stats */
        $sumArray = array();
        foreach ($totalStats as $k=>$subArray) {
          foreach ($subArray as $id=>$value) {
            if (!isset($sumArray[$id])){
                $sumArray[$id] = 0;
            }
            $sumArray[$id] += $value;
          }
        }
        foreach ($sumArray as $k=>$v) {
            $sumArray[$k] = (int) ($sumArray[$k] / $totalWeight);
        }
        $laureate["popularity"] = $sumArray;
    }
    unset($laureate); // PHP is weird, but see http://php.net/manual/en/control-structures.foreach.php

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

    /* Get sparkline data. */
    global $gStatsInterval;
    foreach ($list as &$laureate){
        $laureate["popularity"] = array_reverse( $popularityList->getIndividual( $laureate["id"], $gStatsInterval ) );
    }

}

$api->write_headers();
$api->write_json($list);
