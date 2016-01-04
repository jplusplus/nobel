<?php
define('TopList', TRUE);
require_once __DIR__ . '/settings.default.php';
require_once __DIR__ . '/settings.php';
require $baseDir . 'lib/api.php';
require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/db.php';
require $baseDir . 'lib/dbpedia.php';
require $baseDir . 'lib/wikidata.php';
require $baseDir . 'lib/popularity.php';

$api = new Toplist\Api();
$validationRules = array (
        'id'    => 'integer|min_numeric,0|max_numeric,9999',
        'popularity'=> 'alpha_dash',
    );
$filterRules = array(
        'id'    => 'trim|sanitize_numbers',
        'popularity'=> 'trim|sanitize_string'
    );
$parameters = $api->getParameters( $validationRules, $filterRules );

$laureate = @$parameters['id'] ?: 1;
/* Get dbPedia url */
$simpleSPARQLQuery = new Toplist\SimpleSPARQLQuery( $laureate );
$dbPediaLink = $simpleSPARQLQuery->getDbpedia();

if ( array_key_exists('popularity', $parameters) && $parameters['popularity'] === 'wikipedia'){

    /* Get all WP ids from dbPedia */
    $dbPediaQuery = new Toplist\DbPediaQuery();
    $wpName = $dbPediaQuery->getWikipediaNames( $dbPediaLink );

    /* get iw links */
    $wikiDataQuery = new Toplist\WikiDataQuery();
    $iwLinks = $wikiDataQuery->getSitelinks( array_pop( $wpName ) );

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
    $spark = $sumArray;

} else {
    $popularityList = new Toplist\OnsitePopularityList();
    
    global $gStatsInterval;
    $spark = array_reverse( $popularityList->getIndividual( $laureate, $gStatsInterval ) );

}

$api->write_headers();
$api->write_json($spark);
