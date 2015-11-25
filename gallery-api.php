<?php
namespace Toplist;
define('TopList', TRUE);

require __DIR__ . '/settings.php';
require $baseDir . 'vendor/bordercloud/sparql/Endpoint.php'; //This lib is not autoloaded
require $baseDir . 'lib/dbpedia.php';

header('Content-Type: application/json; charset=utf-8');

$laureate = 579;

/* Get dbPedia url */
$sparqlEndpoint = new \Endpoint('http://data.nobelprize.org/sparql');

$query = "PREFIX owl: <http://www.w3.org/2002/07/owl#>
SELECT ?laur ?sameAs {
    ?laur owl:sameAs ?sameAs
    FILTER (?laur IN (<http://data.nobelprize.org/resource/laureate/$laureate>))
}";
$result = $sparqlEndpoint->query($query)["result"]["rows"];
$dbPediaUri = null;
$dbPediaLinks = array_filter( $result, function( $var ){
    $host = parse_url( $var["sameAs"], PHP_URL_HOST );
    return ('dbpedia.org' === $host);
});
//Use only the first link, if multiple
$dbPediaLink = array_pop($dbPediaLinks)["sameAs"];

/* Query DbPedia for enwp link */
$dbPediaQuery = new DbPediaQuery(array("$dbPediaLink"));
$enWikipediaName = $dbPediaQuery->getWikipediaNames()[$dbPediaLink];

/* Query enwp for images */
$endpoint = "https://en.wikipedia.org/w/api.php?action=query&prop=imageinfo&iiprop=extmetadata&iilimit=50&generator=images&titles=$enWikipediaName&format=json";

$md5 = md5($endpoint);
$images = null;//__c()->get($md5);
if ($images === null){
    $json = file_get_contents($endpoint);
    $response = json_decode($json, true);
    if (array_key_exists('query', $response)){
    	$pages = $response["query"]["pages"];
    	foreach ( $pages as $page ){
    		$images[] = array (
    			"title" => $page["title"]
    		);

    	}
    } else {
        $images = array();
    }
    global $gExternalLaureateDataCacheTime;
    //__c()->set($md5, $images, $gExternalLaureateDataCacheTime*3600);
}




echo json_encode( array( $laureate => $images ) );
