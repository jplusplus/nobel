<?php
namespace Toplist;
define('TopList', TRUE);

require __DIR__ . '/settings.php';
require $baseDir . 'vendor/bordercloud/sparql/Endpoint.php'; //This lib is not autoloaded

header('Content-Type: application/json; charset=utf-8');

$laureate = 579;

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
//Use only the first link, i multiple
$dbPediaLink = array_pop($dbPediaLinks)["sameAs"];

echo json_encode(array($laureate => $dbPediaLink));
