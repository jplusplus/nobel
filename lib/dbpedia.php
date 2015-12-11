<?php
/* Contains a class for querying the dbPedia endpoint.
   Todo: Merge with ED!

*/ 
namespace Toplist;
if(!defined('TopList')) {
   die('Not permitted');
}

require_once $baseDir . 'lib/external-data.php';

Class DbPediaQuery extends ExternalDataSparql {

    function __construct( ){
        $this->endPoint = new \Endpoint('http://dbpedia.org/sparql');
    }

    /* Return an indexed array of laureates data */
    /* Accept an array of laureate uris, or a single uri */
    function getWikipediaNames( $laureates ){

        if ( !is_array( $laureates) ){
            $laureates = array( $laureates );
        }

        $uris = array_map(array($this, '_encodeUri'), $laureates);
        $uris = $this->_joinAndAffix( $uris, ', ', '<', '>');

        $laureateDictionary = array();

        $query = "SELECT ?uri ?label {
            ?uri foaf:isPrimaryTopicOf ?label
            FILTER (?uri IN ($uris))
          }";
        global $gExternalLaureateDataCacheTime;
        $result = $this->fetchAndCache($query, $gExternalLaureateDataCacheTime );
        foreach( $result as $row){

            if (isset($row['label'])){
                $host = parse_url( $row['label'], PHP_URL_HOST );
                $pathParts = explode('/', parse_url( $row['label'], PHP_URL_PATH ));
                if ('en.wikipedia.org' === $host){
                    //use the part after the last / as article name. Would break in case of / in name, e.g. in some khoisan languages.
                    $laureateDictionary[rawurldecode($row['uri'])] = array_pop($pathParts);
                }
            }
        }
        return $laureateDictionary;
    }
}

