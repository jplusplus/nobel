<?php
define('TopList', TRUE);

require __DIR__ . '/settings.php';

require_once $baseDir . 'vendor/autoload.php';
require_once $baseDir . 'lib/api.php';
require_once $baseDir . 'lib/dbpedia.php';
require_once $baseDir . 'lib/wikidata.php';
require_once $baseDir . 'lib/wikipedia.php';

$api = new Toplist\Api();
$validationRules = array( 'id'     => 'required|integer',
                          'width'  => 'integer',
                          'height' => 'integer',
                        );
$filterRules = array( 'id'    => 'trim|sanitize_numbers',
                      'width' => 'trim|sanitize_numbers',
                      'height' => 'trim|sanitize_numbers',
                    );
$parameters = $api->getParameters( $validationRules, $filterRules );

$laureate = $parameters['id'];
$width = @$parameters['width'] ?: null;
$height = @$parameters['height'] ?: null;
if (!($height || $width)){
    $height = '300';
}

/* Get dbPedia url */
$sparqlEndpoint = new Endpoint('http://data.nobelprize.org/sparql');

$query = "PREFIX owl: <http://www.w3.org/2002/07/owl#>
SELECT ?laur ?sameAs {
    ?laur owl:sameAs ?sameAs
    FILTER (?laur IN (<http://data.nobelprize.org/resource/laureate/$laureate>))
}";
$result = $sparqlEndpoint->query($query);
$dbPediaUri = null;
$dbPediaLinks = array_filter( $result["result"]["rows"], function( $var ){
    $host = parse_url( $var["sameAs"], PHP_URL_HOST );
    return ('dbpedia.org' === $host);
});
//Use only the first link, if multiple
$dbPediaLinkObj = array_pop($dbPediaLinks);
$dbPediaLink = $dbPediaLinkObj["sameAs"];

/* Query DbPedia for enwp link */
$dbPediaQuery = new Toplist\DbPediaQuery( $dbPediaLink );
$response = $dbPediaQuery->getWikipediaNames();
if ( !array_key_exists( $dbPediaLink, $response ) ){
    echo json_encode( array( ) );
    exit();
}
$enWikipediaName = $response[$dbPediaLink];

/* Get language links */
$wikiDataQuery = new Toplist\WikiDataQuery();
$iwLinks = $wikiDataQuery->getSitelinks($enWikipediaName);
$allWikipediaNames = array();
global $gImageSourceWPEditions;
foreach ($gImageSourceWPEditions as $wpEdition) {
    if ( array_key_exists( $wpEdition . 'wiki', $iwLinks )){
        $allWikipediaNames[$wpEdition] = $iwLinks[$wpEdition . 'wiki'];
    }
}

/* Query Wikipedias for images */
$output = array();
global $gImageBlacklist;
foreach ($allWikipediaNames as $wikipediaEdition => $pageName){
    $wikipediaApi = new TopList\WikipediaQuery( $wikipediaEdition );
    $images = $wikipediaApi->getImages( $pageName, $width, $height );
    $output = array_merge( $images, $output );
}

/* Filter out duplicates */
$allImageUrls = array();
$filteredOutput = array_filter($output, function( $image ) use (&$allImageUrls) {
    if ( in_array( $image['url'], $allImageUrls ) ){
        return false;
    } else {
        $allImageUrls[] = $image['url'];
        return true;
    }
});

$data = array( $laureate => $filteredOutput );

$api->write_headers();
$api->write_json($data);
