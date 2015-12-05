<?php
define('TopList', TRUE);

require __DIR__ . '/settings.php';

// TODO remove, left here 2015-12-04 for backward compability
// after adding VERBOSE to settings.php
defined('VERBOSE') or define('VERBOSE', 3);

require $baseDir . 'vendor/autoload.php';
require $baseDir . 'lib/dbpedia.php';
require $baseDir . 'lib/wikidata.php';
require $baseDir . 'lib/api.php';

/* Validate parameters. No not accept any invalid value */
$gump = new GUMP();
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
global $debugLevel;
if ( $debugLevel >= VERBOSE ){
    error_log( "Gallery: Using $dbPediaLink for dbPedia link." );
}

/* Query DbPedia for enwp link */
$dbPediaQuery = new Toplist\DbPediaQuery( $dbPediaLink );
$response = $dbPediaQuery->getWikipediaNames();
if ( !array_key_exists( $dbPediaLink, $response ) ){
    echo json_encode( array( ) );
    exit();
}
$enWikipediaName = $response[$dbPediaLink];

if ( $debugLevel >= VERBOSE ){
    error_log( "Gallery: Using $enWikipediaName for enwp name." );
}

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

if ( $debugLevel >= VERBOSE ){
    $num = count($allWikipediaNames);
    error_log( "Gallery: Using $num Wikipedia editions." );
}

/* Query Wikipedias for images */
$params = array(
    'action'    => 'query',
    'prop'      => 'imageinfo',
    'generator' => 'images',
    'iiprop'    => 'extmetadata|mediatype|size|url',
    'iiextmetadatalanguage' => 'en',
    'format'    => 'json'
);
if ($height) {
    $params['iiurlheight'] = $height;
} elseif ($width) {
    $params['iiurlwidth'] = $width;
}

$output = array();
$allImageNames = array(); // to filter out duplicates
global $gImageBlacklist;
foreach ($allWikipediaNames as $wikipediaEdition => $pageName){
    $params['titles'] = $pageName;
    $paramString = http_build_query( $params );
    $endpoint = "https://$wikipediaEdition.wikipedia.org/w/api.php?$paramString";

    $md5 = md5($endpoint);
    $images = __c()->get($md5);
    if ($images === null){
        $images = array();
        $json = file_get_contents($endpoint);
        $response = json_decode($json, true);
        if (array_key_exists('query', $response)){
            $pages = $response["query"]["pages"];
            if ( $debugLevel >= VERBOSE ){
                $num = count($pages);
                error_log( "Gallery: Found $num image pages." );
            }
            foreach ( $pages as $page ){
                $titleParts = explode(':', $page["title"]); // Add only part after ':'
                $title = $titleParts[1];

                if ( !in_array( $title, $allImageNames ) &&
                     !in_array( $title, $gImageBlacklist ) ){
                    $allImageNames[] = $title;
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

            }
        } else {
            /* invalid page or no images */
        }
        global $gExternalLaureateDataCacheTime;
        __c()->set($md5, $images, $gExternalLaureateDataCacheTime*3600);
    }
    $output = array_merge( $images, $output );

}

$data = array( $laureate => $output );

$api = new Toplist\Api();
$api->write_headers();
$api->write_json($data);
