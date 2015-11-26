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
$gump->validation_rules( array( 'id'     => 'required|integer',
                                'width'  => 'integer',
                                'height' => 'integer',
                        ) );
$gump->filter_rules( array( 'id'    => 'trim|sanitize_numbers',
                            'width' => 'trim|sanitize_numbers',
                            'height' => 'trim|sanitize_numbers',
                        ) );
$parameters = $gump->run($parameters);

$laureate = $parameters['id'];
$width = @$parameters['width'] ?: null;
$height = @$parameters['height'] ?: null;
if (!($height || $weight)){
    $height = '300';
}

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

/* Get language links */
https://www.wikidata.org/w/api.php?action=wbgetentities&sites=enwiki&titles=Ore%20Mountains&languages=cs|de|es|fr|it|pl|pt|ru&props=sitelinks%2Furls

/* Query wp for images */
$params = array(
    'action'    => 'query',
    'prop'      => 'imageinfo',
    'generator' => 'images',
    'titles'    => $enWikipediaName,
    'iiprop'    => 'extmetadata|mediatype|size|url',
    'iiextmetadatalanguage' => 'en',
    'format'    => 'json'
);
if ($height) {
    $params['iiurlheight'] = $height;
} elseif ($width) {
    $params['iiurlwidth'] = $width;
}
$paramString = http_build_query( $params );
$wikipediaEdition = 'en';
$endpoint = "https://$wikipediaEdition.wikipedia.org/w/api.php?$paramString";

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
    			 $imgInfo["width"] > 200 &&
                 $imgInfo["height"] > 280 ){

                $metaData = $imgInfo["extmetadata"];

                $attributionRequired = ('true' === @$metaData["AttributionRequired"]["value"]);
                $cred = '';
                if ($attributionRequired){
                    $cred .= @$metaData["LicenseShortName"]["value"] ?: @$metaData["LicenseShortName"]["value"];
                    $cred .= ', ';
                    $cred .= implode(' ', array( strip_tags(@$metaData["Credit"]["value"]), strip_tags(@$metaData["Artist"]["value"]) ));
                }
	    		$images[] = array (
	    			"caption"   => strip_tags(@$metaData['ImageDescription']['value'] ?: ''),
                    "credit"    => $cred,
                    "url"       => $imgInfo['thumburl'],
                    "sourceurl" => $imgInfo['descriptionurl'],

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
