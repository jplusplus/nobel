<?php
namespace Toplist;
define('TopList', TRUE);

require __DIR__ . '/settings.php';

require $baseDir . 'vendor/autoload.php';
//require $baseDir . 'vendor/bordercloud/sparql/Endpoint.php'; //This lib is not autoloaded
require $baseDir . 'lib/dbpedia.php';

header('Content-Type: application/json; charset=utf-8');

/* Validate parameters. No not accept any invalid value */
$gump = new \GUMP();
$parameters = $gump->sanitize($_GET);
$gump->validation_rules( array('id' => 'required|integer') );
$gump->filter_rules( array('id' => 'trim|sanitize_numbers') );
$parameters = $gump->run($parameters);

$laureate = $parameters['id'];

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
$response = $dbPediaQuery->getWikipediaNames();
if ( !array_key_exists( $dbPediaLink, $response ) ){
    echo json_encode( array( ) );
    exit();
}
$enWikipediaName = $response[$dbPediaLink];


/* Query enwp for images */
$endpoint = "https://en.wikipedia.org/w/api.php?action=query&prop=imageinfo&iiprop=extmetadata|mediatype|size&iilimit=30&generator=images&titles=$enWikipediaName&format=json";

$md5 = md5($endpoint);
$images = null;//__c()->get($md5);
if ($images === null){
    $json = file_get_contents($endpoint);
    $response = json_decode($json, true);
    if (array_key_exists('query', $response)){
    	$pages = $response["query"]["pages"];
    	foreach ( $pages as $page ){
    		$imgInfo = array_pop($page["imageinfo"]);
    		if ( $imgInfo["mediatype"] === 'BITMAP' &&
    			 $imgInfo["width"] > 250 &&
                 $imgInfo["height"] > 250 ){

                $metaData = $imgInfo["extmetadata"];

                $attributionRequired = ('true' === @$metaData["AttributionRequired"]["value"]);
                $cred = '';
                if ($attributionRequired){
                    $cred .= @$metaData["LicenseShortName"]["value"] ?: @$metaData["LicenseShortName"]["value"];
                    $cred .= ', ';
                    $cred .= strip_tags(@$metaData["Credit"]["value"]) . ' ' . strip_tags(@$metaData["Artist"]["value"]);
                }
	    		$images[] = array (
	    			"title" => $page["title"],
                    "credit" => $cred
	    		);
    		}

    	}
    } else {
        $images = array();
    }
    global $gExternalLaureateDataCacheTime;
    //__c()->set($md5, $images, $gExternalLaureateDataCacheTime*3600);
}

echo json_encode( array( $laureate => $images ) );
